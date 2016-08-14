<?php

namespace MrCrankHank\IetParser\Interfaces;

interface NormalizerInterface
{
    public function diff();

    public function write();

    public function check();
}
