<?php
declare(strict_types=1);

namespace App\Test\TestCase\Service;

use App\Service\LocalImageStorageService;
use Cake\TestSuite\TestCase;
use InvalidArgumentException;
use Laminas\Diactoros\UploadedFile;

class LocalImageStorageServiceTest extends TestCase
{
    private string $root;

    protected function setUp(): void
    {
        parent::setUp();
        $this->root = TMP . 'catops-image-storage-test' . DIRECTORY_SEPARATOR;
        $this->removeTree($this->root);
        mkdir($this->root, 0775, true);
    }

    protected function tearDown(): void
    {
        $this->removeTree($this->root);
        parent::tearDown();
    }

    public function testRejectsInvalidFile(): void
    {
        $path = $this->writeFile('fake.png', 'no soy una imagen');
        $service = $this->service();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Formato no permitido');
        $service->store($this->uploadedFile($path, 'image/png', 'fake.png'), 'uploads/sites/1/logos');
    }

    public function testRejectsTooLargeFile(): void
    {
        $path = $this->writePng('big.png', 24, 24);
        $service = $this->service(['maxBytes' => 10]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('supera el máximo');
        $service->store($this->uploadedFile($path, 'image/png', 'big.png'), 'uploads/sites/1/logos');
    }

    public function testStoresWithRandomSafeNameAndRealExtension(): void
    {
        $path = $this->writePng('safe-source.php', 32, 32);
        $service = $this->service();

        $stored = $service->store($this->uploadedFile($path, 'text/plain', '../../logo.php'), 'uploads/sites/1/logos');

        $expectedExtension = function_exists('imagewebp') ? 'webp' : 'png';
        $expectedMime = function_exists('imagewebp') ? 'image/webp' : 'image/png';
        $this->assertMatchesRegularExpression('/^uploads\/sites\/1\/logos\/[a-f0-9]{32}\.' . $expectedExtension . '$/', $stored);
        $this->assertFileExists($this->root . $stored);
        $this->assertSame($expectedMime, mime_content_type($this->root . $stored));
    }

    public function testResizesAndDeletesImage(): void
    {
        $path = $this->writePng('wide.png', 80, 40);
        $service = $this->service(['maxWidth' => 20, 'maxHeight' => 20]);

        $stored = $service->store($this->uploadedFile($path, 'image/png', 'wide.png'), 'uploads/sites/1/products');
        [$width, $height] = getimagesize($this->root . $stored);

        $this->assertSame(20, $width);
        $this->assertSame(10, $height);
        $this->assertTrue($service->delete($stored));
        $this->assertFileDoesNotExist($this->root . $stored);
    }

    public function testFallbackPath(): void
    {
        $service = $this->service();

        $this->assertSame('/img/placeholder.png', $service->publicPath(null));
        $this->assertSame('/img/placeholder.png', $service->publicPath('uploads/sites/1/missing.png'));
    }

    private function service(array $config = []): LocalImageStorageService
    {
        return new LocalImageStorageService($config + ['root' => $this->root]);
    }

    private function uploadedFile(string $path, string $mime, string $clientFilename): UploadedFile
    {
        return new UploadedFile($path, filesize($path), UPLOAD_ERR_OK, $clientFilename, $mime);
    }

    private function writeFile(string $name, string $content): string
    {
        $path = $this->root . $name;
        file_put_contents($path, $content);

        return $path;
    }

    private function writePng(string $name, int $width, int $height): string
    {
        $path = $this->root . $name;
        $image = imagecreatetruecolor($width, $height);
        imagefilledrectangle($image, 0, 0, $width, $height, imagecolorallocate($image, 243, 107, 22));
        imagepng($image, $path);
        imagedestroy($image);

        return $path;
    }

    private function removeTree(string $path): void
    {
        if (!is_dir($path)) {
            return;
        }

        $items = array_diff(scandir($path) ?: [], ['.', '..']);
        foreach ($items as $item) {
            $child = $path . DIRECTORY_SEPARATOR . $item;
            is_dir($child) ? $this->removeTree($child) : unlink($child);
        }
        rmdir($path);
    }
}
