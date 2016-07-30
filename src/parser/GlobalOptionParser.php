<?php

namespace mrcrankhank\ietParser\parser;

/**
 * Class GlobalOption
 *
 * Add a global option to a iet config file.
 * Global options are similar to target specific options.
 * But they are defined before any target definition
 *
 * @package mrcrankhank\ietParser\parser
 */
class GlobalOptionParser extends Parser {
    private $file;

    /**
     * @param $option
     * @return $this
     */
    public function add($option) {

        return $this;
    }

    /**
     * @param $option
     * @return $this
     */
    public function remove($option) {
        return $this;
    }
}