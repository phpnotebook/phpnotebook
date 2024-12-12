<?php

namespace PHPNotebook\PHPNotebook;

class Notebook
{
    public function __construct()
    {
        // Initialize some sane defaults
        $this->version = '0.0.1';
        $this->sections = [];

        $this->metadata = new Metadata;
        $this->metadata->title = "Untitled Notebook";
        $this->metadata->description = "";
        $this->metadata->runtime = '8.3';
        $this->metadata->created = new \DateTime();
        $this->metadata->modified = new \DateTime();
        $this->metadata->authors = [];
        $this->metadata->composer = [];
    }
}