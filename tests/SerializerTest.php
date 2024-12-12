<?php

namespace PHPNotebook\PHPNotebook\Tests;

use PHPNotebook\PHPNotebook\Notebook;
use PHPNotebook\PHPNotebook\Serializer;
use PHPUnit\Framework\TestCase;

class SerializerTest extends TestCase
{
    public function test_create_blank_file()
    {
        $notebook = new Notebook();

        $tempFileName = tempnam(sys_get_temp_dir(), 'phpnotebook_') . '.phpnb';
        
        Serializer::write($notebook, $tempFileName);

        $this->assertFileExists($tempFileName);

    }

    public function test_read_blank_file()
    {
        // Create a blank file
        $notebook = new Notebook();

        $notebook->metadata->title = 'Test Notebook at ' . date('Y-m-d H:i:s');

        $tempFileName = tempnam(sys_get_temp_dir(), 'phpnotebook_') . '.phpnb';

        Serializer::write($notebook, $tempFileName);

        $readNotebook = Serializer::read($tempFileName);

        $this->assertEquals($readNotebook->metadata->title, $notebook->metadata->title);

    }
}