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
    /**
     * Test if I can add a parameter to the file
     */
    public function testAdd() {
        // Create new filesystem adapter
        $local = new Local(__DIR__ . DIRECTORY_SEPARATOR . 'case1_files', LOCK_EX);

        // create new filesystem
        $filesystem = new Filesystem($local);

        // for testing purposes: copy the sample file. So we don't change the real data
        $filesystem->copy('iet.sample.conf', 'iet.test-running.conf');

        // create parser instance
        $parser = new GlobalOptionParser($filesystem, 'iet.test-running.conf');

        // create normalizer instance
        $normalizer = new Normalizer($parser);

        // normalize the file
        $normalizer->write();

        if ($normalizer->check()) {
            try {
                $parser->add("IncomingUser user password")->write();
            } catch (DuplicationErrorException $e) {
                $filesystem->delete('iet.test-running.conf');
                $this->assertEquals($e->getMessage(), 'The option IncomingUser user password is already set.');
            }
        } else {
            $this->fail("The normalizer did not properly normalize the file!");
        }

        $contentAfterWrite = $filesystem->read('iet.test-running.conf');
        $expectedContent = $filesystem->read('iet.expected.testAdd.conf');

        $filesystem->delete('iet.test-running.conf');

        //$this->assertEquals(preg_split('/\r\n|\r|\n/', $expectedContent), preg_split('/\r\n|\r|\n/', $contentAfterWrite));
        $this->assertEquals($contentAfterWrite, $expectedContent);
    }

    /**
     * Test if the duplication check is working
     */
    public function testDuplicationError() {
        // Create new filesystem adapter
        $local = new Local(__DIR__ . DIRECTORY_SEPARATOR . 'case1_files', LOCK_EX);

        // create new filesystem
        $filesystem = new Filesystem($local);

        // for testing purposes: copy the sample file. So we don't change the real data
        $filesystem->copy('iet.sample.conf', 'iet.test-running.conf');

        // create parser instance
        $parser = new GlobalOptionParser($filesystem, 'iet.test-running.conf');

        // create normalizer instance
        $normalizer = new Normalizer($parser);

        // normalize the file
        $normalizer->write();

        if ($normalizer->check()) {
            try {
                $parser->add("IncomingUser user password")->write();
                $parser->add("IncomingUser user password")->write();
                $this->fail("Test did not throw DuplicationError exception!");
            } catch (DuplicationErrorException $e) {
                $this->assertEquals($e->getMessage(), 'The option IncomingUser user password is already set.');
            } finally {
                $filesystem->delete('iet.test-running.conf');
            }
        } else {
            $this->fail("The normalizer did not properly normalize the file!");
        }
    }

    /**
     * Test the addOutgoingUser() helper method
     */
    public function testAddOutgoingUserHelperMethod() {
        // Create new filesystem adapter
        $local = new Local(__DIR__ . DIRECTORY_SEPARATOR . 'case1_files', LOCK_EX);

        // create new filesystem
        $filesystem = new Filesystem($local);

        // for testing purposes: copy the sample file. So we don't change the real data
        $filesystem->copy('iet.sample.conf', 'iet.test-running.conf');

        // create normalizer instance
        $parser = new GlobalOptionParser($filesystem, 'iet.test-running.conf');

        // create normalizer instance
        $normalizer = new Normalizer($parser);

        // normalize the file
        $normalizer->write();

        if ($normalizer->check()) {
            try {
                $parser->addOutgoingUser("user", "password")->write();
            } catch (DuplicationErrorException $e) {
                $this->assertEquals($e->getMessage(), 'The option OutgoingUser user password is already set.');
            }
        } else {
            $this->fail("The normalizer did not properly normalize the file!");
        }

        $contentAfterWrite = $filesystem->read('iet.test-running.conf');
        $expectedContent = $filesystem->read('iet.expected.testAddOutgoingUserHelperMethod.conf');

        $filesystem->delete('iet.test-running.conf');

        $this->assertEquals(preg_split('/\r\n|\r|\n/', $expectedContent), preg_split('/\r\n|\r|\n/', $contentAfterWrite));
    }

    /**
     * Test the addIncomingUser() helper method
     */
    public function testAddIncomingUserHelperMethod() {
        // Create new filesystem adapter
        $local = new Local(__DIR__ . DIRECTORY_SEPARATOR . 'case1_files', LOCK_EX);

        // create new filesystem
        $filesystem = new Filesystem($local);

        // for testing purposes: copy the sample file. So we don't change the real data
        $filesystem->copy('iet.sample.conf', 'iet.test-running.conf');

        // create normalizer instance
        $parser = new GlobalOptionParser($filesystem, 'iet.test-running.conf');

        // create normalizer instance
        $normalizer = new Normalizer($parser);

        // normalize the file
        $normalizer->write();

        if ($normalizer->check()) {
            try {
                $parser->addIncomingUser("user", "password")->write();
            } catch (DuplicationErrorException $e) {
                $this->assertEquals($e->getMessage(), 'The option IncomingUser user password is already set.');
            }
        } else {
            $this->fail("The normalizer did not properly normalize the file!");
        }

        $contentAfterWrite = $filesystem->read('iet.test-running.conf');
        $expectedContent = $filesystem->read('iet.expected.testAddIncomingUserHelperMethod.conf');

        $filesystem->delete('iet.test-running.conf');

        $this->assertEquals(preg_split('/\r\n|\r|\n/', $expectedContent), preg_split('/\r\n|\r|\n/', $contentAfterWrite));
    }
}