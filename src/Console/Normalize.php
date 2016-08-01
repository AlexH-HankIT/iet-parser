<?php

namespace MrCrankHank\IetParser\Console;

use Illuminate\Console\Command;
use MrCrankHank\IetParser\Parser\Parser;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use MrCrankHank\IetParser\Parser\Diff;

class Normalize extends Command
{
    // ToDo: Support multiple adapters

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'iet:file-normalize {type : config/allow} {file : Path}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Parse a iscsitarget config or allow file. And check if its compatible with this parser. If not, ask for permission to fix it.';

    /**
     * Create a new command instance.
     *
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        /*
         * normalize file
         * artisan command
         * check given file for the following:
         * multiple spaces are replaced with one
         * spaces at the start or end are deleted
         * newlines are deleted
         * inline comments are removed
         * merge multi line definitions in one line

         Give user a choice for automatic correction
         */

        $local = new Local(__DIR__ . '\..\..\tests\GlobalOptionParserAdd\files', LOCK_EX);

        $filesystem = new Filesystem($local);
        if ($filesystem->has('iet.test-running.conf')) {
            $filesystem->delete('iet.test-running.conf');
        }

        $filesystem->copy('iet.sample.conf', 'iet.test-running.conf');

        $parser = new Parser($filesystem, 'iet.test-running.conf');

        $originalFileContent = $parser->getRaw();

        // remove spaces and the ending/beginning
        $fileContent = $originalFileContent->map(function($line, $key) {
            return trim($line, ' ');
        });

        // create string from array
        $fileContentString = implode("\n", $fileContent->all());

        // replace multiple newlines with a single one
        $fileContentString = preg_replace('/[\r\n|\n]+/', "\n", $fileContentString);

        // replace multiple spaces with a single one
        $fileContentString = preg_replace('/\s\s+/', ' ', $fileContentString);

        // merge escaped lines into one
        $fileContentString = str_replace("\\\n", '', $fileContentString);

        // create collection
        $fileContent = collect(explode("\n", $fileContentString));

        // check for inline comments
        $fileContent = $fileContent->map(function($line) {
            $position = strpos($line, '#');

            if ($position !== 0 && $position !== false) {
                return trim(substr_replace($line, '', $position), ' ');
            } else {
                return $line;
            }
        });

        // $fileContentString = implode("\n", $fileContent->all());
        // $originalFileContentString = implode("\n", $originalFileContent->all());
        // echo Diff::toString(Diff::compare($originalFileContentString, $fileContentString));
    }
}