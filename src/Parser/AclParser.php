<?php

/**
 * This file contains the AclParser class
 *
 * PHP version 5.6
 *
 * @category Parser
 * @package  MrCrankHank\IetParser\Parser
 * @author   Alexander Hank <mail@alexander-hank.de>
 * @license  Apache License 2.0 http://www.apache.org/licenses/LICENSE-2.0.txt
 * @link     null
 */

namespace MrCrankHank\IetParser\Parser;

use League\Flysystem\Filesystem;

/**
 * Class AclParser
 *
 * Add/delete targets to/from the iet config file
 * Add/delete options to/from a target
 * Get a target with options
 *
 * @category Parser
 * @package  MrCrankHank\IetParser\Parser
 * @author   Alexander Hank <mail@alexander-hank.de>
 * @license  Apache License 2.0 http://www.apache.org/licenses/LICENSE-2.0.txt
 * @link     null
 */
class AclParser extends Parser
{
    protected $target;

    public function __construct(Filesystem $filesystem, $filePath, $target) {
        parent::__construct($filesystem, $filePath);

        $this->target = $target;
    }

    public function add() {

    }

    public function delete() {

    }

    public function get() {

    }
}