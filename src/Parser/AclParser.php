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
    public function add()
    {

    }

    public function delete()
    {

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
        $line = $this->_findIqn();

        // explode array by comma we get everything
        // here except the first acl because
        // it is not separated by a comma
        $acls = collect(explode(',', $line));

        // explode first item line to get the
        // acl after the target definition
        $acl = explode(' ', $acls[0]);

        // remove index with iqn
        unset($acls[0]);

        if (empty($acls->all())) {
            throw new ParserErrorException('The target ' . $this->target . ' has no acls');
        }

        // prepend the extract acl to the collection
        $acls->prepend($acl[1]);

        // trim spaces
        $acls = $acls->map(function($item, $key) {
            return trim($item);
        });

        return collect($acls)->values();
    }

    private function _findIqn()
    {
        return $this->fileContent->first(function($key, $value) {
            if (strpos($value, $this->target) !== false) {
                return true;
            }
        });
    }
}