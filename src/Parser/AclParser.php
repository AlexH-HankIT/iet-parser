<?php

/**
 * This file contains the AclParser class.
 *
 * PHP version 5.6
 *
 * @category Parser
 *
 * @author   Alexander Hank <mail@alexander-hank.de>
 * @license  Apache License 2.0 http://www.apache.org/licenses/LICENSE-2.0.txt
 *
 * @link     null
 */
namespace MrCrankHank\IetParser\Parser;

use MrCrankHank\IetParser\Exceptions\DuplicationErrorException;
use MrCrankHank\IetParser\Exceptions\NotFoundException;
use MrCrankHank\IetParser\Exceptions\ParserErrorException;
use MrCrankHank\IetParser\Interfaces\AclParserInterface;
use MrCrankHank\IetParser\Interfaces\FileInterface;
use MrCrankHank\IetParser\Interfaces\ParserInterface;

/**
 * Class AclParser.
 *
 * Add/delete targets to/from the iet config file
 * Add/delete options to/from a target
 * Get a target with options
 *
 * @category Parser
 *
 * @author   Alexander Hank <mail@alexander-hank.de>
 * @license  Apache License 2.0 http://www.apache.org/licenses/LICENSE-2.0.txt
 *
 * @link     null
 */
class AclParser extends Parser implements ParserInterface, AclParserInterface
{
    /**
     * AclParser constructor.
     *
     * @param FileInterface $file
     * @param null          $target
     */
    public function __construct(FileInterface $file, $target = null)
    {
        parent::__construct($file, $target);

        $this->targetId = $this->findIqn();
    }

    /**
     * Add a acl for a target.
     *
     * @param $add
     *
     * @throws DuplicationErrorException
     *
     * @return $this
     */
    public function add($add)
    {
        // get all acl for $this->iqn
        $acl = $this->get();

        if ($acl->isEmpty()) {
            $this->fileContent->push($this->target.' '.$add);
        } else {
            $key = $acl->search($add);

            if ($key !== false) {
                throw new DuplicationErrorException('The acl '.$add.' was already added');
            }

            $acl->push($add);

            $line = $this->target.' '.$acl->implode(', ');

            $this->fileContent->put($this->targetId, $line);
        }

        return $this;
    }

    /**
     * Delete acl from a target.
     *
     * @param $delete
     *
     * @throws NotFoundException
     * @throws ParserErrorException
     *
     * @return $this
     */
    public function delete($delete)
    {
        if ($this->fileContent->isEmpty()) {
            throw new ParserErrorException('The file is empty');
        }

        // get all acl for $this->iqn
        $acl = $this->get();

        if ($acl->isEmpty()) {
            throw new NotFoundException('The acl '.$delete.' was not found on target '.$this->target);
        }

        $key = $acl->search($delete);

        if ($key === false) {
            throw new NotFoundException('The acl '.$delete.' was not found on target '.$this->target);
        }

        // Remove the acl
        $acl->forget($key);

        // When the target has no acl left
        // we delete the whole line
        if ($acl->isEmpty()) {
            $this->fileContent->forget($this->targetId);
        } else {
            $line = $this->target.' '.$acl->implode(', ');
            $this->fileContent->put($this->targetId, $line);
        }

        return $this;
    }

    /**
     * Get single or multiple acls.
     *
     * @param bool $all
     *
     * @return \Illuminate\Support\Collection
     */
    public function get($all = false)
    {
        if ($all === false) {
            return $this->getSingle();
        }

        return $this->getAll();
    }

    /**
     * Get all acls.
     *
     * @return \Illuminate\Support\Collection
     */
    private function getAll()
    {
        foreach ($this->fileContent as $key => $line) {
            // explode array by comma we get everything
            // here except the first acl because
            // it is not separated by a comma
            $acls = collect(explode(',', $line));

            // explode first item line to get the
            // acl after the target definition
            $acl = explode(' ', $acls[0]);

            // remove index with iqn
            unset($acls[0]);

            // prepend the extract acl to the collection
            $acls->prepend($acl[1]);

            $data[$acl[0]] = $acls;

            // trim spaces
            $data[$acl[0]] = $acls->map(function ($item, $key) {
                return trim($item);
            });
        }

        return collect($data);
    }

    /**
     * Get single acl.
     *
     * @throws ParserErrorException
     *
     * @return \Illuminate\Support\Collection
     */
    private function getSingle()
    {
        if ($this->targetId === false) {
            return collect([]);
        }

        $line = $this->fileContent->get($this->targetId);

        // explode array by comma we get everything
        // here except the first acl because
        // it is not separated with a comma
        $acls = collect(explode(',', $line));

        // explode first item line to get the
        // acl after the target definition
        $acl = explode(' ', $acls[0]);

        // remove index with iqn
        unset($acls[0]);

        if (!isset($acl[1])) {
            throw new ParserErrorException('The target '.$this->target.' has no acls');
        }

        // prepend the extract acl to the collection
        $acls->prepend($acl[1]);

        // trim spaces
        $acls = $acls->map(function ($item, $key) {
            return trim($item);
        });

        return collect($acls)->values();
    }

    /**
     * Find iqn in file.
     *
     * @return mixed
     */
    private function findIqn()
    {
        return $this->fileContent->search(function ($item, $key) {
            if (strpos($item, $this->target) !== false) {
                return true;
            }
        });
    }
}
