<?php

namespace PHPNotebook\PHPNotebook\Types;

class Section
{
    /**
     * @var string Unique identifier for this section, used in the runtime
     */
    public string $uuid;

    /**
     * @var SectionType The type of section (changes how the processor handles it)
     */
    public SectionType $type;

    /**
     * @var string Input required by this section type
     */
    public string $input;

    /**
     * @var ?Output The output of executing this section (if relevant)
     */
    public ?Output $output;
}
