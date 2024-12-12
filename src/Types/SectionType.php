<?php

namespace PHPNotebook\PHPNotebook\Types;

enum SectionType: string
{

    case PHP = 'php';
    case File = 'file';
    case Markdown = 'markdown';
    case Text = 'text';
}
