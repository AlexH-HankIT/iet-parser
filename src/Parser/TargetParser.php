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
use League\Flysystem\Filesystem;

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
    protected $target;
    protected $targetId;
    protected $nextTargetId;

    public function __construct(Filesystem $filesystem, $filePath, $target)
    {
        parent::__construct($filesystem, $filePath);

        $this->target = $target;
        $this->fileContent = $this->get();
        $this->targetId = $this->findTargetDefinition();
        $this->nextTargetId = $this->findNextTargetDefinition();
    }

    /**
     * Add a target
     *
     * @return $this
     *
     * @throws DuplicationErrorException
     */
    public function addTarget()
    {
        if ($this->targetId === false) {
            $this->fileContent->push('Target ' . $this->target, 'new');
        } else {
            throw new DuplicationErrorException('The target ' . $this->target . ' already exists');
        }

        return $this;
    }

    /**
     * Delete a target
     *
     * @return $this
     *
     * @throws NotFoundException
     * @throws TargetNotEmptyException
     */
    public function deleteTarget()
    {
        $options = $this->getOptions();

        if ($options === false) {
            if ($this->targetId === false) {
                throw new NotFoundException('The target ' . $this->target . ' was not found');
            } else {
                $this->fileContent->forget($this->targetId);
            }

            return $this;
        } else {
            throw new TargetNotEmptyException('The target ' . $this->target . ' has options defined');
        }
    }

    /**
     * Add a option to a target
     * Updates are also supported
     *
     * @param string $option Option
     *
     * @return $this
     *
     * @throws NotFoundException
     */
    public function addOption($option)
    {
        if ($this->targetId === false) {
            throw new NotFoundException('The target ' . $this->target . ' was not found');
        } else {
            $key = $this->isOptionSet($option);

            if ($key === false) {
                $target = $this->fileContent->get($this->targetId);
                $this->fileContent->put($this->targetId, $target . "\n" . $option);
            } else {
                // Replace existing option with new one
                $this->fileContent->put($key, $option);
            }
        }

        return $this;
    }

    /**
     * Delete a option
     * This should not be used to delete a lun or users
     *
     * @param string  $option     Option without value
     *
     * @return $this
     *
     * @throws NotFoundException
     */
    public function deleteOption($option)
    {
        if ($this->targetId === false) {
            throw new NotFoundException('The target ' . $this->target . ' was not found');
        } else {
            $options = $this->getOptions();

            if ($options === false) {
                throw new NotFoundException('The target ' . $this->target . ' has no options');
            } else {
                $key = $this->isOptionSet($option);

                if ($key === false) {
                    throw new NotFoundException('The option ' . $option . ' was not found');
                } else {
                    $this->fileContent->forget($key);
                    return $this;
                }
            }
        }
    }

    /**
     * Get all options of the target
     *
     * @return bool|\Illuminate\Support\Collection
     */
    public function getOptions()
    {
        for ($i = $this->targetId + 1; $i < $this->nextTargetId; $i++) {
            if ($this->fileContent->has($i)) {
                $options[$i] = $this->fileContent->get($i);
            }
        }

        if (empty($options)) {
            return false;
        } else {
            return collect(array_values($options));
        }
    }

    /**
     * Retrieve all or a specific lun
     *
     * @param bool $id
     *
     * @return bool|Collection
     *
     * @throws NotFoundException
     */
    public function getLun($id = false)
    {
        for ($i = $this->targetId; $i < $this->nextTargetId; $i++) {
            if ($this->fileContent->has($i)) {
                if (substr($this->fileContent->get($i), 0, 4) === 'Lun ') {
                    $lun = explode(' ', $this->fileContent->get($i));

                    $luns[$i]['id'] = $lun[1];

                    $options = explode(',', $lun[2]);

                    foreach ($options as $option) {
                        $temp = explode('=', $option);

                        $luns[$i][strtolower($temp[0])] = $temp[1];
                    }

                    if ($id !== false && $id == $lun[1]) {
                        return collect($luns[$i])->values();
                    } else if ($id !== false) {
                        throw new NotFoundException('The lun with the id of ' . $id . ' was not found');
                    }
                }
            }
        }

        if (empty($luns)) {
            return false;
        } else {
            return collect($luns)->values();
        }
    }

    /*public function addLun($path, $type = 'fileio', $scsiId = null, $scsiSN = null, $ioMode = null, $blockSize = null)
    {
        $fileContent = $this->get();

        $line = [
            'Lun',
            ''
        ];
    }*/

    /**
     * Find a target definition
     *
     * @return bool|mixed
     */
    protected function findTargetDefinition()
    {
        $id = $this->findFirstTargetDefinition($this->fileContent);

        $lastKey = $this->fileContent->keys()->last();

        for ($i = $id; $i <= $lastKey; $i++) {
            if ($this->fileContent->has($i)) {
                if ($this->fileContent->get($i) === 'Target ' . $this->target) {
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
     * @return bool
     */
    protected function findNextTargetDefinition()
    {
        $lastKey = $this->fileContent->keys()->last();

        $id = $this->targetId + 1;

        for ($i = $id; $i <= $lastKey; $i++) {
            if ($this->fileContent->has($i)) {
                if (substr($this->fileContent->get($i), 0, 6) === 'Target') {
                    return $i;
                }
            }

            if ($i === $lastKey) {
                return false;
            }
        }

        return false;
    }

    /**
     * Checks if a option is already set
     *
     * @param string $option Option
     *
     * @return bool|mixed
     *
     * @throws NotFoundException
     */
    protected function isOptionSet($option)
    {
        if ($this->targetId === false) {
            throw new NotFoundException('The target ' . $this->target . ' was not found');
        } else {
            $options = $this->getOptions();

            if ($options === false) {
                throw new NotFoundException('The target ' . $this->target . ' has no options');
            } else {
                for ($i = $this->targetId; $i < $this->nextTargetId; $i++) {
                    if ($this->fileContent->has($i)) {
                        $line = explode(" ", ($this->fileContent->get($i)));

                        if ($line[0] === $option) {
                            return $i;
                        }
                    }
                }

                return false;
            }
        }
    }

    /*protected function getNextFreeLun()
    {
        if ($this->nextTargetId === false) {
            return false;
        } else {

        }
    }*/
}