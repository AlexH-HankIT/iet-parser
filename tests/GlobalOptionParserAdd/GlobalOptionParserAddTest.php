<?php

namespace MrCrankHank\IetParser\tests;

use MrCrankHank\IetParser\Exceptions\DuplicationErrorException;
use MrCrankHank\IetParser\Parser\GlobalOptionParser;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
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
        $local = new Local(__DIR__ . DIRECTORY_SEPARATOR . 'files', LOCK_EX);

        $filesystem = new Filesystem($local);

        $filesystem->copy('iet.sample.conf', 'iet.test-running.conf');

        $parser = new GlobalOptionParser($filesystem, 'iet.test-running.conf');

        $parser->add("test")->write();

        $contentAfterWrite = $filesystem->read('iet.test-running.conf');
        $expectedContent = $filesystem->read('iet.expected.conf');

        $filesystem->delete('iet.test-running.conf');

        $this->assertEquals(preg_split('/\r\n|\r|\n/', $expectedContent), preg_split('/\r\n|\r|\n/', $contentAfterWrite));
    }

    /**
     * Test if the duplication check is working
     */
    public function testDuplicationError() {
        $local = new Local(__DIR__ . DIRECTORY_SEPARATOR . 'files', LOCK_EX);

        $filesystem = new Filesystem($local);

        $filesystem->copy('iet.sample.conf', 'iet.test-running.conf');

        $parser = new GlobalOptionParser($filesystem, 'iet.test-running.conf');

        try {
            $parser->add("test")->write();
            $parser->add("test")->write();
        } catch (DuplicationErrorException $e) {
            $this->assertEquals($e->getMessage(), 'The option test is already set.');
            $filesystem->delete('iet.test-running.conf');
        }
    }

    /**
     * Test the addOutgoingUser() helper method
     */
    public function testAddOutgoingUserHelperMethod() {
        $local = new Local(__DIR__ . DIRECTORY_SEPARATOR . 'files', LOCK_EX);

        $filesystem = new Filesystem($local);

        $filesystem->copy('iet.sample.conf', 'iet.test-running.conf');

        $parser = new GlobalOptionParser($filesystem, 'iet.test-running.conf');

        $parser->addOutgoingUser("User", "password")->write();

        $contentAfterWrite = $filesystem->read('iet.test-running.conf');
        $expectedContent = $filesystem->read('iet.expected2.conf');

        $filesystem->delete('iet.test-running.conf');

        $this->assertEquals(preg_split('/\r\n|\r|\n/', $expectedContent), preg_split('/\r\n|\r|\n/', $contentAfterWrite));
    }

    /**
     * Test the addIncomingUser() helper method
     */
    public function testAddIncomingUserHelperMethod() {
        $local = new Local(__DIR__ . DIRECTORY_SEPARATOR . 'files', LOCK_EX);

        $filesystem = new Filesystem($local);

        $filesystem->copy('iet.sample.conf', 'iet.test-running.conf');

        $parser = new GlobalOptionParser($filesystem, 'iet.test-running.conf');

        $parser->addIncomingUser("User", "password")->write();

        $contentAfterWrite = $filesystem->read('iet.test-running.conf');
        $expectedContent = $filesystem->read('iet.expected3.conf');

        $filesystem->delete('iet.test-running.conf');

        $this->assertEquals(preg_split('/\r\n|\r|\n/', $expectedContent), preg_split('/\r\n|\r|\n/', $contentAfterWrite));
    }
}