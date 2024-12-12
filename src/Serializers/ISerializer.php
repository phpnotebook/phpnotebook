<?php

namespace PHPNotebook\PHPNotebook\Serializers;

use PHPNotebook\PHPNotebook\Notebook;

interface ISerializer
{
    public function serialize(Notebook $notebook) : string;
    public function deserialize(string $workingFolder) : Notebook;
    public function serializeMetadata(Notebook $notebook) : string;
    public function serializeNotebook(Notebook $notebook, string $workingFolder) : string;
}
