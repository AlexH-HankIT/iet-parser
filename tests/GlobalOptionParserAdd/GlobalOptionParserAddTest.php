<?php

namespace MrCrankHank\IetParser\tests;

use MrCrankHank\IetParser\Exceptions\DuplicationErrorException;
use MrCrankHank\IetParser\Parser\GlobalOptionParser;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use MrCrankHank\IetParser\Parser\Normalizer;
use MrCrankHank\IetParser\Parser\Diff;
use PHPUnit_Framework_TestCase;

/**
 * Class GlobalOptionParserTestAdd
 * @package MrCrankHank\IetParser\tests
 */
class GlobalOptionParserTestAdd extends PHPUnit_Framework_TestCase {
    protected $sampleFile = 'iet.sample.conf';
    protected $testFile = 'iet.test-running.conf';
    protected $dirs = ['case1_files', 'case2_files'];

    /**
     * Test if I can add a parameter to the file
     */
    public function testAdd() {
        $file = 'iet.expected.testAdd.conf';

        foreach ($this->dirs as $dir) {
            $dir = __DIR__ . DIRECTORY_SEPARATOR . $dir;

            $objects = $this->normalize($dir);

            if ($objects['normalizer']->check()) {
                $objects['parser']->add("IncomingUser user password")->write();
            } else {
                $this->fail("The normalizer did not properly normalize the file!");
            }

            $this->assertFileEquals($dir . DIRECTORY_SEPARATOR . $file, $dir . DIRECTORY_SEPARATOR . $this->testFile);
        }
    }

    /**
     * Test if the duplication check is working
     */
    public function testDuplicationError() {
        foreach ($this->dirs as $dir) {
            $dir = __DIR__ . DIRECTORY_SEPARATOR . $dir;

            $objects = $this->normalize($dir);

            if ($objects['normalizer']->check()) {
                try {
                    $objects['parser']->add("IncomingUser user password")->write();
                    $objects['parser']->add("IncomingUser user password")->write();
                    $this->fail("Test did not throw DuplicationError exception!");
                } catch (DuplicationErrorException $e) {
                    $this->assertEquals($e->getMessage(), 'The option IncomingUser user password is already set.');
                }
            } else {
                $this->fail("The normalizer did not properly normalize the file!");
            }
        }
    }

    /**
     * Test the addOutgoingUser() helper method
     */
    public function testAddOutgoingUserHelperMethod() {
        $file = 'iet.expected.testAddOutgoingUserHelperMethod.conf';

        foreach ($this->dirs as $dir) {
            $dir = __DIR__ . DIRECTORY_SEPARATOR . $dir;

            $objects = $this->normalize($dir);

            if ($objects['normalizer']->check()) {
                try {
                    $objects['parser']->addOutgoingUser("user", "password")->write();
                } catch (DuplicationErrorException $e) {
                    $this->assertEquals($e->getMessage(), 'The option OutgoingUser user password is already set.');
                }
            } else {
                $this->fail("The normalizer did not properly normalize the file!");
            }

            $this->assertFileEquals($dir . DIRECTORY_SEPARATOR . $file, $dir . DIRECTORY_SEPARATOR . $this->testFile);
        }
    }

    /**
     * Test the addIncomingUser() helper method
     */
    public function testAddIncomingUserHelperMethod() {
        $file = 'iet.expected.testAddIncomingUserHelperMethod.conf';

        foreach ($this->dirs as $dir) {
            $dir = __DIR__ . DIRECTORY_SEPARATOR . $dir;

            $objects = $this->normalize($dir);

            if ($objects['normalizer']->check()) {
                try {
                    $objects['parser']->addIncomingUser("user", "password")->write();
                } catch (DuplicationErrorException $e) {
                    $this->assertEquals($e->getMessage(), 'The option IncomingUser user password is already set.');
                }
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