<?php

namespace PHPNotebook\PHPNotebook\Types;

class Output
{
    public string $uuid;
    public string $name;
    public string $mime;
    public string $base64;

    public static function stdout(string $content) : Output
    {
        $output = new Output();
        $output->uuid = phpnotebook_generate_uuid();
        $output->name = 'stdout.txt';
        $output->mime = "text/plain";
        $output->base64 = base64_encode($content);
        return $output;
    }
}
