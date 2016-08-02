<?php

namespace MrCrankHank\IetParser\Parser;

use MrCrankHank\IetParser\Parser\Parser;

/**
 * Class Normalizer
 * @package MrCrankHank\IetParser\Parser
 */
class Normalizer
{
    /**
     * @var Parser
     */
    protected $parser;

    /**
     * Normalize constructor.
     * @param Parser $parser
     */
    public function __construct(Parser $parser)
    {
        $this->parser = $parser;
    }

    /**
     * Normalize a ietd file
     *
     * It checks and normalizes the following:
     *      multiple spaces are replaced with one
     *      spaces at the start or end are deleted
     *      newlines are deleted
     *      inline comments are removed
     *      merge multi line definitions in one line
     */
    protected function normalize()
    {
        $originalFileContent = $this->parser->getRaw();

        // remove spaces and the ending/beginning
        $fileContent = $originalFileContent->map(function($line, $key) {
            return trim($line, ' ');
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
        $fileContent = $fileContent->map(function($line) {
            $position = strpos($line, '#');

            if ($position !== 0 && $position !== false) {
                return trim(substr_replace($line, '', $position), ' ');
            } else {
                return $line;
            }
        });

        return [
            'fileContentString' => implode("\n", $fileContent->all()),
            'originalFileContentString' => implode("\n", $originalFileContent->all())
        ];
    }

    /**
     * Return a diff of the normalization without writing anything
     */
    public function diff()
    {
        $data = $this->normalize();
        return Diff::toString(Diff::compare($data['originalFileContentString'], $data['fileContentString']));
    }

    /**
     * Write the normalized data to the file
     */
    public function write()
    {
        $this->parser->writeRaw($this->normalize()['fileContentString']);
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
        } else {
            return true;
        }
    }
}