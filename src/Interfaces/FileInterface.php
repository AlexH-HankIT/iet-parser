<?php

namespace MrCrankHank\IetParser\Interfaces;

use League\Flysystem\FilesystemInterface;

interface FileInterface
{
    public function readContent(FilesystemInterface $filesystem, $filePath);

    public function refresh();

    public function setContent($fileContent);

    public function getContent();

    public function getFilesystem();

    public function getFilePath();
}
