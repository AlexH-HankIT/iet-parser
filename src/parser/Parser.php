<?php

namespace mrcrankhank\ietParser\parser;

use League\Flysystem\Filesystem;

/**
 * Class Parser
 * @package mrcrankhank\ietParser\parser
 */
class Parser
{
    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var
     */
    protected $file;

    /**
     * Parser constructor.
     * @param Filesystem $filesystem
     * @param            $fileName
     * @param bool       $test
     */
    public function __construct(Filesystem $filesystem, $fileName, $test = false)
    {
        $this->filesystem = $filesystem;
        $this->file = $filesystem->get($fileName);
    }

    /**
     * Retrieves the file's content without any comments or newlines
     */
    private function get() {

        $this->file = '';
    }

    /**
     * Merge the file's content with comments
     * and new lines and write it back
     *
     * @param string $file
     */
    public function write($file) {

    }
}
