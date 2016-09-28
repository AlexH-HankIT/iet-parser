<?php

/**
 * This file contains the ProcParser class
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

use MrCrankHank\IetParser\Interfaces\FileInterface;
use MrCrankHank\IetParser\Interfaces\ParserInterface;
use MrCrankHank\IetParser\Interfaces\ProcParserInterface;
use MrCrankHank\IetParser\Exceptions\ParserErrorException;

/**
 * Class ProcParser
 *
 * @category Parser
 * @package  MrCrankHank\IetParser\Parser
 * @author   Alexander Hank <mail@alexander-hank.de>
 * @license  Apache License 2.0 http://www.apache.org/licenses/LICENSE-2.0.txt
 * @link     null
 */
class ProcParser extends Parser implements ParserInterface, ProcParserInterface {
    /**
     * Helper property. To store the last
     * iqn for the next loop iteration
     *
     * @var int|boolean
     */
    private $index;

    /**
     * Helper property. To store the last
     * id for the next loop iteration.
     *
     * Used for sessions and luns. Because
     * there *can* be multiple.
     *
     * @var
     */
    private $id;

    /**
     * Indicates if the file is empty
     *
     * @var bool
     */
    private $empty = false;

    /**
     * By default this class returns the iqn
     * as array/collection index. If this
     * is set to true. The tid will be
     * used instead.
     *
     * @var bool
     */
    private $tidIndex = false;

    public function __construct(FileInterface $file, $target = null)
    {
        parent::__construct($file, $target);

        if ($this->fileContent->isEmpty()) {
            $this->empty = true;
        } else {
            // remove spaces and the ending/beginning
            $this->fileContent->transform(function ($line, $key) {
                return trim($line);
            });
        }
    }

    /**
     * Read the session file normally found in /proc/net/iet/session.
     * And return the information as a collection for easy use.
     *
     * @throws ParserErrorException
     *
     * @return \Illuminate\Support\Collection|bool
     */
    public function getSession()
    {
        if ($this->empty) {
            return false;
        }

        if (is_int($this->target)) {
            return collect($this->_parseSession(true)->get($this->target));
        } else if (is_null($this->target)) {
            return $this->_parseSession($this->tidIndex);
        } else {
            // if target is not a boolean or integer, it has to be a string aka iqn
            return collect($this->_parseSession(false)->get($this->target));
        }
    }

    /**
     * Read the volume file normally found /proc/net/iet/volume.
     * And return the information as a collection for easy use.
     *
     * @throws ParserErrorException
     *
     * @return bool|\Illuminate\Support\Collection
     */
    public function getVolume()
    {
        if ($this->empty) {
            return false;
        }

        if (is_int($this->target)) {
            return collect($this->_parseVolume(true)->get($this->target));
        } else if (is_null($this->target)) {
            return $this->_parseVolume($this->tidIndex);
        } else {
            // if target is not a boolean or integer, it has to be a string aka iqn
            return collect($this->_parseVolume(false)->get($this->target));
        }
    }

    /**
     * Setter for the $tidIndex property
     *
     * @param $tidIndex boolean tidIndex
     *
     * @return $this
     */
    public function setTidIndex($tidIndex)
    {
        $this->tidIndex = $tidIndex;

        return $this;
    }

    /**
     * Getter for the $tidIndex property
     *
     * @return bool
     */
    public function getTidIndex()
    {
        return $this->tidIndex;
    }

    /**
     * @return bool
     */
    public function volumeExists()
    {
        $data = $this->getVolume();

        if ($data === false) {
            return false;
        }

        return isset($data[$this->target]);
    }

    /**
     * @param $sid
     *
     * @return bool
     */
    public function sessionExists($sid)
    {
        $data = $this->getSession();

        if ($data === false) {
            return false;
        }

        return isset($data[$this->target]);
    }

    /**
     * Loop through the file and check with what kind of line we deal with.
     * Every target has a line inside the session file, even if it doesn't
     * have any sessions. The first line always contains the target name
     * and the tid. The following lines contains the session information
     * it is unclear how many sessions the target has, so we have to
     * check every line for its type and data. It's a bit messy.
     *
     * @param $tidIndex boolean Indicates if the array/collection should be associative
     *
     * @throws ParserErrorException
     *
     * @return mixed
     */
    private function _parseSession($tidIndex)
    {
        foreach ($this->fileContent as $line) {
            if (substr($line, 0, 3) === 'tid') {
                // Check for the target definition line
                // It contains the name and tid
                preg_match("/name:(.*)/", $line, $iqn);
                preg_match("/tid:([0-9].*?) /", $line, $tid);

                // save the data from this iteration
                // so the next cycle knows to which
                // target it should add the data
                if ($tidIndex) {
                    $this->index = $tid[1];
                } else {
                    $this->index = $iqn[1];
                }

                $data[$this->index]['iqn'] = $iqn[1];
                $data[$this->index]['tid'] = $tid[1];
            }

            // Check for the first session line.
            // It contains the sid and initiator
            if (substr($line, 0, 3) === 'sid') {
                if (is_null($this->index)) {
                    throw new ParserErrorException('The session file is malformed');
                }

                preg_match("/sid:(.*?) /", $line, $sid);
                preg_match("/initiator:(.*)/", $line, $initiator);

                $this->id = $sid[1];

                $data[$this->index][$this->id]['cid'] = $sid[1];
                $data[$this->index][$this->id]['initiator'] = $initiator[1];
            }

            // Check for the second session line
            // It contains the cid, ip, state hd and dd
            if (substr($line, 0, 3) === 'cid') {
                if (is_null($this->index)) {
                    throw new ParserErrorException('The session file is malformed');
                }

                preg_match("/cid:([0-9].*?) /", $line, $cid);
                preg_match("/ip:(.*?) /", $line, $ip);
                preg_match("/state:(.*?) /", $line, $state);
                preg_match("/hd:(.*?) /", $line, $hd);
                preg_match("/dd:(.*)/", $line, $dd);

                $data[$this->index][$this->id]['cid'] = $cid[1];
                $data[$this->index][$this->id]['ip'] = $ip[1];
                $data[$this->index][$this->id]['state'] = $state[1];
                $data[$this->index][$this->id]['hd'] = $hd[1];
                $data[$this->index][$this->id]['dd'] = $dd[1];
            }
        }

        return collect($data);
    }

    /**
     * Does the same as the _parseSession function,
     * but for volumes ;-)
     *
     * @param $tidIndex
     * @return \Illuminate\Support\Collection
     * @throws ParserErrorException
     */
    private function _parseVolume($tidIndex)
    {
        foreach ($this->fileContent as $line) {
            if (substr($line, 0, 3) === 'tid') {
                // Check for the target definition line
                // It contains the name and tid
                preg_match("/name:(.*)/", $line, $iqn);
                preg_match("/tid:([0-9].*?) /", $line, $tid);

                // save the data from this iteration
                // so the next cycle knows to which
                // target it should add the data
                if ($tidIndex) {
                    $this->index = $tid[1];
                } else {
                    $this->index = $iqn[1];
                }

                $data[$this->index]['iqn'] = $iqn[1];
                $data[$this->index]['tid'] = $tid[1];
            }

            if (substr($line, 0, 3) === 'lun') {
                if (is_null($this->index)) {
                    throw new ParserErrorException('The volume file is malformed');
                }

                preg_match("/lun:([0-9])/", $line, $id);
                preg_match("/state:([0-9])/", $line, $state);
                preg_match("/iotype:(.*?) /", $line, $iotype);
                preg_match("/iomode:(.*?) /", $line, $iomode);
                preg_match("/blocks:(.*?) /", $line, $blocks);
                preg_match("/blocksize:(.*?) /", $line, $blocksize);
                preg_match("/path:(.*)/", $line, $path);

                $data[$this->index][$id[1]]['state'] = $state[1];
                $data[$this->index][$id[1]]['iotype'] = $iotype[1];
                $data[$this->index][$id[1]]['iomode'] = $iomode[1];
                $data[$this->index][$id[1]]['blocks'] = $blocks[1];
                $data[$this->index][$id[1]]['blocksize'] = $blocksize[1];
                $data[$this->index][$id[1]]['path'] = $path[1];
            }
        }

        return collect($data);
    }
}