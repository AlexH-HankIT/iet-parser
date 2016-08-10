<?php

namespace MrCrankHank\IetParser;

use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use MrCrankHank\IetParser\Parser\Normalizer;

trait TestTrait {
    protected static $testFile = 'iet.test-running.conf';
    protected static $delete = true;

    public static function tearDownAfterClass()
    {
       self::_removeTestFiles(__DIR__);
    }

    public static function setUpBeforeClass()
    {
        self::_removeTestFiles(__DIR__);
    }

    public function tearDown()
    {
        self::_removeTestFiles(__DIR__);
    }

    private static function _removeTestFiles($dir)
    {
        $files = scandir($dir);

        foreach ($files as $key => $value) {
            $path = realpath($dir.DIRECTORY_SEPARATOR.$value);
            if(!is_dir($path)) {
                if($value === self::$testFile && self::$delete === true) {
                    unlink($path);
                }
            } else if($value != "." && $value != "..") {
                self::_removeTestFiles($path);
            }
        }
    }

    private static function normalize($dir, $sourceFile)
    {
        // Create new filesystem adapter
        $local = new Local($dir, LOCK_EX);

        // create new filesystem
        $filesystem = new Filesystem($local);

        // for testing purposes: copy the sample file. So we don't change the real data
        $filesystem->copy($sourceFile, self::$testFile);

        // create normalizer instance
        $normalizer = new Normalizer($filesystem, self::$testFile);

        // normalize the file
        $normalizer->write();

        return [
            'normalizer' => $normalizer,
            'filesystem' => $filesystem
        ];
    }
}