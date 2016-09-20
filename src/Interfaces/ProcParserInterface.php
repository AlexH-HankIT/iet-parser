<?php

namespace MrCrankHank\IetParser\Interfaces;

interface ProcParserInterface
{
    public function __construct(FileInterface $file, $target = null);

    public function getSession();

    public function getVolume();

    public function setTidIndex($tidIndex);

    public function getTidIndex();

    public function volumeExists();

    public function sessionExists($sid);
}