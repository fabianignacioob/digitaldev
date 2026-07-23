<?php
declare(strict_types=1);

namespace App\Test\TestCase;

use Cake\TestSuite\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

class WebrootIntegrityTest extends TestCase
{
    public function testOnlyFrontControllerIsPublicPhpFile(): void
    {
        $phpFiles = [];
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(WWW_ROOT));
        /** @var SplFileInfo $file */
        foreach ($iterator as $file) {
            if ($file->isFile() && strtolower($file->getExtension()) === 'php') {
                $phpFiles[] = $file->getRealPath();
            }
        }
        sort($phpFiles);

        $this->assertSame([realpath(WWW_ROOT . 'index.php')], $phpFiles);
    }
}
