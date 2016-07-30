<?php

namespace MrCrankHank\IetParser\Parser;

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
    /**
     * @param $option
     * @return $this
     */
    public function add($option) {
        $fileContent = $this->get();

        // Check if the option is already defined
        $id = $this->findGlobalOption($fileContent, $option);

        if ($id === false) {
            $fileContent->prepend('new', $option);
        } else {
            // ToDo: Throw a DuplicationErrorException here
        }

        $this->fileContent = $fileContent;

        return $this;
    }

    /**
     * @param $option
     * @return $this
     */
    public function remove($option) {
        return $this;
    }


    /**
     * Convenience wrapper for adding a incoming user
     *
     * @param $username
     * @param $password
     * @return $this
     */
    public function addIncomingUser($username, $password) {
        return $this->add('IncomingUser ' . $username . ' ' . $password);
    }

    /**
     * Convenience wrapper for adding a outgoing user
     *
     * @param $username
     * @param $password
     * @return $this
     */
    public function addOutgoingUser($username, $password) {
        return $this->add('OutgoingUser ' . $username . ' ' . $password);
    }

    public function removeIncomingUser() {

    }

    public function removeOutgoingUser() {

    }

    /**
     * Validate the global option according to the iet man page
     */
    protected function validate() {

    }
}