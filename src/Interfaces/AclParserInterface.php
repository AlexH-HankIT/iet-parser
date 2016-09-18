<?php

namespace MrCrankHank\IetParser\Interfaces;

use Illuminate\Support\Collection;

interface AclParserInterface
{
    public function __construct(FileInterface $file, $target = null);

    public function add($add);

    public function delete($delete);

    public function get($all = false);
}