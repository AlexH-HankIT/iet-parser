<?php

/**
 * This file contains the Parser class
 *
 * PHP version 5.6
 *
 * @category Parser
 * @package  MrCrankHank\IetParser\Parser
 * @author   Alexander Hank <mail@alexander-hank.de>
 * @license  Apache License 2.0 http://www.apache.org/licenses/LICENSE-2.0.txt
 * @link     null
 */

namespace MrCrankHank\IetParser\Parser;

use League\Flysystem\Filesystem;
use Illuminate\Support\Collection;
use MrCrankHank\IetParser\Exceptions\NotFoundException;

/**
 * Class Parser
 *
 * @category Parser
 * @package  MrCrankHank\IetParser\Parser
 * @author   Alexander Hank <mail@alexander-hank.de>
 * @license  Apache License 2.0 http://www.apache.org/licenses/LICENSE-2.0.txt
 * @link     null
 */
class Parser
{
    /**
     * Contains a Filesystem instance
     *
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * Contains the file content
     *
     * @var
     */
    protected $fileContent;

    /**
     * Contains the file path
     *
     * @var
     */
    protected $filePath;

    /**
     * Contains the extracted comments
     * of the file
     *
     * @var
     */
    protected $comments;

    /**
     * Contains the file content
     * without any modifications
     *
     * @var
     */
    protected $originalContent;

    /**
     * Parser constructor.
     *
     * @param Filesystem $filesystem Filesystem instance
     * @param string     $filePath   Path to the file
     */
    public function __construct(Filesystem $filesystem, $filePath)
    {
        $this->filesystem = $filesystem;
        $this->filePath = $filePath;
    }

    /**
     * Retrieves the file's content without any comments or newlines
     *
     * @return Collection
     */
    public function get()
    {
        $fileContent = $this->getRaw();

        $fileContent = $this->_handleComments($fileContent);

        return $fileContent;
    }

    /**
     * Retrieves the file's content exactly as it is
     *
     * @return string
     */
    public function getRaw()
    {
        $fileContent = $this->filesystem->read($this->filePath);

        $this->originalContent = $fileContent;

        return collect(explode("\n", $fileContent));
    }

    /**
     * Merge the file's content with comments
     * and new lines and write it back
     *
     * @return void
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

        if (!empty($new)) {
            // push the new line as first item
            array_unshift($fileContent, $new);
        }

        $fileContent = implode("\n", $fileContent);

        $this->filesystem->update($this->filePath, $fileContent);
    }

    /**
     * Write a raw string as file
     *
     * @param string $string String to be written
     *
     * @return void
     */
    public function writeRaw($string)
    {
        $this->filesystem->update($this->filePath, $string);
    }

    /**
     * Reread the files content
     *
     * @return void
     */
    public function refresh() {
        $this->fileContent = $this->get();
    }

    /**
     * Extract comments from the file
     *
     * @param Collection $fileContent Collection of the file's content
     *
     * @return Collection
     */
    private function _handleComments(Collection $fileContent)
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
     * Find a specific global option
     *
     * @param Collection $fileContent Collection of the file's content
     * @param string     $option      Option to be found
     *
     * @throws NotFoundException
     *
     * @return mixed
     */
    protected function findGlobalOption(Collection $fileContent, $option)
    {
        $id = $this->findFirstTargetDefinition($fileContent);

        // decrement id
        // so we get the last global line
        $id--;

        for ($i = 0; $i <= $id; $i++) {
            if ($fileContent->has($i)) {
                if ($fileContent->get($i) === $option) {
                    return $i;
                }
            }

            // So here we are, last line
            // This means we didn't find the index
            // So let's throw an exception here and go home
            if ($i === $id) {
                return false;
            }
        }

        return false;
    }

    /**
     * Return the id of the first target definition
     *
     * @param Collection $fileContent Collection of the file's content
     *
     * @return mixed
     */
    protected function findFirstTargetDefinition(Collection $fileContent)
    {
        $firstTarget = $fileContent->first(function ($key, $value) {
            if (substr($value, 0, 6) === 'Target') {
                return true;
            }
        });

        return $fileContent->search($firstTarget, true);
    }
}