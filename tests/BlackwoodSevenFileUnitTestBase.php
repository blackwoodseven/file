<?php
namespace BlackwoodSeven\Tests\File;

class BlackwoodSevenFileUnitTestBase extends \PHPUnit_Framework_TestCase
{
    public $fixturesPath = __DIR__ . '/fixtures';
    public $tempFiles = [];
    public $tempDirs = [];

    public function tearDown()
    {
        $this->cleanupTempFiles();
    }

    /**
     * Cleanup our temporary files.
     */
    public function cleanupTempFiles()
    {
        foreach ($this->tempFiles as $tmpFile) {
            unlink($tmpFile);
        }
        $this->tempFiles = [];

        foreach ($this->tempDirs as $tmpDir) {
            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($tmpDir, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::CHILD_FIRST
            );

            foreach ($files as $fileinfo) {
                $todo = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
                $todo($fileinfo->getRealPath());
            }
            rmdir($tmpDir);
        }
        $this->tempDirs = [];
    }

    /**
     * Like tempnam() but with sane defaults.
     *
     * @see tempnam()
     */
    public function tempnam($path = null, $prefix = 'BlackwoodSevenUnitTest')
    {
        $path = $path ?? sys_get_temp_dir();
        $filename = tempnam($path, $prefix);
        $this->tempFiles[] = $filename;
        return $filename;
    }

    /**
     * Like tempnam() but for directories.
     *
     * @see tempnam()
     */
    public function tempdirnam($path = null, $prefix = 'BlackwoodSevenUnitTest', $mode = 0777, $recursive = true)
    {
        $path = $path ?? sys_get_temp_dir();
        $dirname = tempnam($path, $prefix);
        unlink($dirname);
        $dirname .= '.dir';
        mkdir($dirname, $mode, $recursive);
        $this->tempDirs[] = $dirname;
        return $dirname;
    }
}
