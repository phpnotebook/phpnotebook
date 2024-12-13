<?php

namespace PHPNotebook\PHPNotebook\Tests;

use PHPNotebook\PHPNotebook\Notebook;
use PHPNotebook\PHPNotebook\Runner;
use PHPNotebook\PHPNotebook\Serializer;
use PHPNotebook\PHPNotebook\Types\SectionType;
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

    public function test_adding_input()
    {
        $notebook = new Notebook();

        // We'll create a simple input as a temporary file, then attach that to the notebook
        $tempInputFile = tempnam(sys_get_temp_dir(), 'phpnotebook_input_');
        $tempFileName = tempnam(sys_get_temp_dir(), 'phpnotebook_') . '.phpnb';
        $randomBytes = random_bytes(64);
        file_put_contents($tempInputFile, $randomBytes);

        $notebook->addFile($tempInputFile);

        Serializer::write($notebook, $tempFileName);

        // This should now result in a ZIP archive at $tempFileName which includes an input/ folder!

        $tempExtractDir = sys_get_temp_dir() . '/phpnotebook_extract_' . uniqid();

        mkdir($tempExtractDir);

        $zip = new \ZipArchive();
        if ($zip->open($tempFileName) === true) {
            $zip->extractTo($tempExtractDir);
            $zip->close();
        } else {
            $this->fail("Failed to open the archive: $tempFileName");
        }

        $inputsDir = $tempExtractDir . '/input';
        $this->assertDirectoryExists($inputsDir);

        // Check if the inputs/ directory contains exactly one file
        $inputFiles = scandir($inputsDir);
        $inputFiles = array_diff($inputFiles, ['.', '..']);
        $this->assertCount(1, $inputFiles);

        // Check that the content of this file is a JSON payload with decodeable bytes
        $inputFileContent = file_get_contents($inputsDir . '/' . array_values($inputFiles)[0]);
        $inputFileObject = json_decode($inputFileContent);

        $this->assertEquals($randomBytes, base64_decode($inputFileObject->base64));

        // Cleanup the temporary folder after assertions
        array_map('unlink', glob($inputsDir . '/*'));
        rmdir($inputsDir);
        rmdir($tempExtractDir);

    }

    public function test_notebook_runner()
    {
        $notebook = new Notebook();

        // Add a code section that will produce output to a file, then verify that output shows up
        $randomString = bin2hex(random_bytes(16));

        $notebook->addSection(SectionType::PHP, "echo '".$randomString."';");

        // Running the notebook should now cause the output to be written and returned

        $tempFileName = tempnam(sys_get_temp_dir(), 'phpnotebook_') . '.phpnb';
        Serializer::write($notebook, $tempFileName);

        $this->assertFileExists($tempFileName);

        $result = Runner::run($notebook);

        $this->assertEquals($result->stdout, $randomString);

    }
}