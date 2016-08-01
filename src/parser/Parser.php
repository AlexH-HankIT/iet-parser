<?php

namespace MrCrankHank\IetParser\Parser;

use League\Flysystem\Filesystem;
use Illuminate\Support\Collection;
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
    protected $fileContent;

    /**
     * @var bool
     */
    protected $test;

    /**
     * @var
     */
    protected $filePath;

    /**
     * @var
     */
    protected $comments;

    /**
     * @var
     */
    protected $originalContent;

    /**
     * Parser constructor.
     * @param Filesystem $filesystem
     * @param            $filePath
     * @param bool       $test
     */
    public function __construct(Filesystem $filesystem, $filePath, $test = false)
    {
        $this->filesystem = $filesystem;
        $this->filePath = $filePath;
        $this->test = $test;
    }

    /**
     * Retrieves the file's content without any comments or newlines
     */
    protected function get()
    {
        $fileContent = $this->filesystem->read($this->filePath);

        $this->originalContent = $fileContent;

        $fileContent = collect(explode("\n", $fileContent));

        $fileContent =  $this->handleComments($fileContent);

        return $fileContent;
    }

    /**
     * Retrieves the file's content exactly as it is
     */
    public function getRaw()
    {
        return collect(explode("\n", $this->filesystem->read($this->filePath)));
    }

    /**
     * Retrieves the file's global section without any comennts or newlines
     */
    public function getGlobalSection()
    {
        // go through every line and replace multiple spaces with one space
        // replace multiple newlines with one newline
    }

    /**
     * Merge the file's content with comments
     * and new lines and write it back
     *
     */
    public function write()
    {
        $this->fileContent = $this->fileContent->flip();

        // convert collections to arrays
        $fileContent = $this->fileContent->all();
        $comments = $this->comments->all();

        // save new line to variable and delete it from the array
        // so ksort can sort the indexes numerically
        $new = $fileContent['new'];
        unset($fileContent['new']);

        // merge config with comments
        $fileContent = $fileContent + $comments;

        // sort the array, so the lines are correct
        ksort($fileContent);

        // push the new line as first item
        array_unshift($fileContent, $new);

        $fileContent = implode("\n", $fileContent);

        if ($this->test) {
            dd($this->originalContent, $fileContent);
        } else {
            // ToDo: xdiff_string_diff($this->originalContent. $fileContent)
            $this->filesystem->update($this->filePath, $fileContent);
        }
    }

    /**
     * @param Collection $fileContent
     * @return static
     */
    private function handleComments(Collection $fileContent) {
        $fileContent = $fileContent->filter(function ($line, $key) {
            if (empty($line)) {
                // save empty lines in comments array
                $this->comments[$key] = $line;
                return false;
            } else {
                // check for comments
                $offset = stripos(preg_replace('/\s+/', '', $line), '#');
                if ($offset !== false) {
                    // extract the whole line if it's commented
                    $this->comments[$key] = $line;
                    return false;
                } else {
                    return true;
                }
            }
        });

        $this->comments = collect($this->comments);

        // Flip collection to preserve the indexes
        return $fileContent->flip();
    }

    /**
     *
     */
    protected function findTarget() {

    }

    /**
     *
     */
    protected function getTargetOptionCount() {

    }

    /**
     * @param Collection $fileContent
     * @param            $option
     * @return mixed
     */
    protected function findGlobalOption(Collection $fileContent, $option) {
        return $fileContent->search($option);
    }

    /**
     *
     */
    protected function getGlobalOptionCount() {

    }
}
