<?php

namespace MrCrankHank\IetParser\Interfaces;

interface ParserInterface
{
    public function __construct(FileInterface $file, $target = null);

    public function read();

    public function readRaw();

    public function write();

    public function writeRaw($string);

    public function refresh();
}
