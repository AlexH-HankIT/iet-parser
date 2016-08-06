<?php

namespace MrCrankHank\IetParser\tests;

use PHPUnit_Framework_TestCase;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use MrCrankHank\IetParser\Parser\TargetParser;
use MrCrankHank\IetParser\Parser\Normalizer;

class TargetParserDelete extends PHPUnit_Framework_TestCase
{
    protected $sampleFile = 'iet.sample.conf';
    protected $testFile = 'iet.test-running.conf';
    protected $dirs = ['case1_files'];

    public function testAddTarget()
    {
        $file = 'iet.expected.testAddTarget.conf';

        foreach($this->dirs as $dir) {
            $dir = __DIR__ . DIRECTORY_SEPARATOR . $dir;

            $objects = $this->normalize($dir, 'iqn.2016-08.test.ing.host:testAdd');

            if ($objects['normalizer']->check()) {
                $objects['parser']->addTarget()->write();
            } else {
                $this->fail("The normalizer did not properly normalize the file!");
            }

            $this->assertFileEquals($dir . DIRECTORY_SEPARATOR . $file, $dir . DIRECTORY_SEPARATOR . $this->testFile);
        }
    }

    public function testAddTargetDuplicationError()
    {
        $this->expectException('MrCrankHank\IetParser\Exceptions\DuplicationErrorException');

        foreach($this->dirs as $dir) {
            $dir = __DIR__ . DIRECTORY_SEPARATOR . $dir;

            $objects = $this->normalize($dir, 'iqn.2016-08.test.ing.host:server1');

            if ($objects['normalizer']->check()) {
                $objects['parser']->addTarget()->write();
            } else {
                $this->fail("The normalizer did not properly normalize the file!");
            }
        }
    }

    public function tearDown() {
        foreach ($this->dirs as $dir) {
            $dir = __DIR__ . DIRECTORY_SEPARATOR . $dir;

            $file = $dir . DIRECTORY_SEPARATOR . $this->testFile;

            if (file_exists($file)) {
                unlink($file);
            }
        }
    }

    private function normalize($dir, $target) {
        // Create new filesystem adapter
        $local = new Local($dir, LOCK_EX);

        // create new filesystem
        $filesystem = new Filesystem($local);

        // for testing purposes: copy the sample file. So we don't change the real data
        $filesystem->copy($this->sampleFile, $this->testFile);

        // create parser instance
        $parser = new TargetParser($filesystem, $this->testFile, $target);

        // create normalizer instance
        $normalizer = new Normalizer($parser);

        // normalize the file
        $normalizer->write();

        $parser->refresh();

        return [
            'normalizer' => $normalizer,
            'parser' => $parser,
            'filesystem' => $filesystem
        ];
    }
}