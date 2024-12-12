<?php

namespace PHPNotebook\PHPNotebook\Tests;

use PHPUnit\Framework\TestCase;

class InitializationTest extends TestCase
{
    public function initializes_correctly()
    {
        $notebook = new Notebook;
    }
}