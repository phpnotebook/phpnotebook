<?php

namespace PHPNotebook\PHPNotebook\Serializers;

use PHPNotebook\PHPNotebook\Notebook;

interface ISerializer
{
    public function serialize(Notebook $notebook) : string;
    public function deserialize(object $data) : Notebook;
}
