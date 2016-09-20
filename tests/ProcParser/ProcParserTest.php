<?php

namespace MrCrankHank\IetParser\ProcParser;

use PHPUnit_Framework_TestCase;
use MrCrankHank\IetParser\File;
use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\Local;
use MrCrankHank\IetParser\TestTrait;
use MrCrankHank\IetParser\Parser\ProcParser;

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

        $file = (new File)->readContent($filesystem, $file);

        $parser = new ProcParser($file, $target);

        $this->assertEquals($parser->getSession(), collect($expectedData));

        $parser = new ProcParser($file, $tid);

        $this->assertEquals($parser->getSession(), collect($expectedData));
    }

    public static function getSessionMultipleProvider()
    {
        return [
            ['session.1', array('iqn.2014-08.local.example.san01:mssql01' => array('iqn' => 'iqn.2014-08.local.example.san01:mssql01', 'tid' => '18', '1125900829655104' => array('initiator' => 'iqn.1991-05.com.microsoft:mssql01.example.local', 'cid' => '1', 'ip' => '192.168.100.20', 'state' => 'active', 'hd' => 'none', 'dd' => 'none',),), 'iqn.2014-08.local.example.san01:dms' => array('iqn' => 'iqn.2014-08.local.example.san01:dms', 'tid' => '17', '562950876233792' => array('initiator' => 'iqn.1991-05.com.microsoft:dms.example.local', 'cid' => '1', 'ip' => '192.168.100.247', 'state' => 'active', 'hd' => 'none', 'dd' => 'none',),), 'iqn.2014-08.local.example.san01:server10' => array('iqn' => 'iqn.2014-08.local.example.san01:server10', 'tid' => '16', '4504700061810752' => array('initiator' => 'iqn.1991-05.com.microsoft:server10.example.local', 'cid' => '1', 'ip' => '192.168.100.61', 'state' => 'active', 'hd' => 'none', 'dd' => 'none',),), 'iqn.2014-08.local.example.san01:backuppc' => array('iqn' => 'iqn.2014-08.local.example.san01:backuppc', 'tid' => '15', '281474997486080' => array('initiator' => 'iqn.1993-08.org.debian:01:792572ead4e4', 'cid' => '0', 'ip' => '192.168.100.44', 'state' => 'active', 'hd' => 'none', 'dd' => 'none',),), 'iqn.2014-08.local.example.san01:server22' => array('iqn' => 'iqn.2014-08.local.example.san01:server22', 'tid' => '14', '2533275713208384' => array('initiator' => 'iqn.1991-05.com.microsoft:server22.example.local', 'cid' => '1', 'ip' => '192.168.100.60', 'state' => 'active', 'hd' => 'none', 'dd' => 'none',),), 'iqn.2014-08.local.example.san01:server12' => array('iqn' => 'iqn.2014-08.local.example.san01:server12', 'tid' => '13', '3659175620051008' => array('initiator' => 'iqn.1991-05.com.microsoft:server12.example.local', 'cid' => '1', 'ip' => '192.168.100.54', 'state' => 'active', 'hd' => 'none', 'dd' => 'none',),), 'iqn.2014-08.local.example.san01:server21' => array('iqn' => 'iqn.2014-08.local.example.san01:server21', 'tid' => '12', '2814750689919040' => array('initiator' => 'iqn.1991-05.com.microsoft:server21.example.local', 'cid' => '1', 'ip' => '192.168.100.11', 'state' => 'active', 'hd' => 'none', 'dd' => 'none',), '2343447334519040' => array('initiator' => 'iqn.1991-05.com.microsoft:server', 'cid' => '1', 'ip' => '8.8.8.8', 'state' => 'active', 'hd' => 'none', 'dd' => 'none',),), 'iqn.2014-08.local.example.san01:zeiterfassung' => array('iqn' => 'iqn.2014-08.local.example.san01:zeiterfassung', 'tid' => '11', '1688850783076416' => array('initiator' => 'iqn.1991-05.com.microsoft:time.example.local', 'cid' => '1', 'ip' => '192.168.100.6', 'state' => 'active', 'hd' => 'none', 'dd' => 'none',),), 'iqn.2014-08.local.example.san01:ex01' => array('iqn' => 'iqn.2014-08.local.example.san01:ex01', 'tid' => '10', '844425852944448' => array('initiator' => 'iqn.1991-05.com.microsoft:ex01.example.local', 'cid' => '1', 'ip' => '192.168.100.43', 'state' => 'active', 'hd' => 'none', 'dd' => 'none',),), 'iqn.2014-08.local.example.san01:server17' => array('iqn' => 'iqn.2014-08.local.example.san01:server17', 'tid' => '9', '3096225666629696' => array('initiator' => 'iqn.1991-05.com.microsoft:server17.example.local', 'cid' => '1', 'ip' => '192.168.100.59', 'state' => 'active', 'hd' => 'none', 'dd' => 'none',),), 'iqn.2014-08.local.example.san01:server18' => array('iqn' => 'iqn.2014-08.local.example.san01:server18', 'tid' => '8',), 'iqn.2014-08.local.example.san01:server16' => array('iqn' => 'iqn.2014-08.local.example.san01:server16', 'tid' => '7', '3377700643340352' => array('initiator' => 'iqn.1991-05.com.microsoft:server16.example.local', 'cid' => '1', 'ip' => '192.168.100.57', 'state' => 'active', 'hd' => 'none', 'dd' => 'none',),), 'iqn.2014-08.local.example.san01:base' => array('iqn' => 'iqn.2014-08.local.example.san01:base', 'tid' => '6', '1970325759787072' => array('initiator' => 'iqn.1991-05.com.microsoft:base.example.local', 'cid' => '1', 'ip' => '192.168.100.5', 'state' => 'active', 'hd' => 'none', 'dd' => 'none',),), 'iqn.2014-08.local.example.san01:server8' => array('iqn' => 'iqn.2014-08.local.example.san01:server8', 'tid' => '5', '4222125573472320' => array('initiator' => 'iqn.1991-05.com.microsoft:server8.example.local', 'cid' => '1', 'ip' => '192.168.100.56', 'state' => 'active', 'hd' => 'none', 'dd' => 'none',),), 'iqn.2014-08.local.example.san01:server7' => array('iqn' => 'iqn.2014-08.local.example.san01:server7', 'tid' => '4', '3940650596761664' => array('initiator' => 'iqn.1991-05.com.microsoft:server7.example.local', 'cid' => '1', 'ip' => '192.168.100.55', 'state' => 'active', 'hd' => 'none', 'dd' => 'none',),), 'iqn.2014-08.local.example.san01:maps' => array('iqn' => 'iqn.2014-08.local.example.san01:maps', 'tid' => '3',), 'iqn.2014-08.local.example.san01:session' => array('iqn' => 'iqn.2014-08.local.example.san01:session', 'tid' => '2', '2251800736497728' => array('initiator' => 'iqn.1991-05.com.microsoft:session.example.local', 'cid' => '1', 'ip' => '192.168.100.52', 'state' => 'active', 'hd' => 'none', 'dd' => 'none',),), 'iqn.2014-08.local.example.san01:ps' => array('iqn' => 'iqn.2014-08.local.example.san01:ps', 'tid' => '1', '4786175038521408' => array('initiator' => 'iqn.1991-05.com.microsoft:ps.example.local', 'cid' => '1', 'ip' => '192.168.100.51', 'state' => 'active', 'hd' => 'none', 'dd' => 'none',),),)]
        ];
    }

    /**
     * @param $file
     * @param $expectedData
     *
     * @dataProvider getSessionMultipleProvider
     */
    public function testGetSessionMultiple($file, $expectedData)
    {
        $dir = __DIR__ . DIRECTORY_SEPARATOR . 'files';

        $local = new Local($dir, LOCK_EX);

        $filesystem = new Filesystem($local);

        $file = (new File)->readContent($filesystem, $file);

        $parser = new ProcParser($file);

        $this->assertEquals($parser->getSession(), collect($expectedData));
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

        $file = (new File)->readContent($filesystem, $file);

        $parser = new ProcParser($file, $target);

        $this->assertEquals($parser->getVolume(), collect($expectedData));

        $parser = new ProcParser($file, $tid);

        $this->assertEquals($parser->getVolume(), collect($expectedData));
    }

    public static function getVolumeMultipleProvider()
    {
        return [
            ['volume.1', array('iqn.2014-08.local.example.san01:mssql01' => array('iqn' => 'iqn.2014-08.local.example.san01:mssql01', 'tid' => '18', 0 => array('state' => '0', 'iotype' => 'fileio', 'iomode' => 'wt', 'blocks' => '1059061760', 'blocksize' => '512', 'path' => '/dev/VG_Datastore03/LV_mssql01_backup',),), 'iqn.2014-08.local.example.san01:dms' => array('iqn' => 'iqn.2014-08.local.example.san01:dms', 'tid' => '17', 0 => array('state' => '0', 'iotype' => 'blockio', 'iomode' => 'wt', 'blocks' => '631242752', 'blocksize' => '512', 'path' => '/dev/VG_Datastore01/LV_dms',),), 'iqn.2014-08.local.example.san01:server10' => array('iqn' => 'iqn.2014-08.local.example.san01:server10', 'tid' => '16', 1 => array('state' => '0', 'iotype' => 'fileio', 'iomode' => 'wt', 'blocks' => '524288000', 'blocksize' => '512', 'path' => '/dev/VG_Datastore03/LV_server10DBBackup',), 0 => array('state' => '0', 'iotype' => 'fileio', 'iomode' => 'wt', 'blocks' => '838860800', 'blocksize' => '512', 'path' => '/dev/VG_Datastore02/LV_server10',),), 'iqn.2014-08.local.example.san01:backuppc' => array('iqn' => 'iqn.2014-08.local.example.san01:backuppc', 'tid' => '15', 0 => array('state' => '0', 'iotype' => 'blockio', 'iomode' => 'wt', 'blocks' => '1258291200', 'blocksize' => '512', 'path' => '/dev/VG_Datastore01/LV_backuppc',),), 'iqn.2014-08.local.example.san01:server22' => array('iqn' => 'iqn.2014-08.local.example.san01:server22', 'tid' => '14', 0 => array('state' => '0', 'iotype' => 'blockio', 'iomode' => 'wt', 'blocks' => '209715200', 'blocksize' => '512', 'path' => '/dev/VG_Datastore01/LV_server22',),), 'iqn.2014-08.local.example.san01:server12' => array('iqn' => 'iqn.2014-08.local.example.san01:server12', 'tid' => '13', 0 => array('state' => '0', 'iotype' => 'blockio', 'iomode' => 'wt', 'blocks' => '314572800', 'blocksize' => '512', 'path' => '/dev/VG_Datastore01/LV_server12',),), 'iqn.2014-08.local.example.san01:server21' => array('iqn' => 'iqn.2014-08.local.example.san01:server21', 'tid' => '12', 0 => array('state' => '0', 'iotype' => 'blockio', 'iomode' => 'wt', 'blocks' => '209715200', 'blocksize' => '512', 'path' => '/dev/VG_Datastore03/LV_server21',),), 'iqn.2014-08.local.example.san01:time' => array('iqn' => 'iqn.2014-08.local.example.san01:time', 'tid' => '11', 0 => array('state' => '0', 'iotype' => 'blockio', 'iomode' => 'wt', 'blocks' => '419430400', 'blocksize' => '512', 'path' => '/dev/VG_Datastore03/LV_time',), 1 => array('state' => '0', 'iotype' => 'blockio', 'iomode' => 'wt', 'blocks' => '309420400', 'blocksize' => '512', 'path' => '/dev/VG_Datastore/LV_time_2',),), 'iqn.2014-08.local.example.san01:ex01' => array('iqn' => 'iqn.2014-08.local.example.san01:ex01', 'tid' => '10', 0 => array('state' => '0', 'iotype' => 'blockio', 'iomode' => 'wt', 'blocks' => '1258291200', 'blocksize' => '512', 'path' => '/dev/VG_Datastore01/LV_ex01',),), 'iqn.2014-08.local.example.san01:server17' => array('iqn' => 'iqn.2014-08.local.example.san01:server17', 'tid' => '9', 0 => array('state' => '0', 'iotype' => 'fileio', 'iomode' => 'wt', 'blocks' => '314572800', 'blocksize' => '512', 'path' => '/dev/VG_Datastore02/LV_server17',),), 'iqn.2014-08.local.example.san01:server18' => array('iqn' => 'iqn.2014-08.local.example.san01:server18', 'tid' => '8', 0 => array('state' => '0', 'iotype' => 'fileio', 'iomode' => 'wt', 'blocks' => '419430400', 'blocksize' => '512', 'path' => '/dev/VG_Datastore01/LV_server18',),), 'iqn.2014-08.local.example.san01:server16' => array('iqn' => 'iqn.2014-08.local.example.san01:server16', 'tid' => '7', 0 => array('state' => '0', 'iotype' => 'fileio', 'iomode' => 'wt', 'blocks' => '419430400', 'blocksize' => '512', 'path' => '/dev/VG_Datastore02/LV_server16',),), 'iqn.2014-08.local.example.san01:base' => array('iqn' => 'iqn.2014-08.local.example.san01:base', 'tid' => '6', 1 => array('state' => '0', 'iotype' => 'fileio', 'iomode' => 'wt', 'blocks' => '419430400', 'blocksize' => '512', 'path' => '/dev/VG_Datastore02/LV_base',),), 'iqn.2014-08.local.example.san01:server8' => array('iqn' => 'iqn.2014-08.local.example.san01:server8', 'tid' => '5', 0 => array('state' => '0', 'iotype' => 'fileio', 'iomode' => 'wt', 'blocks' => '314572800', 'blocksize' => '512', 'path' => '/dev/VG_Datastore02/LV_server8',),), 'iqn.2014-08.local.example.san01:server7' => array('iqn' => 'iqn.2014-08.local.example.san01:server7', 'tid' => '4', 0 => array('state' => '0', 'iotype' => 'blockio', 'iomode' => 'wt', 'blocks' => '419430400', 'blocksize' => '512', 'path' => '/dev/VG_Datastore02/LV_server7',),), 'iqn.2014-08.local.example.san01:maps' => array('iqn' => 'iqn.2014-08.local.example.san01:maps', 'tid' => '3', 0 => array('state' => '0', 'iotype' => 'fileio', 'iomode' => 'wt', 'blocks' => '524288000', 'blocksize' => '512', 'path' => '/dev/VG_Datastore02/LV_maps',),), 'iqn.2014-08.local.example.san01:session' => array('iqn' => 'iqn.2014-08.local.example.san01:session', 'tid' => '2', 0 => array('state' => '0', 'iotype' => 'fileio', 'iomode' => 'wt', 'blocks' => '209715200', 'blocksize' => '512', 'path' => '/dev/VG_Datastore02/LV_session',),), 'iqn.2014-08.local.example.san01:ps' => array('iqn' => 'iqn.2014-08.local.example.san01:ps', 'tid' => '1', 0 => array('state' => '0', 'iotype' => 'fileio', 'iomode' => 'wt', 'blocks' => '209715200', 'blocksize' => '512', 'path' => '/dev/VG_Datastore02/LV_ps',),),),]
        ];
    }

    /**
     * @param $file
     * @param $expectedData
     *
     * @dataProvider getVolumeMultipleProvider
     */
    public function testGetVolumeMultiple($file, $expectedData)
    {
        $dir = __DIR__ . DIRECTORY_SEPARATOR . 'files';

        $local = new Local($dir, LOCK_EX);

        $filesystem = new Filesystem($local);

        $file = (new File)->readContent($filesystem, $file);

        $parser = new ProcParser($file);

        $this->assertEquals($parser->getVolume(), collect($expectedData));
    }
}