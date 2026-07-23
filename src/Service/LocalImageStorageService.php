<?php
declare(strict_types=1);

namespace App\Service;

use InvalidArgumentException;
use Psr\Http\Message\UploadedFileInterface;
use RuntimeException;

class LocalImageStorageService
{
    private string $root;

    private int $maxBytes;

    private int $maxWidth;

    private int $maxHeight;

    private bool $preferWebp;

    /**
     * @var array<string, string>
     */
    private array $allowedMimeExtensions;

    public function __construct(array $config = [])
    {
        $this->root = rtrim((string)($config['root'] ?? WWW_ROOT), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        $this->maxBytes = (int)($config['maxBytes'] ?? env('IMAGE_UPLOAD_MAX_BYTES', 4 * 1024 * 1024));
        $this->maxWidth = (int)($config['maxWidth'] ?? env('IMAGE_UPLOAD_MAX_WIDTH', 1200));
        $this->maxHeight = (int)($config['maxHeight'] ?? env('IMAGE_UPLOAD_MAX_HEIGHT', 1200));
        $this->preferWebp = (bool)($config['preferWebp'] ?? true);
        $this->allowedMimeExtensions = $config['allowedMimeExtensions'] ?? [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
        ];
    }

    public function storeOptional(mixed $file, string $relativeDir): ?string
    {
        if (!$file instanceof UploadedFileInterface) {
            return null;
        }

        $error = $file->getError();
        if ($error === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        if ($error !== UPLOAD_ERR_OK) {
            throw new InvalidArgumentException($this->uploadErrorMessage($error));
        }

        return $this->store($file, $relativeDir);
    }

    public function store(UploadedFileInterface $file, string $relativeDir): string
    {
        $source = $this->sourcePath($file);
        $size = $file->getSize() ?? filesize($source);
        if ($size === false || $size <= 0) {
            throw new InvalidArgumentException('No pudimos leer la imagen. Intenta subir otro archivo.');
        }
        if ($size > $this->maxBytes) {
            throw new InvalidArgumentException(sprintf(
                'La imagen supera el máximo permitido de %s MB.',
                number_format($this->maxBytes / 1024 / 1024, 1, ',', '.'),
            ));
        }

        $mime = $this->detectMime($source);
        if (!isset($this->allowedMimeExtensions[$mime])) {
            throw new InvalidArgumentException('Formato no permitido. Usa JPG, PNG o WEBP.');
        }

        $dimensions = getimagesize($source);
        if ($dimensions === false) {
            throw new InvalidArgumentException('El archivo no parece ser una imagen válida.');
        }

        $relativeDir = $this->safeRelativeDir($relativeDir);
        $targetDir = $this->root . $relativeDir;
        if (!is_dir($targetDir) && !mkdir($targetDir, 0775, true) && !is_dir($targetDir)) {
            throw new RuntimeException('No pudimos preparar la carpeta para guardar la imagen.');
        }

        $outputMime = $this->preferWebp && function_exists('imagewebp') ? 'image/webp' : $mime;
        $extension = $this->allowedMimeExtensions[$outputMime] ?? $this->allowedMimeExtensions[$mime];
        $filename = bin2hex(random_bytes(16)) . '.' . $extension;
        $targetPath = $targetDir . DIRECTORY_SEPARATOR . $filename;

        $this->writeOptimizedImage($source, $targetPath, $mime, $outputMime, (int)$dimensions[0], (int)$dimensions[1]);

        return $relativeDir . '/' . $filename;
    }

    public function delete(?string $relativePath): bool
    {
        if (!$relativePath || !str_starts_with($relativePath, 'uploads/')) {
            return true;
        }

        $target = $this->root . ltrim($relativePath, '/\\');
        $uploadsRoot = realpath($this->root . 'uploads');
        $parent = realpath(dirname($target));
        if (!$uploadsRoot || !$parent || !str_starts_with($parent, $uploadsRoot)) {
            return false;
        }

        if (is_file($target)) {
            return unlink($target);
        }

        return true;
    }

    public function publicPath(?string $relativePath, string $fallback = 'img/placeholder.png'): string
    {
        if ($relativePath && is_file($this->root . ltrim($relativePath, '/\\'))) {
            return '/' . ltrim($relativePath, '/');
        }

        return '/' . ltrim($fallback, '/');
    }

    public function cleanupOrphans(string $relativeDir, array $keepPaths): int
    {
        $relativeDir = $this->safeRelativeDir($relativeDir);
        $dir = $this->root . $relativeDir;
        if (!is_dir($dir)) {
            return 0;
        }

        $keep = array_flip(array_map(fn ($path) => $this->root . ltrim((string)$path, '/\\'), $keepPaths));
        $removed = 0;
        foreach (glob($dir . DIRECTORY_SEPARATOR . '*') ?: [] as $path) {
            if (is_file($path) && !isset($keep[$path]) && $this->delete($relativeDir . '/' . basename($path))) {
                $removed++;
            }
        }

        return $removed;
    }

    private function sourcePath(UploadedFileInterface $file): string
    {
        $uri = $file->getStream()->getMetadata('uri');
        if (!is_string($uri) || !is_file($uri)) {
            throw new InvalidArgumentException('No pudimos leer la imagen temporal.');
        }

        return $uri;
    }

    private function detectMime(string $source): string
    {
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($source);
        if (!is_string($mime)) {
            throw new InvalidArgumentException('No pudimos validar el tipo de imagen.');
        }

        return $mime;
    }

    private function safeRelativeDir(string $relativeDir): string
    {
        $relativeDir = trim(str_replace('\\', '/', $relativeDir), '/');
        if ($relativeDir === '' || str_contains($relativeDir, '..') || str_starts_with($relativeDir, '/')) {
            throw new InvalidArgumentException('Ruta de almacenamiento inválida.');
        }

        return $relativeDir;
    }

    private function writeOptimizedImage(
        string $source,
        string $targetPath,
        string $sourceMime,
        string $outputMime,
        int $width,
        int $height,
    ): void {
        if (!extension_loaded('gd')) {
            if (!copy($source, $targetPath)) {
                throw new RuntimeException('No pudimos guardar la imagen.');
            }

            return;
        }

        $image = match ($sourceMime) {
            'image/jpeg' => imagecreatefromjpeg($source),
            'image/png' => imagecreatefrompng($source),
            'image/webp' => function_exists('imagecreatefromwebp') ? imagecreatefromwebp($source) : false,
            default => false,
        };
        if (!$image) {
            throw new InvalidArgumentException('No pudimos procesar esta imagen.');
        }

        $scale = min(1, $this->maxWidth / $width, $this->maxHeight / $height);
        $targetWidth = max(1, (int)round($width * $scale));
        $targetHeight = max(1, (int)round($height * $scale));
        $canvas = imagecreatetruecolor($targetWidth, $targetHeight);
        if (!$canvas) {
            imagedestroy($image);
            throw new RuntimeException('No pudimos optimizar la imagen.');
        }

        if (in_array($sourceMime, ['image/png', 'image/webp'], true)) {
            imagealphablending($canvas, false);
            imagesavealpha($canvas, true);
            $transparent = imagecolorallocatealpha($canvas, 255, 255, 255, 127);
            imagefilledrectangle($canvas, 0, 0, $targetWidth, $targetHeight, $transparent);
        }

        imagecopyresampled($canvas, $image, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);

        $saved = match ($outputMime) {
            'image/jpeg' => imagejpeg($canvas, $targetPath, (int)env('IMAGE_UPLOAD_JPEG_QUALITY', 82)),
            'image/png' => imagepng($canvas, $targetPath, (int)env('IMAGE_UPLOAD_PNG_COMPRESSION', 6)),
            'image/webp' => function_exists('imagewebp') && imagewebp(
                $canvas,
                $targetPath,
                (int)env('IMAGE_UPLOAD_WEBP_QUALITY', 82),
            ),
            default => false,
        };

        imagedestroy($image);
        imagedestroy($canvas);

        if (!$saved) {
            throw new RuntimeException('No pudimos guardar la imagen optimizada.');
        }
    }

    private function uploadErrorMessage(int $error): string
    {
        return match ($error) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'La imagen supera el tamaño máximo permitido.',
            UPLOAD_ERR_PARTIAL => 'La imagen se subió de forma incompleta. Intenta nuevamente.',
            UPLOAD_ERR_NO_TMP_DIR, UPLOAD_ERR_CANT_WRITE => 'No pudimos guardar la imagen temporalmente.',
            default => 'No pudimos procesar la imagen subida.',
        };
    }
}
