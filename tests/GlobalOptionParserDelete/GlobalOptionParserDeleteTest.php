<?php

namespace MrCrankHank\IetParser\tests;

use PHPUnit_Framework_TestCase;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use MrCrankHank\IetParser\Parser\GlobalOptionParser;
use MrCrankHank\IetParser\Parser\Normalizer;

class GlobalOptionParserDelete extends PHPUnit_Framework_TestCase {
    public function testDelete() {
        $dirs = ['case1_files'];

        foreach($dirs as $dir) {
            // Create new filesystem adapter
            $local = new Local(__DIR__ . DIRECTORY_SEPARATOR . $dir, LOCK_EX);

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
                $parser->delete('IncomingUser user2 password2')->write();
            } else {
                $this->fail("The normalizer did not properly normalize the file!");
            }

            $contentAfterWrite = $filesystem->read('iet.test-running.conf');
            $expectedContent = $filesystem->read('iet.expected.testDelete.conf');

            $filesystem->delete('iet.test-running.conf');

            $this->assertEquals($contentAfterWrite, $expectedContent);
        }
    }

    public function testNotFoundError() {
        $this->expectException('MrCrankHank\IetParser\Exceptions\NotFoundException');

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
            $parser->delete('This wont be found')->write();
        } else {
            $this->fail("The normalizer did not properly normalize the file!");
        }

        $filesystem->delete('iet.test-running.conf');
    }

    public function testDeleteIncomingUserHelperMethod() {
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
            $parser->deleteIncomingUser('user2',  'password2')->write();
        } else {
            $this->fail("The normalizer did not properly normalize the file!");
        }

        $contentAfterWrite = $filesystem->read('iet.test-running.conf');
        $expectedContent = $filesystem->read('iet.expected.testDeleteIncomingUserHelperMethod.conf');

        $filesystem->delete('iet.test-running.conf');

        $this->assertEquals($contentAfterWrite, $expectedContent);
    }

    public function testDeleteOutgoingUserHelperMethod() {
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
            $parser->deleteOutgoingUser('user2',  'password2')->write();
        } else {
            $this->fail("The normalizer did not properly normalize the file!");
        }

        $contentAfterWrite = $filesystem->read('iet.test-running.conf');
        $expectedContent = $filesystem->read('iet.expected.testDeleteOutgoingUserHelperMethod.conf');

        $filesystem->delete('iet.test-running.conf');

        $this->assertEquals($contentAfterWrite, $expectedContent);
    }

    public function tearDown() {
        if (file_exists(__DIR__ . '/case1_files/iet.test-running.conf')) unlink(__DIR__ . '/case1_files/iet.test-running.conf');
        if (file_exists(__DIR__ . '/case2_files/iet.test-running.conf')) unlink(__DIR__ . '/case2_files/iet.test-running.conf');
    }
}