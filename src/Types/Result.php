<?php

namespace PHPNotebook\PHPNotebook\Types;

class Result
{
    // TODO: Return stdout per section, by echoing out uuids in a mime-split-part kinda way and then parsing that out
    public string $stdout;
    public array $files;
}
