<?php
namespace BlackwoodSeven\Tests\File;

use BlackwoodSeven\File\AtomicTempFileObject;

class AtomicTempFileObjectUnitTest extends \PHPUnit_Framework_TestCase
{
    public function testPersist()
    {
        $filename = tempnam(sys_get_temp_dir(), 'AtomicTempFileObjectUnitTest');

        $file = new AtomicTempFileObject($filename);
        $file->fwrite("TEST1");
        $file->persistOnClose();
        unset($file);

        $this->assertEquals(5, filesize($filename), 'File is not correctly persisted - size.');
        $this->assertEquals("TEST1", file_get_contents($filename), 'File is not correctly persisted - content.');
    }

    public function testPersistIfChanged()
    {
        $filename = tempnam(sys_get_temp_dir(), 'AtomicTempFileObjectUnitTest');

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
        $filename = tempnam(sys_get_temp_dir(), 'AtomicTempFileObjectUnitTest');

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
}
