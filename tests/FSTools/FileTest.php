<?php

require_once 'FSTools/FileSystemHarness.php';

class FSTools_FileTest extends FSTools_FileSystemHarness
{
    
    function test() {
        $name = 'test.txt';
        $file = new FSTools_File($name);
        $this->assertFalse($file->exists());
        $file->write('foobar');
        $this->assertTrue($file->exists());
        $this->assertEqual($file->get(), 'foobar');
        $file->delete();
        $this->assertFalse($file->exists());
    }
    
    function testGetNonExistent() {
        $name = 'notfound.txt';
        $file = new FSTools_File($name);
        $this->expectError();
        $this->assertFalse($file->get());
    }
    
    function testHandle() {
        $file = new FSTools_File('foo.txt');
        $this->assertFalse($file->exists());
        $file->open('w');
        $this->assertTrue($file->exists());
        $file->close();
    }
    
}

