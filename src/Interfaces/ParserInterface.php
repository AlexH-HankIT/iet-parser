<?php

namespace MrCrankHank\IetParser\Interfaces;

use League\Flysystem\FilesystemInterface;

interface ParserInterface
{
    public function __construct(FilesystemInterface $filesystem, $filePath, $target = null);

    public function read();

    public function readRaw();

    public function write();

    public function writeRaw($string);
}