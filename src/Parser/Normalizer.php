<?php

/**
 * This file contains the Normalizer
 * of a iet config file via console
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
use MrCrankHank\IetParser\Interfaces\NormalizerInterface;
use MrCrankHank\IetParser\Interfaces\ParserInterface;

/**
 * Class Normalizer
 *
 * @category Parser
 * @package  MrCrankHank\IetParser\Parser
 * @author   Alexander Hank <mail@alexander-hank.de>
 * @license  Apache License 2.0 http://www.apache.org/licenses/LICENSE-2.0.txt
 * @link     null
 */
class Normalizer extends Parser implements ParserInterface, NormalizerInterface
{
    /**
     * Normalize a ietd file
     *
     * It checks and normalizes the following:
     *      multiple spaces are replaced with one
     *      spaces at the start or end are deleted
     *      newlines are deleted
     *      inline comments are removed
     *      merge multi line definitions in one line
     *
     * @return array
     */
    protected function normalize()
    {
        $originalFileContent = $this->readRaw();

        // remove spaces and the ending/beginning
        $fileContent = $originalFileContent->map(function ($line, $key) {
            return trim($line);
        });

        // create string from array
        $fileContentString = implode("\n", $fileContent->all());

        // replace multiple newlines with a single one
        $fileContentString = preg_replace('/[\r\n|\n]+/', "\n", $fileContentString);

        // replace multiple spaces with a single one
        $fileContentString = preg_replace('/\s\s+/', ' ', $fileContentString);

        // merge escaped lines into one
        $fileContentString = str_replace("\\\n", '', $fileContentString);

        // create collection
        $fileContent = collect(explode("\n", $fileContentString));

        // check for inline comments
        $fileContent = $fileContent->map(function ($line) {
            $position = strpos($line, '#');

            if ($position !== 0 && $position !== false) {
                return trim(substr_replace($line, '', $position), ' ');
            }

            return $line;
        });

        return ['fileContentString' => implode("\n", $fileContent->all()), 'originalFileContentString' => implode("\n", $originalFileContent->all())];
    }

    /**
     * Return a diff of the normalization without writing anything
     *
     * @return string
     */
    public function diff()
    {
        $data = $this->normalize();
        return Diff::toString(Diff::compare($data['originalFileContentString'], $data['fileContentString']));
    }

    /**
     * Write the normalized data to the file
     *
     * This violates the Liskov Substitution principle :S
     *
     * @return void
     */
    public function write()
    {
        $this->writeRaw($this->normalize()['fileContentString']);
    }

    /**
     * Verify if a file is already normalized
     *
     * @return boolean
     */
    public function check()
    {
        $data = $this->normalize();

        if (strcmp($data['originalFileContentString'], $data['fileContentString']) !== 0) {
            return false;
        }

        return true;
    }
}