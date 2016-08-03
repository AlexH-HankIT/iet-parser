<?php

/**
 * This file contains the GlobalOptionParser class
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

use MrCrankHank\IetParser\Exceptions\DuplicationErrorException;
use MrCrankHank\IetParser\Exceptions\NotFoundException;

/**
 * Class GlobalOption
 *
 * Add/delete global options to/from a iet config file.
 * Global options are similar to target specific options.
 * But they are defined before any target definition
 *
 * @category Parser
 * @package  MrCrankHank\IetParser\Parser
 * @author   Alexander Hank <mail@alexander-hank.de>
 * @license  Apache License 2.0 http://www.apache.org/licenses/LICENSE-2.0.txt
 * @link     null
 */
class GlobalOptionParser extends Parser
{
    /**
     * Add a global line
     *
     * @param string $option Add this to the file
     *
     * @throws DuplicationErrorException
     * @return $this
     */
    public function add($option)
    {
        $fileContent = $this->get();

        $id = $this->findGlobalOption($fileContent, $option);

        // Check if the option is already defined
        if ($id === false) {
            $fileContent->prepend($option, 'new');
        } else {
            throw new DuplicationErrorException('The option ' . $option . ' is already set.');
        }

        $this->fileContent = $fileContent;

        return $this;
    }

    /**
     * Remove a global line
     *
     * Don't remove target definitions using this function,
     * because it does not take care of target options
     *
     * @param string $option Delete this from the file
     *
     * @throws NotFoundException
     * @return $this
     */
    public function delete($option)
    {
        $fileContent = $this->get();

        $id = $this->findFirstTargetDefinition($fileContent);

        // decrement id
        // so we get the last global line
        $id--;

        for ($i = 0; $i <= $id; $i++) {
            if ($fileContent->has($i)) {
                if ($fileContent->get($i) === $option) {
                    $fileContent->forget($i);
                    break;
                }
            }

            // So here we are, last line
            // This means we didn't find the index
            // So let's throw an exception here and go home
            if ($i === $id) {
                throw new NotFoundException('The option ' . $option . ' was not found');
            }
        }

        $this->fileContent = $fileContent;

        return $this;
    }

    /**
     * Convenience wrapper for adding a incoming user
     *
     * @param string $username Username
     * @param string $password Password
     *
     * @return $this
     */
    public function addIncomingUser($username, $password)
    {
        return $this->add('IncomingUser ' . $username . ' ' . $password);
    }

    /**
     * Convenience wrapper for adding a outgoing user
     *
     * @param string $username Username
     * @param string $password Password
     *
     * @return $this
     */
    public function addOutgoingUser($username, $password)
    {
        return $this->add('OutgoingUser ' . $username . ' ' . $password);
    }

    /**
     * Convenience wrapper for deleting a incoming user
     *
     * @param string $username Username
     * @param string $password Password
     *
     * @return GlobalOptionParser
     */
    public function deleteIncomingUser($username, $password)
    {
        return $this->delete('IncomingUser ' . $username . ' ' . $password);
    }

    /**
     * Convenience wrapper for deleting a outgoing user
     *
     * @param string $username Username
     * @param string $password Password
     *
     * @return GlobalOptionParser
     */
    public function deleteOutgoingUser($username, $password)
    {
        return $this->delete('OutgoingUser ' . $username . ' ' . $password);
    }
}