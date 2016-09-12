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
use League\Flysystem\FilesystemInterface;
use MrCrankHank\IetParser\Interfaces\ParserInterface;
use MrCrankHank\IetParser\Interfaces\TargetParserInterface;

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
class TargetParser extends Parser implements ParserInterface, TargetParserInterface
{
    /**
     * Line of the next target in the $this->fileContent collection
     *
     * @var bool
     */
    protected $nextTargetId;

    /**
     * Contains the id of the last added lun, if applicable
     *
     * @var string
     */
    protected $lastAddedLun;

    /**
     * TargetParser constructor.
     * @param FilesystemInterface $filesystem
     * @param string              $filePath
     * @param string              $target
     */
    public function __construct(FilesystemInterface $filesystem, $filePath, $target = null)
    {
        parent::__construct($filesystem, $filePath, $target);

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
        $this->_existsOrDie();

        $options = $this->getOptions();

        if ($options === false) {
            $this->fileContent->forget($this->targetId);
        } else {
            throw new TargetNotEmptyException('The target ' . $this->target . ' has options defined');
        }

        return $this;
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
        $this->_existsOrDie();

        $key = $this->isOptionSet($option);

        if ($key === false) {
            $target = $this->fileContent->get($this->targetId);
            $this->fileContent->put($this->targetId, $target . "\n" . $option);
        } else {
            // Replace existing option with new one
            $this->fileContent->put($key, $option);
        }

        return $this;
    }

    /**
     * Delete a option
     * This should not be used to delete a lun.
     * This function also works, if the value is unknown
     *
     * @param string  $option     Option without value
     *
     * @return $this
     *
     * @throws NotFoundException
     */
    public function deleteOption($option)
    {
        $this->_existsOrDie();

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

    /**
     * Get all options of the target
     *
     * ToDo: Add param for ignoring luns
     *
     * @return bool|\Illuminate\Support\Collection
     */
    public function getOptions()
    {
        if ($this->targetId + 1 === $this->nextTargetId) {
            // If there is another target definition in the next
            // line, then we have no reason to look for options
            return false;
        }

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
     * @return Collection|boolean
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
                        // preserve format in comparison
                        // to multiple luns
                        $data[0] = $luns[$i];

                        return collect($data);
                    }
                }
            }
        }

        // method should return specific lun
        // but it was not found
        if ($id !== false) {
            return false;
        // method should return all luns
        // but none where found
        } else if (empty($luns) && $id === false) {
            return false;
        // return all luns
        } else {
            return collect($luns)->values();
        }
    }

    /**
     * Add a lun to the target.
     * ID incrementation is supported.
     *
     * @param $path
     * @param string        $type      fileio|blockio|nullio
     * @param string|null   $scsiId    scsi_id
     * @param string|null   $scsiSN    scsi_sn
     * @param string|null   $ioMode    wb|ro|wt
     * @param string|null   $blockSize size
     *
     * @throws NotFoundException
     *
     * @return $this
     */
    public function addLun($path, $type = null, $scsiId = null, $scsiSN = null, $ioMode = null, $blockSize = null)
    {
        $this->_existsOrDie();

        $params['path'] = 'Path=' . $path;

        if (isset($type)) {
            $params['type'] = 'Type=' . $type;
        }

        if(isset($scsiId)) {
            $params['scsiId'] = 'ScsiId=' . $scsiId;
        }

        if (isset($scsiSN)) {
            $params['scsiIn'] = 'ScsiSN=' . $scsiSN;
        }

        if (isset($ioMode)) {
            $params['ioMode'] = 'IOMode=' . $ioMode;
        }

        if (isset($blockSize)) {
            $params['blocksize'] = 'BlockSize=' . $blockSize;
        }

        $id = $this->getNextFreeLun();

        $this->addOption('Lun ' . $id . ' ' . implode(',', $params));

        $this->lastAddedLun = $id;

        return $this;
    }

    /**
     * Delete lun from a target
     *
     * @param int $id id of the lun
     *
     * @return $this
     */
    public function deleteLun($id)
    {
        // this will throw a NotFoundException, if the lun does not exist
        $this->_lunExistsOrDie($id);

        for ($i = $this->targetId; $i < $this->nextTargetId; $i++) {
            if ($this->fileContent->has($i)) {
                if (substr($this->fileContent->get($i), 0, 5) === 'Lun ' . $id) {
                    $this->fileContent->forget($i);
                }
            }
        }

        return $this;
    }

    /**
     * Add a outgoing user to a target
     *
     * @param string $user     User
     * @param string $password Password
     */
    public function addOutgoingUser($user, $password)
    {
        $this->addOption('OutgoingUser ' . $user . ' ' . $password);
    }

    /**
     * Delete outgoing user from a target
     *
     * @param string $user     User
     * @param string $password Password
     */
    public function deleteOutgoingUser($user, $password)
    {
        $this->deleteOption('OutgoingUser ' . $user . ' ' . $password);
    }

    /**
     * Add a incoming user to atarget
     *
     * @param string $user     User
     * @param string $password Password
     */
    public function addIncomingUser($user, $password)
    {
        $this->addOption('Incoming ' . $user . ' ' . $password);
    }

    /**
     * Delete incoming user from a target
     *
     * @param string $user     User
     * @param string $password Password
     */
    public function deleteIncomingUser($user, $password)
    {
        $this->deleteOption('Incoming ' . $user . ' ' . $password);
    }

    /**
     * Return the last added lun
     *
     * @return mixed
     */
    public function getLastAddedLun()
    {
        return $this->lastAddedLun;
    }

    public function exists()
    {
        if ($this->targetId === false) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Find a target definition
     *
     * @return bool|mixed
     */
    protected function findTargetDefinition()
    {
        $id = $this->findFirstTargetDefinition($this->fileContent);

        if ($id !== false) {
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
     * Checks if a option is already set.
     * Ignores the value and only checks the key.
     *
     * @param string $option Option
     *
     * @return bool|mixed
     *
     * @throws NotFoundException
     */
    protected function isOptionSet($option)
    {
        $this->_existsOrDie();

        $options = $this->getOptions();

        if ($options === false) {
            return false;
        } else {
            for ($i = $this->targetId; $i < $this->nextTargetId; $i++) {
                if ($this->fileContent->has($i)) {
                    $line = explode(" ", ($this->fileContent->get($i)));

                    // Workaround, to detect luns correctly
                    if ($line[0] === 'Lun') {
                        if (strpos($option, $line[0] . ' ' . $line[1]) !== false) {
                            return $i;
                        }
                    } else {
                        // If the lines contains the option
                        // we know that the value is already set
                        if (strpos($option, $line[0]) !== false) {
                            return $i;
                        }
                    }
                }
            }

            return false;
        }
    }

    /**
     * Get the next free lun id
     *
     * @return bool|int
     */
    protected function getNextFreeLun()
    {
        if ($this->targetId === false) {
            return false;
        } else {
            $luns = $this->getLun();

            if ($luns === false) {
                return 0;
            }

            foreach ($luns as $key => $lun) {
                if (isset($luns[$key + 1])) {
                    if ($lun['id'] + 1 !== $luns[$key + 1]) {
                        return $lun['id'] + 2;
                    }
                } else {
                    return $lun['id'] + 1;
                }
            }

            return 0;
        }
    }

    /**
     * Throw an exception if the target does not exist
     *
     * @throws NotFoundException
     *
     * @return void
     */
    private function _existsOrDie()
    {
        if ($this->targetId === false) {
            throw new NotFoundException('The target ' . $this->target . ' was not found');
        }
    }

    private function _lunExistsOrDie($id)
    {
        $data = $this->getLun($id);

        if ($data === false) {
            throw new NotFoundException('The lun ' . $id . ' was not found on ' . $this->target);
        }
    }
}