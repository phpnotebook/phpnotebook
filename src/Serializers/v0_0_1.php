<?php

namespace PHPNotebook\PHPNotebook\Serializers;

use PHPNotebook\PHPNotebook\Notebook;
use PHPNotebook\PHPNotebook\Types\Input;
use PHPNotebook\PHPNotebook\Types\Output;
use PHPNotebook\PHPNotebook\Types\Section;
use PHPNotebook\PHPNotebook\Types\SectionType;
use PhpParser\Node\Param;
use PHPUnit\Framework\Exception;

class v0_0_1 implements ISerializer
{

    /**
     * @param Notebook $notebook The input object to serialize
     * @return string The serialized string content ready for writing to disk
     */
    public function serialize(Notebook $notebook): string
    {

        if ($notebook->version !== "0.0.1")
            throw new \Exception("Invalid serializer!");

        // Key fields
        $serialized = [];
        $serialized['version'] = "0.0.1";
        $serialized['metadata'] = [
            "runtime" => $notebook->metadata->runtime,
            "created" => $notebook->metadata->created->format(DATE_W3C),
            "modified" => ($notebook->isChanged) ? (new \DateTime())->format(DATE_W3C) : $notebook->metadata->modified->format(DATE_W3C),
            "authors" => $notebook->metadata->authors,
            "title" => $notebook->metadata->title,
            "description" => $notebook->metadata->description,
        ];

        $serialized['sections'] = [];

        foreach($notebook->sections as $section) {
            $serialized['sections'][] = match ($section->type) {
                "php", "file", "markdown", "text" => $this->serializeSection($section),
                default => throw new \Exception("Invalid section type " . $section->type->value),
            };
        }

        return json_encode($serialized);

    }

    public function deserialize(object $data, string $workingFolder): Notebook
    {
        // Validate against incoming JSON blob data
        // Then create a Notebook and populate by decoding from the file
        $blank = new Notebook();

        // Write basic and supporting information
        $blank->version = $data->version;

        // Deserialize the metadata
        $blank->metadata->runtime = $data->metadata->runtime;
        $blank->metadata->created = \DateTime::createFromFormat(DATE_W3C, $data->metadata->created);
        $blank->metadata->modified = \DateTime::createFromFormat(DATE_W3C, $data->metadata->modified);
        $blank->metadata->authors = $data->metadata->authors;
        $blank->metadata->title = $data->metadata->title;
        $blank->metadata->description = $data->metadata->description;

        // And populate the sections
        foreach($data->sections as $section) {

            $newSection = new Section();

            $newSectionType = SectionType::tryFrom($section->type);

            if ($newSectionType == null) {
                throw new Exception("Invaild type: {$section->type}");
            }

            $newSection->type = $newSectionType;
            $newSection->input = $section->input;

            if ($newSection->type == SectionType::PHP) {

                // $output should point to a file on disk, so:
                $outputFile = $workingFolder . "/output/" . $section->output;

                if (!file_exists($outputFile)) {
                    throw new Exception("Unable to read: Required output file is missing from archive");
                } else {

                    // The output file can now be decoded and loaded into memory
                    $output = json_decode(file_get_contents($outputFile));

                    $newSection->output = new Output();
                    $newSection->output->uuid = $output->uuid;
                    $newSection->output->name = $output->name;
                    $newSection->output->mime = $output->mime;
                    $newSection->output->base64 = $output->base64;

                }

            }

            if ($newSection->type == SectionType::File) {

                // $section->input should point to a file on disk
                $inputFile = $workingFolder . "/input/" . $section->input;

                if (!file_exists($inputFile)) {
                    throw new Exception("Unable to read: Required input file is missing from archive");
                } else {

                    // The input file will be loaded into memory here, though that might not be the smartest approach!
                    // Better might be to write them into php://temp handles and return an array of those by uuid?

                    $input = json_decode(file_get_contents($inputFile));
                    $inputObject = new Input();

                    $inputObject->uuid = $input->uuid;
                    $inputObject->name = $input->name;
                    $inputObject->mime = $input->mime;
                    $inputObject->base64 = $input->base64;

                    $blank->inputs[$inputObject->uuid] = $inputObject;

                }

            }

            $blank->sections[] = $newSection;

        }

        return $blank;

    }

    private function serializeSection(Section $section) : array
    {
        return [
            'type' => $section->type->value,
            'input' => $section->input,
            'output' => $section->output
        ];
    }

    private function unserializeSection(object $section) : Section
    {
        $blank = new Section();

        $type = SectionType::tryFrom($section->type);

        if ($type == null) {
            throw new Exception("Invaild type: {$section->type}");
        }

        $blank->type = $type;
        $blank->input = $section->input;

        if (property_exists($section, 'output') && $section->output != null) {
            $blank->output = new Output();
            $blank->output->uuid = $section->output->uuid;
            $blank->output->mime = $section->output->mime;
            $blank->output->base64 = $section->output->base64;
            $blank->output->name = $section->output->name;
        }

        return $blank;
    }

    public function serializeMetadata(Notebook $notebook): string
    {
        if ($notebook->version !== "0.0.1")
            throw new \Exception("Invalid serializer!");

        return json_encode([
            "runtime" => $notebook->metadata->runtime,
            "created" => $notebook->metadata->created->format(DATE_W3C),
            "modified" => ($notebook->isChanged) ? (new \DateTime())->format(DATE_W3C) : $notebook->metadata->modified->format(DATE_W3C),
            "authors" => $notebook->metadata->authors,
            "composer" => $notebook->metadata->composer,
            "title" => $notebook->metadata->title,
            "description" => $notebook->metadata->description,
        ], JSON_PRETTY_PRINT);
    }

    public function serializeNotebook(Notebook $notebook, string $workingFolder): string
    {
        // Generate out a notebook.json file, but also render out any inputs and outputs
        $sections = [];

        foreach($notebook->sections as $section) {
            $sections[] = [
                'type' => $section->type->value,
                'input' => $section->input,
                'output' => $section->input,
            ];
        }

        return json_encode($sections, JSON_PRETTY_PRINT);

    }
}
