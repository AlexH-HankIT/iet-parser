<?php

/**
 * This file contains the File class.
 *
 * PHP version 5.6
 *
 * @category Parser
 *
 * @author   Alexander Hank <mail@alexander-hank.de>
 * @license  Apache License 2.0 http://www.apache.org/licenses/LICENSE-2.0.txt
 *
 * @link     null
 */
namespace MrCrankHank\IetParser;

use League\Flysystem\FilesystemInterface;
use MrCrankHank\IetParser\Interfaces\FileInterface;

/**
 * Class File.
 *
 * @category Parser
 *
 * @author   Alexander Hank <mail@alexander-hank.de>
 * @license  Apache License 2.0 http://www.apache.org/licenses/LICENSE-2.0.txt
 *
 * @link     null
 */
class File implements FileInterface
{
    /**
     * @var string|null
     */
    private $fileContent;

    /**
     * @var FilesystemInterface|null
     */
    private $filesystem;

    /**
     * @var string|null
     */
    private $filePath;

    /**
     * Read the file content from a filesystem instance.
     *
     * @param FilesystemInterface $filesystem
     * @param                     $filePath
     *
     * @return $this
     */
    public function readContent(FilesystemInterface $filesystem, $filePath)
    {
        $this->filesystem = $filesystem;

        $this->filePath = $filePath;

        $this->fileContent = $this->filesystem->read($this->filePath);

        return $this;
    }

    /**
     * Refresh the files content, if a filesystem instance is used.
     *
     * @return $this
     */
    public function refresh()
    {
        if ($this->filesystem) {
            $this->fileContent = $this->filesystem->read($this->filePath);
        }

        return $this;
    }

    /**
     * Set the file content directly.
     *
     * @param $fileContent
     *
     * @return $this
     */
    public function setContent($fileContent)
    {
        $this->fileContent = $fileContent;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getContent()
    {
        return $this->fileContent;
    }

    /**
     * @return FilesystemInterface|null
     */
    public function getFilesystem()
    {
        return $this->filesystem;
    }

    /**
     * @return null|string
     */
    public function getFilePath()
    {
        return $this->filePath;
    }
}
