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
     */
    public function __construct(Filesystem $filesystem, $filePath)
    {
        $this->filesystem = $filesystem;
        $this->filePath = $filePath;
    }

    /**
     * Retrieves the file's content without any comments or newlines
     */
    public function get()
    {
        $fileContent = $this->getRaw();

        $fileContent = $this->handleComments($fileContent);

        return $fileContent;
    }

    public function getGlobal()
    {
        // extract global section from file content
        // return the global section
    }

    /**
     * Retrieves the file's content exactly as it is
     */
    public function getRaw()
    {
        $fileContent = $this->filesystem->read($this->filePath);

        $this->originalContent = $fileContent;

        return collect(explode("\n", $fileContent));
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
        // convert collections to arrays
        $fileContent = $this->fileContent->all();
        $comments = $this->comments->all();

        if (isset($fileContent['new'])) {
            // save new line to variable and delete it from the array
            // so ksort can sort the indexes numerically
            $new = $fileContent['new'];
            unset($fileContent['new']);
        }

        // merge config with comments
        $fileContent = $fileContent + $comments;

        // sort the array, so the lines are correct
        ksort($fileContent);

        if (isset($fileContent['new'])) {
            // push the new line as first item
            array_unshift($fileContent, $new);
        }

        $fileContent = implode("\n", $fileContent);

        $this->filesystem->update($this->filePath, $fileContent);
    }

    /**
     * Write a raw string as file
     *
     * @param $string
     */
    public function writeRaw($string)
    {
        $this->filesystem->update($this->filePath, $string);
    }

    /**
     * @param Collection $fileContent
     * @return Collection
     */
    private function handleComments(Collection $fileContent)
    {
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
        return $fileContent;
    }

    /**
     *
     */
    protected function findTarget()
    {

    }

    /**
     *
     */
    protected function getTargetOptionCount()
    {

    }

    /**
     * @param Collection $fileContent
     * @param            $option
     * @return mixed
     */
    protected function findGlobalOption(Collection $fileContent, $option)
    {
        return $fileContent->search($option);
    }

    /**
     *
     */
    protected function getGlobalOptionCount()
    {

    }

    /**
     * Return the id of the first target definition
     *
     * @param Collection $fileContent
     * @return mixed
     */
    protected function findFirstTargetDefinition(Collection $fileContent)
    {
        $firstTarget = $fileContent->first(function($key, $value) {
            if (substr($value, 0, 6) === 'Target') {
                return true;
            }
        });

        return $fileContent->search($firstTarget, true);
    }
}
