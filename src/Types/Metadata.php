<?php

namespace PHPNotebook\PHPNotebook\Types;

class Metadata
{
    /**
     * @var string The PHP version required to execute this notebook
     */
    public string $runtime;

    /**
     * @var \DateTime The UTC datestamp that this notebook was created
     */
    public \DateTime $created;

    /**
     * @var \DateTime
     */
    public \DateTime $modified;

    /**
     * @var string[] A list of authors as strings
     */
    public array $authors;

    /**
     * @var string The built-in title for this notebook
     */
    public string $title;

    /**
     * @var string An optional description for the contents of this notebook
     */
    public string $description;

    /**
     * @var array The array of composer dependencies to install before running this script
     */
    public array $composer;
}
