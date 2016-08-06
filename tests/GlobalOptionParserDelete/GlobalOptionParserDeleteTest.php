<?php

namespace MrCrankHank\IetParser\tests;

use PHPUnit_Framework_TestCase;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use MrCrankHank\IetParser\Parser\GlobalOptionParser;
use MrCrankHank\IetParser\Parser\Normalizer;

class GlobalOptionParserDelete extends PHPUnit_Framework_TestCase {
    protected $sampleFile = 'iet.sample.conf';
    protected $testFile = 'iet.test-running.conf';
    protected $dirs = ['case1_files', 'case2_files'];

    public function testDelete() {
        $file = 'iet.expected.testDelete.conf';

        foreach($this->dirs as $dir) {
            $dir = __DIR__ . DIRECTORY_SEPARATOR . $dir;

            $objects = $this->normalize($dir);

            if ($objects['normalizer']->check()) {
                $objects['parser']->delete('IncomingUser user2 password2')->write();
            } else {
                $this->fail("The normalizer did not properly normalize the file!");
            }

            $this->assertFileEquals($dir . DIRECTORY_SEPARATOR . $file, $dir . DIRECTORY_SEPARATOR . $this->testFile);
        }
    }

    public function testNotFoundError() {
        $this->expectException('MrCrankHank\IetParser\Exceptions\NotFoundException');

        foreach($this->dirs as $dir) {
            $dir = __DIR__ . DIRECTORY_SEPARATOR . $dir;

            $objects = $this->normalize($dir);

            if ($objects['normalizer']->check()) {
                $objects['parser']->delete('This wont be found')->write();
            } else {
                $this->fail("The normalizer did not properly normalize the file!");
            }
        }
    }

    public function testDeleteIncomingUserHelperMethod() {
        $file = 'iet.expected.testDeleteIncomingUserHelperMethod.conf';

        foreach($this->dirs as $dir) {
            $dir = __DIR__ . DIRECTORY_SEPARATOR . $dir;

            $objects = $this->normalize($dir);

            if ($objects['normalizer']->check()) {
                $objects['parser']->deleteIncomingUser('user2',  'password2')->write();
            } else {
                $this->fail("The normalizer did not properly normalize the file!");
            }

            $this->assertFileEquals($dir . DIRECTORY_SEPARATOR . $file, $dir . DIRECTORY_SEPARATOR . $this->testFile);
        }
    }

    public function testDeleteOutgoingUserHelperMethod() {
        $file = 'iet.expected.testDeleteOutgoingUserHelperMethod.conf';

        foreach($this->dirs as $dir) {
            $dir = __DIR__ . DIRECTORY_SEPARATOR . $dir;

            $objects = $this->normalize($dir);

            if ($objects['normalizer']->check()) {
                $objects['parser']->deleteOutgoingUser('user2',  'password2')->write();
            } else {
                $this->fail("The normalizer did not properly normalize the file!");
            }

            $this->assertFileEquals($dir . DIRECTORY_SEPARATOR . $file, $dir . DIRECTORY_SEPARATOR . $this->testFile);
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

    private function normalize($dir) {
        // Create new filesystem adapter
        $local = new Local($dir, LOCK_EX);

        // create new filesystem
        $filesystem = new Filesystem($local);

        // for testing purposes: copy the sample file. So we don't change the real data
        $filesystem->copy($this->sampleFile, $this->testFile);

        // create normalizer instance
        $normalizer = new Normalizer($filesystem, $this->testFile);

        // normalize the file
        $normalizer->write();

        // create parser instance
        $parser = new GlobalOptionParser($filesystem, $this->testFile);

        return [
            'normalizer' => $normalizer,
            'parser' => $parser,
            'filesystem' => $filesystem
        ];
    }
}