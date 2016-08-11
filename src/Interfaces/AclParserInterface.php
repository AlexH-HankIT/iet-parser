<?php

namespace MrCrankHank\IetParser\Interfaces;

use League\Flysystem\FilesystemInterface;

interface AclParserInterface
{
    public function __construct(FilesystemInterface $filesystem, $filePath, $target = null);

    public function add($add);

    public function delete($delete);

    public function get($all = false);
}