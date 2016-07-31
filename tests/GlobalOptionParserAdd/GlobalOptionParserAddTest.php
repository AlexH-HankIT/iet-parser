<?php

namespace MrCrankHank\IetParser\tests;

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
     * @var
     */
    protected $expectedContent;

    /**
     * @var
     */
    protected $contentAfterWrite;

    /**
     * Do some setup and then write the test data
     */
    protected function write() {
        $local = new Local(__DIR__ . DIRECTORY_SEPARATOR . 'files', LOCK_EX);

        $filesystem = new Filesystem($local);

        $filesystem->copy('iet.sample.conf', 'iet.test-running.conf');

        $parser = new GlobalOptionParser($filesystem, 'iet.test-running.conf');

        $parser->add("test")->write();

        $this->contentAfterWrite = $filesystem->read('iet.test-running.conf');
        $this->expectedContent = $filesystem->read('iet.expected.conf');

        $filesystem->delete('iet.test-running.conf');
    }

    /**
     * GlobalOptionParser test
     */
    public function testAdd() {
        $this->write();

        $this->assertEquals(preg_split('/\r\n|\r|\n/', $this->expectedContent), preg_split('/\r\n|\r|\n/', $this->contentAfterWrite));
    }
}