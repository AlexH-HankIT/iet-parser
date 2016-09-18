<?php

namespace MrCrankHank\IetParser\Interfaces;

use Illuminate\Support\Collection;

interface AclParserInterface
{
    public function __construct($target = null);

    public function add($add);

    public function delete($delete);

    public function get($all = false);

    public function setFileContent(Collection $fileContent);
}