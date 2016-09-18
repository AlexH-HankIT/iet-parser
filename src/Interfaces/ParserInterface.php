<?php

namespace MrCrankHank\IetParser\Interfaces;

use Illuminate\Support\Collection;
use League\Flysystem\FilesystemInterface;

interface ParserInterface
{
    public function __construct($target = null);

    public function read();

    public function readRaw();

    public function write();

    public function writeRaw($string);

    public function setFileContent(Collection $fileContent);

    public function readFileContent(FilesystemInterface $filesystem, $filePath);
}