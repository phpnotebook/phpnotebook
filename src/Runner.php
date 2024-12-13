<?php

namespace PHPNotebook\PHPNotebook;

use PHPNotebook\PHPNotebook\Types\Result;
use PHPNotebook\PHPNotebook\Types\SectionType;

class Runner
{
    public static function run(Notebook $notebook) : Result
    {
        // Create a temporary folder for all the work
        $tempDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('phpnotebook_run_', true);
        $runFile = $tempDir . '/run.php';

        if (!mkdir($tempDir) && !is_dir($tempDir)) {
            throw new \RuntimeException(sprintf('Directory "%s" could not be created', $tempDir));
        }

        // Prepare the environment for the run
        self::handleComposer($notebook, $tempDir);

        // Build and run the script
        file_put_contents($runFile, self::generateScript($notebook));

        $output = [];
        $returnVar = 0;

        exec(sprintf(
            '%s -d open_basedir=%s %s 2>&1',
            self::getPHPExecutable(),
            $tempDir,
            $runFile
        ), $output, $returnVar);

        if ($returnVar !== 0) {
            self::cleanup($tempDir);
            throw new \RuntimeException(sprintf("Unable to run notebook: %s", implode("\n", $output)));
        } else {

            $result = new Result();

            // Processing the STDOUT: We'll do an echo SECTIONSTART=UUID\nstuff\nSECTIONEND=UUID\n kinda thing
            $result->stdout = trim(implode("\n", $output));
            $result->files = array_values(array_diff(scandir($tempDir), ['.', '..', 'vendor', 'run.php']));

            // TODO: Might need to read the files into the $notebook object
            self::cleanup($tempDir);

            return $result;

        }
    }


    private static function cleanup(string $folderPath): void
    {
        if (!is_dir($folderPath)) {
            throw new \InvalidArgumentException(sprintf('The path "%s" is not a valid directory.', $folderPath));
        }

        // Use glob to get all files and directories in the folder
        $items = glob($folderPath . DIRECTORY_SEPARATOR . '*', GLOB_MARK);

        foreach ($items as $item) {
            if (is_dir($item)) {
                // Recursively destroy sub-folder
                self::cleanup($item);
            } else {
                // Delete file
                unlink($item);
            }
        }

        // Remove the folder itself
        rmdir($folderPath);
    }

    private static function generateScript(Notebook $notebook) : string
    {
        $script = "<?php\n\n";

        foreach($notebook->sections as $section) {
            if ($section->type == SectionType::PHP) {
                $script .= $section->input . "\n";
            }
        }

        return $script;
    }

    private static function getComposerExecutable() : string
    {

        $composerPath = shell_exec(PHP_OS_FAMILY === 'Windows' ? 'where composer' : 'which composer');

        if ($composerPath === null || trim($composerPath) === '') {
            throw new \RuntimeException('Composer binary not found on the system. Please ensure it is installed and accessible in your PATH.');
        }

        return trim($composerPath);
    }

    private static function getPHPExecutable() : string
    {

        $phpPath = shell_exec(PHP_OS_FAMILY === 'Windows' ? 'where php' : 'which php');

        if ($phpPath === null || trim($phpPath) === '') {
            throw new \RuntimeException('Composer binary not found on the system. Please ensure it is installed and accessible in your PATH.');
        }

        return trim($phpPath);
    }

    private static function handleComposer(Notebook $notebook, string $workingFolder)
    {
        if (!empty($notebook->metadata->composer) && is_array($notebook->metadata->composer)) {

            file_put_contents($workingFolder . '/composer.json', json_encode([
                'require' => $notebook->metadata->composer,
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            // Execute Composer install in the temporary directory
            $composerInstallCmd = sprintf(
                '%s install --no-dev --working-dir=%s 2>&1',
                self::getComposerExecutable(),
                escapeshellarg($workingFolder)
            );

            $output = [];
            $returnVar = 0;

            exec($composerInstallCmd, $output, $returnVar);

            if ($returnVar !== 0) {
                throw new \RuntimeException(sprintf("Composer install failed: %s", implode("\n", $output)));
            }
        }
    }
}