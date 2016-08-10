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
use MrCrankHank\IetParser\Exceptions\DuplicationErrorException;
use MrCrankHank\IetParser\Exceptions\NotFoundException;
use MrCrankHank\IetParser\Exceptions\ParserErrorException;

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
    public function __construct(Filesystem $filesystem, $filePath, $target)
    {
        parent::__construct($filesystem, $filePath, $target);

        $this->targetId = $this->_findIqn();
    }

    public function add($add)
    {
        // get all acl for $this->iqn
        $acl = $this->get();

        if ($acl->isEmpty()) {
            $this->fileContent->push($this->target . ' ' . $add);
        } else {
            $key = $acl->search($add);

            if ($key === false) {
                $acl->push($add);

                $line = $this->target . ' ' . $acl->implode(', ');

                $this->fileContent->put($this->targetId, $line);
            } else {
                throw new DuplicationErrorException('The acl ' . $add . ' was already added');
            }
        }

        return $this;
    }

    public function delete($delete)
    {
        if ($this->fileContent->isEmpty()) {
            throw new ParserErrorException('The file is empty');
        }

        // get all acl for $this->iqn
        $acl = $this->get();

        if ($acl->isEmpty()) {
            throw new NotFoundException('The acl ' . $delete . ' was not found on target ' . $this->target);
        }

        $key = $acl->search($delete);

        if ($key === false) {
            throw new NotFoundException('The acl ' . $delete . ' was not found on target ' . $this->target);
        }

        // Remove the acl
        $acl->forget($key);

        // When the target has no acl left
        // we delete the whole line
        if ($acl->isEmpty()) {
            $this->fileContent->forget($this->targetId);
        } else {
            $line = $this->target . ' ' . $acl->implode(', ');
            $this->fileContent->put($this->targetId, $line);
        }

        return $this;
    }

    public function get($all = false)
    {
        if ($all === false) {
            return $this->_getSingle();
        } else {
            return $this->_getAll();
        }
    }

    private function _getAll()
    {
        foreach($this->fileContent as $key => $line) {
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
            $data[$acl[0]] = $acls->map(function($item, $key) {
                return trim($item);
            });
        }

        return collect($data);
    }

    private function _getSingle()
    {
        $line = $this->fileContent->get($this->targetId);

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

        if ($acls->isEmpty()) {
            throw new ParserErrorException('The target ' . $this->target . ' has no acls');
        }

        // trim spaces
        $acls = $acls->map(function($item, $key) {
            return trim($item);
        });

        return collect($acls)->values();
    }

    private function _findIqn()
    {
       return $this->fileContent->search(function($item, $key) {
           if (strpos($item, $this->target) !== false) {
               return true;
           }
       });
    }
}