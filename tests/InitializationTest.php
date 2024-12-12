<?php

namespace PHPNotebook\PHPNotebook\Tests;

use PHPNotebook\PHPNotebook\Notebook;
use PHPUnit\Framework\TestCase;

class InitializationTest extends TestCase
{
    public function test_initializes_correctly()
    {
        $notebook = new Notebook();

        $this->assertInstanceOf(Notebook::class, $notebook);

        $this->assertEquals('0.0.1', $notebook->version);
        $this->assertEquals('Untitled Notebook', $notebook->metadata->title);
        $this->assertEquals('8.1', $notebook->metadata->runtime);
    }
}