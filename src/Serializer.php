<?php

namespace PHPNotebook\PHPNotebook;

use PHPNotebook\PHPNotebook\Serializers\ISerializer;
use PHPNotebook\PHPNotebook\Serializers\v0_0_1;
use PHPNotebook\PHPNotebook\Types\Metadata;
use PHPUnit\Framework\Exception;

class Serializer
{

    public static function getSerializer(string $version = "0.0.1") : ISerializer
    {
        switch($version)
        {
            case "0.0.1": return new v0_0_1();
            default:
                throw new \Exception("No serializers for version $version");
        }
    }

    public static function write(Notebook $notebook, string $path) : bool
    {

        $serializer = self::getSerializer($notebook->version);

        // Create a temporary folder to work in
        $tempDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('phpnotebook_', true);
        if (!mkdir($tempDir, 0777, true) && !is_dir($tempDir)) {
            throw new \Exception("Failed to create temporary directory.");
        }

        // Add some dummy content to the temporary folder for testing
        // TODO: Write the actual file structure in here: metadata, notebook, inputs and outputs
        file_put_contents("$tempDir/metadata.json", $serializer->serializeMetadata($notebook));
        file_put_contents("$tempDir/notebook.json", $serializer->serializeNotebook($notebook, $tempDir));

        // Path for the temporary compressed ZIP file
        $tempZipFile = tempnam(sys_get_temp_dir(), uniqid('phpnotebook_', true)) . '.zip';

        $zip = new \ZipArchive();
        if ($zip->open($tempZipFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            throw new \Exception("Failed to create ZIP archive.");
        }

        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($tempDir));
        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $localName = str_replace($tempDir . DIRECTORY_SEPARATOR, '', $file->getPathname());
                $zip->addFile($file->getPathname(), $localName);
            }
        }

        $zip->close();

        // If we got this far, we have a real ZIP we can export, so:
        rename($tempZipFile, $path);

        // Clean up temporary directory and its contents
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($tempDir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $file) {
            if ($file->isDir()) {
                rmdir($file->getPathname());
            } else {
                unlink($file->getPathname());
            }
        }

        rmdir($tempDir);

        return true;
    }

    public static function read(string $path) : Notebook
    {

        // Open the file by unpacking it into a temp path, then populating everything
        
        if (!file_exists($path) || pathinfo($path, PATHINFO_EXTENSION) !== 'phpnb') {
            throw new \Exception("Invalid file. The file must exist and have a .phpnb extension.");
        }

        $zip = new \ZipArchive();

        if ($zip->open($path) !== true) {
            throw new \Exception("Invalid file. The file is not a valid phpnb file.");
        }

        $tempDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('phpnotebook_', true);

        if (!mkdir($tempDir, 0777, true) && !is_dir($tempDir)) {
            throw new \Exception("Failed to create temporary directory for reading.");
        }

        if (!$zip->extractTo($tempDir)) {
            $zip->close();
            throw new \Exception("Invalid file. Failed to extract while reading.");
        }

        $content = self::readContent($tempDir . "metadata.json");

        $serializer = self::getSerializer($content->version);

        return $serializer->deserialize($content, $tempDir);

    }

    private static function readContent(string $path) : object
    {
        if (!file_exists($path)) {
            throw new Exception("Invalid file. metadata.json not found in .phpnb archive");
        }

        $jsonData = file_get_contents($path);

        $decodedData = json_decode($jsonData);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception("Invalid file. Unable to decode metadata.json: " . json_last_error_msg());
        }

        return $decodedData;
    }

    /**
     * @param string $path
     * @return Metadata
     * @throws \Exception
     */
    private static function readMetadata(string $path) : Metadata
    {

        if (!file_exists($path)) {
            throw new Exception("Invalid file. metadata.json not found in .phpnb archive");
        }

        $jsonData = file_get_contents($path);

        $decodedData = json_decode($jsonData, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception("Invalid file. Unable to decode metadata.json: " . json_last_error_msg());
        }

        $metadata = new Metadata();

        // Deserialize the metadata
        $metadata->title = $decodedData->title;
        $metadata->description = $decodedData->description;
        $metadata->runtime = $decodedData->runtime;
        $metadata->created = \DateTime::createFromFormat(DATE_W3C, $decodedData->created);
        $metadata->modified = \DateTime::createFromFormat(DATE_W3C, $decodedData->modified);
        $metadata->authors = $decodedData->authors;
        $metadata->composer = $decodedData->composer;

        return $metadata;
    }

}