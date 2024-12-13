<?php

namespace PHPNotebook\PHPNotebook;

use PHPNotebook\PHPNotebook\Types\Input;
use PHPNotebook\PHPNotebook\Types\Metadata;
use PHPNotebook\PHPNotebook\Types\Section;
use PHPNotebook\PHPNotebook\Types\SectionType;

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

    public function addFile(string $path, string $name = null)
    {
        // Attaching a file counts as a "section", since you can drag/drop/upload it in the editor
        // and make changes to the metadata there, so:
        $uuid = phpnotebook_generate_uuid();

        // Create a section to load the file
        $section = new Section();
        $section->type = SectionType::File;
        $section->input = $uuid;
        $this->sections[] = $section;

        // And load the input straight into memory for later serialization to disk
        $this->inputs[$uuid] = new Input();
        $this->inputs[$uuid]->uuid = $uuid;
        $this->inputs[$uuid]->mime = mime_content_type($path);
        $this->inputs[$uuid]->name = $name ?? basename($path);
        $this->inputs[$uuid]->base64 = base64_encode(file_get_contents($path));
        
    }

    public function addSection(SectionType $type, string $input)
    {
        if ($type == SectionType::File) {
            throw new \Exception("Incorrect method! Use addFile() to add files");
        }

        $newSection = new Section();
        $newSection->type = $type;
        $newSection->input = $input;

        $this->sections[] = $newSection;

    }
}