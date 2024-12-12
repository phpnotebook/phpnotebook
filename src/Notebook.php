<?php

namespace PHPNotebook\PHPNotebook;

use PHPNotebook\PHPNotebook\Types\Input;
use PHPNotebook\PHPNotebook\Types\Metadata;
use PHPNotebook\PHPNotebook\Types\Section;

class Notebook
{

    /**
     * @var string The format/serializer version to use.
     */
    public string $version;

    /**
     * @var bool If true, will update the modified timestamp on next write
     */
    public bool $isChanged = false;

    /**
     * @var Metadata All relevant metadata
     */
    public Metadata $metadata;

    /**
     * @var Section[] The content of the notebook itself
     */
    public array $sections;

    /**
     * @var array Input[]
     */
    public array $inputs;

    public function __construct()
    {
        // Initialize some sane defaults
        $this->version = '0.0.1';
        $this->sections = [];
        $this->inputs = [];

        $this->metadata = new Metadata;
        $this->metadata->title = "Untitled Notebook";
        $this->metadata->description = "";
        $this->metadata->runtime = '8.1';
        $this->metadata->created = new \DateTime();
        $this->metadata->modified = new \DateTime();
        $this->metadata->authors = [];
        $this->metadata->composer = [];
    }

    public function addFile(string $path)
    {
        $uuid = phpnotebook_generate_uuid();

        $this->inputs[$uuid] = new Input();

        $this->inputs[$uuid]->uuid = $uuid;
        $this->inputs[$uuid]->mime = mime_content_type($path);
        $this->inputs[$uuid]->name = basename($path);
        $this->inputs[$uuid]->base64 = base64_encode(file_get_contents($path));
        
    }
}