<?php
namespace BlackwoodSeven\Tests\File;

use BlackwoodSeven\File\AtomicTempFileObject;

class AtomicTempFileObjectUnitTest extends BlackwoodSevenFileUnitTestBase
{
    public function testPersist()
    {
        $filename = $this->tempnam();

        $file = new AtomicTempFileObject($filename);
        $file->fwrite("TEST1");
        $file->persistOnClose();
        unset($file);

        $this->assertEquals(5, filesize($filename), 'File is not correctly persisted - size.');
        $this->assertEquals("TEST1", file_get_contents($filename), 'File is not correctly persisted - content.');
    }

    public function testPersistIfChanged()
    {
        $filename = $this->tempnam();

        $file = new AtomicTempFileObject($filename);
        $file->fwrite("TEST1");
        $file->persistOnClose();
        unset($file);

        $originalINode = fileinode($filename);

        $file = new AtomicTempFileObject($filename);
        $file->fwrite("TEST1");
        $file->persistOnClose();
        unset($file);

        $this->assertEquals($originalINode, fileinode($filename), 'File was not correctly not persisted.');

        $file = new AtomicTempFileObject($filename);
        $file->fwrite("TEST2");
        $file->persistOnClose();
        unset($file);

        $this->assertNotEquals($originalINode, fileinode($filename), 'File was not correctly persisted.');
    }

    public function testModificationTime()
    {
        $filename = $this->tempnam();

        $mTime = time() - 86400;

        $file = new AtomicTempFileObject($filename);
        $file->fwrite("TEST1");
        $file->persistOnClose();
        $file->setModifiedTime($mTime);
        unset($file);

        $this->assertEquals($mTime, filemtime($filename), 'File\'s modification time was not correctly set.');

        $file = new AtomicTempFileObject($filename);
        $file->fwrite("TEST2");
        $file->persistOnClose();
        unset($file);

        $this->assertLessThan(filemtime($filename), $mTime, 'File\'s modification time was not correctly set.');
    }

    public function testCreateDirectory()
    {
        $dirname = $this->tempdirnam();
        $filename = $dirname . '/testsubdir/testfile';

        $file = new AtomicTempFileObject($filename);
        $file->fwrite("TEST1");
        $file->createDirectoryOnPersist();
        $file->persistOnClose();
        unset($file);

        $this->assertEquals(5, filesize($filename), 'File is not correctly persisted - size.');
        $this->assertEquals("TEST1", file_get_contents($filename), 'File is not correctly persisted - content.');
    }

    /**
     * Test proper exception thrown if create directory is not set.
     *
     * @expectedException \RuntimeException
     * @expectedExceptionMessageRegExp  /^Could not move /
     */
    public function testCreateDirectoryFail()
    {
        $dirname = $this->tempdirnam();
        $filename = $dirname . '/testsubdir/testfile';

        $file = new AtomicTempFileObject($filename);
        $file->fwrite("TEST1");
        $file->persistOnClose();
        unset($file);
    }

    public function testProcess()
    {
        $txtFile = $this->fixturesPath . '/txtfile1-input';
        $txtFileObject = new \SplFileObject($txtFile);

        $dirname = $this->tempdirnam();

        $destFile = new AtomicTempFileObject($dirname . '/txtfile1-result');
        $destFile->process($txtFileObject, function ($line, $lineNum, $input, $output) use ($dirname) {
            $no = ($lineNum % 2);
            if ($no == 0) {
                $output->fwrite($line);
            }
        });
        $destFile->createDirectoryOnPersist();
        $destFile->persistOnClose();
        unset($destFile);

        $this->assertTrue(file_exists($dirname . '/txtfile1-result'), 'File was not written.');

        $expected = file_get_contents($this->fixturesPath . '/txtfile1-result');
        $result = file_get_contents($dirname . '/txtfile1-result');
        $this->assertEquals($expected, $result, 'File was not processed correctly.');
    }

}
