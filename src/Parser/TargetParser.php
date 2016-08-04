<?php

/**
 * This file contains the TargetParser class
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
use MrCrankHank\IetParser\Exceptions\TargetNotEmptyException;
use Illuminate\Support\Collection;

/**
 * Class TargetParser
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
class TargetParser extends Parser
{
    /**
     * Add a target
     *
     * @param string $target Target
     *
     * @return $this
     *
     * @throws DuplicationErrorException
     */
    public function addTarget($target)
    {
        $fileContent = $this->get();

        $id = $this->findTargetDefinition($fileContent, $target);

        if ($id === false) {
            $fileContent->push('Target ' . $target, 'new');
        } else {
            throw new DuplicationErrorException('The target ' . $target . ' already exists');
        }

        $this->fileContent = $fileContent;

        return $this;
    }

    /**
     * Delete a target
     *
     * @param string $target Target
     *
     * @return $this
     *
     * @throws NotFoundException
     * @throws TargetNotEmptyException
     */
    public function deleteTarget($target)
    {
        $fileContent = $this->get();

        $id = $this->findTargetDefinition($fileContent, $target);

        $options = $this->getOptions($target);

        if ($options === false) {
            if ($id === false) {
                throw new NotFoundException('The target ' . $target . ' was not found');
            } else {
                $fileContent->forget($id);
            }

            $this->fileContent = $fileContent;

            return $this;
        } else {
            throw new TargetNotEmptyException('The target ' . $target . ' has options defined');
        }
    }

    /**
     * Add a option to a target
     *
     * @param string $target Target
     * @param string $option Option
     *
     * @return $this
     *
     * @throws NotFoundException
     */
    public function addOption($target, $option)
    {
        // ToDo: Check for duplicated options (without values)

        $fileContent = $this->get();

        $id = $this->findTargetDefinition($fileContent, $target);

        if ($id === false) {
            throw new NotFoundException('The target ' . $target . ' was not found');
        } else {
            $target = $fileContent->get($id);
            $fileContent->put($id, $target . "\n" . $option);
        }

        $this->fileContent = $fileContent;

        return $this;
    }

    /**
     * Delete a option
     * This should not be used to delete a lun
     *
     * @param string  $target     Target
     * @param string  $option     Option
     * @param boolean $valueMatch Only delete option if the value also matches
     *
     * @return $this
     *
     * @throws NotFoundException
     */
    public function deleteOption($target, $option, $valueMatch = true)
    {
        $fileContent = $this->get();

        $id = $this->findTargetDefinition($fileContent, $target);

        if ($id === false) {
            throw new NotFoundException('The target ' . $target . ' was not found');
        } else {
            $options = $this->getOptions($target);

            if ($options === false) {
                throw new NotFoundException('The target ' . $target . ' has no options');
            } else {
                if ($valueMatch) {
                    $id = $fileContent->search($option);
                } else {
                    // ToDo: Search partial here, to delete a option with unknown value
                }

                if ($id === false) {
                    throw new NotFoundException('The option ' . $option . ' was not found');
                } else {
                    $fileContent->forget($id);
                }
            }
        }

        $this->fileContent = $fileContent;

        return $this;
    }

    /**
     * Get all options of the target
     *
     * @param string $target Target
     *
     * @return bool|\Illuminate\Support\Collection
     */
    public function getOptions($target)
    {
        $fileContent = $this->get();

        $thisTarget = $this->findTargetDefinition($fileContent, $target);

        $nextTarget = $this->findNextTargetDefinition($fileContent, $thisTarget);

        for ($i = $thisTarget + 1; $i < $nextTarget; $i++) {
            if ($fileContent->has($i)) {
                $options[$i] = $fileContent->get($i);
            }
        }

        if (empty($options)) {
            return false;
        } else {
            return collect(array_values($options));
        }
    }

    /**
     * Find a target definition
     *
     * @param Collection $fileContent Collection of the file's content
     * @param string     $target      Target
     *
     * @return bool|mixed
     */
    protected function findTargetDefinition(Collection $fileContent, $target)
    {
        $id = $this->findFirstTargetDefinition($fileContent);

        $lastKey = $fileContent->keys()->last();

        for ($i = $id; $i <= $lastKey; $i++) {
            if ($fileContent->has($i)) {
                if ($fileContent->get($i) === 'Target ' . $target) {
                    return $i;
                }
            }

            // So here we are, last line
            // This means we didn't find the index
            // So let's throw an exception here and go home
            if ($i === $lastKey) {
                return false;
            }
        }

        return false;
    }

    /**
     * Find the target definition after the given one
     *
     * @param Collection $fileContent Collection of the file's content
     * @param integer    $id          id inside the $fileContent collection
     *
     * @return bool
     */
    protected function findNextTargetDefinition(Collection $fileContent, $id)
    {
        $lastKey = $fileContent->keys()->last();

        $id++;

        for ($i = $id; $i <= $lastKey; $i++) {
            if ($fileContent->has($i)) {
                if (substr($fileContent->get($i), 0, 6) === 'Target') {
                    return $i;
                }
            }

            if ($i === $lastKey) {
                return false;
            }
        }

        return false;
    }

    protected function isOptionSet($target, $option)
    {

    }
}