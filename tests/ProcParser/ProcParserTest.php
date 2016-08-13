<?php

namespace MrCrankHank\IetParser\ProcParser;

use MrCrankHank\IetParser\Parser\ProcParser;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use MrCrankHank\IetParser\TestTrait;
use PHPUnit_Framework_TestCase;

class ProcParserTest extends PHPUnit_Framework_TestCase
{
    use TestTrait;

    public static function getSessionSingleProvider()
    {
        return [
            // get data for single iqn identified by iqn (one session)
            ['session.1', 'iqn.2014-08.local.example.san01:ps', 1,
                [
                    'iqn' => 'iqn.2014-08.local.example.san01:ps',
                    'tid' => '1',
                    '4786175038521408' => [
                        'initiator' => 'iqn.1991-05.com.microsoft:ps.example.local',
                        'cid' => '1',
                        'ip' => '192.168.100.51',
                        'state' => 'active',
                        'hd' => 'none',
                        'dd' => 'none'
                    ]
                ]
            ],

            // get data for single iqn identified by iqn (two sessions)
            ['session.1', 'iqn.2014-08.local.example.san01:server21', 12,
                [
                    'iqn' => 'iqn.2014-08.local.example.san01:server21',
                    'tid' => '12',
                    '2814750689919040' => [
                        'initiator' => 'iqn.1991-05.com.microsoft:server21.example.local',
                        'cid' => '1',
                        'ip' => '192.168.100.11',
                        'state' => 'active',
                        'hd' => 'none',
                        'dd' => 'none'
                    ],
                    '2343447334519040' => [
                        'initiator' => 'iqn.1991-05.com.microsoft:server',
                        'cid' => '1',
                        'ip' => '8.8.8.8',
                        'state' => 'active',
                        'hd' => 'none',
                        'dd' => 'none'
                    ]
                ]
            ],
        ];
    }

    /**
     * Retrieve data from a iqn identified by iqn and tid.
     * Compare them to prove that they return the same content.
     *
     * @param $file
     * @param $target
     * @param $tid
     * @param $expectedData
     *
     * @dataProvider getSessionSingleProvider
     */
    public function testGetSessionSingle($file, $target, $tid, $expectedData)
    {
        $dir = __DIR__ . DIRECTORY_SEPARATOR . 'files';

        $local = new Local($dir, LOCK_EX);

        $filesystem = new Filesystem($local);

        $parser = new ProcParser($filesystem, $file);

        $iqnData = $parser->getSession($target);

        $tidData = $parser->getSession($tid);

        $this->assertEquals($iqnData, $expectedData);

        $this->assertEquals($tidData, $expectedData);
    }

    public function testGetSessionMultiple()
    {

    }

    public static function getVolumeSingleProvider()
    {
        return [
            ['volume.1', 'iqn.2014-08.local.example.san01:session', 2,
                [
                    'iqn' => 'iqn.2014-08.local.example.san01:session',
                    'tid' => '2',
                    0 => [
                        'state' => '0',
                        'iotype' => 'fileio',
                        'iomode' => 'wt',
                        'blocks' => '209715200',
                        'blocksize' => '512',
                        'path' => '/dev/VG_Datastore02/LV_session'
                    ]
                ]
            ],
            ['volume.1', 'iqn.2014-08.local.example.san01:time', 11,
                [
                    'iqn' => 'iqn.2014-08.local.example.san01:time',
                    'tid' => '11',
                    0 => [
                        'state' => '0',
                        'iotype' => 'blockio',
                        'iomode' => 'wt',
                        'blocks' => '419430400',
                        'blocksize' => '512',
                        'path' => '/dev/VG_Datastore03/LV_time'
                    ],
                    1 => [
                        'state' => '0',
                        'iotype' => 'blockio',
                        'iomode' => 'wt',
                        'blocks' => '309420400',
                        'blocksize' => '512',
                        'path' => '/dev/VG_Datastore/LV_time_2'
                    ]
                ]
            ]
        ];
    }

    /**
     * @param $file
     * @param $target
     * @param $tid
     * @param $expectedData
     *
     * @dataProvider getVolumeSingleProvider
     */
    public function testGetVolumeSingle($file, $target, $tid, $expectedData)
    {
        $dir = __DIR__ . DIRECTORY_SEPARATOR . 'files';

        $local = new Local($dir, LOCK_EX);

        $filesystem = new Filesystem($local);

        $parser = new ProcParser($filesystem, $file);

        $iqnData = $parser->getVolume($target);

        $tidData = $parser->getVolume($tid);

        $this->assertEquals($iqnData, $expectedData);

        $this->assertEquals($tidData, $expectedData);
    }

    public function testGetVolumeMultiple()
    {

    }
}