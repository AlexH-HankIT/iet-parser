<?php

namespace MrCrankHank\IetParser\Console;

use Illuminate\Console\Command;
use MrCrankHank\IetParser\Parser\Parser;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use MrCrankHank\IetParser\Parser\Diff;
use MrCrankHank\IetParser\Parser\Normalize as ParserNormalize;

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
        $local = new Local(__DIR__ . '\..\..\tests\GlobalOptionParserAdd\files', LOCK_EX);

        $filesystem = new Filesystem($local);
        if ($filesystem->has('iet.test-running.conf')) {
            $filesystem->delete('iet.test-running.conf');
        }

        $filesystem->copy('iet.sample.conf', 'iet.test-running.conf');

        $normalize = new ParserNormalize($filesystem, 'iet.test-running.conf');

        echo $normalize->normalizeDiff();
    }
}