<?php

/**
 * This file handles the normalization
 * of a iet config file via console
 *
 * PHP version 5.6
 *
 * @category Console
 * @package  MrCrankHank\IetParser\Console
 * @author   Alexander Hank <mail@alexander-hank.de>
 * @license  Apache License 2.0 http://www.apache.org/licenses/LICENSE-2.0.txt
 * @link     null
 */

namespace MrCrankHank\IetParser\Console;

use Illuminate\Console\Command;
use MrCrankHank\IetParser\Parser\Parser;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use MrCrankHank\IetParser\Parser\Diff;
use MrCrankHank\IetParser\Parser\Normalizer as ParserNormalize;

/*
 * ToDo: Support multiple adapters
 */

/**
 * Class Normalize
 *
 * @category Console
 * @package  MrCrankHank\IetParser\Console
 * @author   Alexander Hank <mail@alexander-hank.de>
 * @license  Apache License 2.0 http://www.apache.org/licenses/LICENSE-2.0.txt
 * @link     null
 */
class Normalize extends Command
{
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
    protected $description = <<< EOF
Parse a iscsitarget config or allow file
And check if its compatible with this parser.
If not, ask for permission to fix it
EOF;

    /**
     * This method is called, when the user executes php artisan iet:file-normalize
     *
     * @return void
     */
    public function handle()
    {
        $local = new Local(__DIR__ . '\..\..\tests\OptionParserAdd\files', LOCK_EX);

        $filesystem = new Filesystem($local);
        if ($filesystem->has('iet.test-running.conf')) {
            $filesystem->delete('iet.test-running.conf');
        }

        $filesystem->copy('iet.sample.conf', 'iet.test-running.conf');

        $normalize = new ParserNormalize($filesystem, 'iet.test-running.conf');

        echo $normalize->normalizeDiff();
    }
}