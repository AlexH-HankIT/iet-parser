<?php

namespace MrCrankHank\IetParser\Interfaces;

use Illuminate\Support\Collection;

interface ProcParserInterface
{
    public function __construct($target = null);

    public function getSession();

    public function getVolume();

    public function setTidIndex($tidIndex);

    public function getTidIndex();

    public function setFileContent(Collection $fileContent);

    public function volumeExists();

    public function sessionExists($sid);
}