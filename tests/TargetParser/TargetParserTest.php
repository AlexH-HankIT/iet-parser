<?php

namespace MrCrankHank\IetParser\tests;

use PHPUnit_Framework_TestCase;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use MrCrankHank\IetParser\Parser\TargetParser;
use MrCrankHank\IetParser\Parser\Normalizer;

class TargetParserDelete extends PHPUnit_Framework_TestCase
{
    protected $sampleFile = 'iet.sample.conf';
    protected $testFile = 'iet.test-running.conf';
    protected $dirs = ['case1_files'];

    public function testAddTarget()
    {
        $file = 'iet.expected.testAddTarget.conf';

        foreach($this->dirs as $dir) {
            $dir = __DIR__ . DIRECTORY_SEPARATOR . $dir;

            $objects = $this->normalize($dir, 'iqn.2016-08.test.ing.host:testAdd');

            if ($objects['normalizer']->check()) {
                $objects['parser']->addTarget()->write();
            } else {
                $this->fail("The normalizer did not properly normalize the file!");
            }

            $this->assertFileEquals($dir . DIRECTORY_SEPARATOR . $file, $dir . DIRECTORY_SEPARATOR . $this->testFile);
        }
    }

    public function testAddTargetDuplicationError()
    {
        $this->expectException('MrCrankHank\IetParser\Exceptions\DuplicationErrorException');

        foreach($this->dirs as $dir) {
            $dir = __DIR__ . DIRECTORY_SEPARATOR . $dir;

            $objects = $this->normalize($dir, 'iqn.2016-08.test.ing.host:server1');

            if ($objects['normalizer']->check()) {
                $objects['parser']->addTarget()->write();
            } else {
                $this->fail("The normalizer did not properly normalize the file!");
            }
        }
    }

    public function testDeleteTarget()
    {
        $file = 'iet.expected.testDeleteTarget.conf';

        foreach($this->dirs as $dir) {
            $dir = __DIR__ . DIRECTORY_SEPARATOR . $dir;

            $objects = $this->normalize($dir, 'iqn.2016-08.test.ing.host:delete');

            if ($objects['normalizer']->check()) {
                $objects['parser']->deleteTarget()->write();
            } else {
                $this->fail("The normalizer did not properly normalize the file!");
            }

            $this->assertFileEquals($dir . DIRECTORY_SEPARATOR . $file, $dir . DIRECTORY_SEPARATOR . $this->testFile);
        }
    }

    public function testDeleteTargetNotFoundException()
    {
        $this->expectException('MrCrankHank\IetParser\Exceptions\NotFoundException');

        foreach ($this->dirs as $dir) {
            $dir = __DIR__ . DIRECTORY_SEPARATOR . $dir;

            $objects = $this->normalize($dir, 'iqn.2016-08.test.ing.host:notFound');

            if ($objects['normalizer']->check()) {
                $objects['parser']->deleteTarget()->write();
            } else {
                $this->fail("The normalizer did not properly normalize the file!");

            }
        }
    }

    public function testDeleteTargetNotEmptyException()
    {
        $this->expectException('MrCrankHank\IetParser\Exceptions\TargetNotEmptyException');

        foreach ($this->dirs as $dir) {
            $dir = __DIR__ . DIRECTORY_SEPARATOR . $dir;

            $objects = $this->normalize($dir, 'iqn.2016-08.test.ing.host:server1');

            if ($objects['normalizer']->check()) {
                $objects['parser']->deleteTarget()->write();
            } else {
                $this->fail("The normalizer did not properly normalize the file!");
            }
        }
    }

    public function testAddOption()
    {
        $file = 'iet.expected.testAddOption.conf';

        foreach($this->dirs as $dir) {
            $dir = __DIR__ . DIRECTORY_SEPARATOR . $dir;

            $objects = $this->normalize($dir, 'iqn.2016-08.test.ing.host:server1');

            if ($objects['normalizer']->check()) {
                $objects['parser']->addOption('This is a awesome option')->write();
            } else {
                $this->fail("The normalizer did not properly normalize the file!");
            }

            $this->assertFileEquals($dir . DIRECTORY_SEPARATOR . $file, $dir . DIRECTORY_SEPARATOR . $this->testFile);
        }
    }

    public function testAddOptionUpdate()
    {
        $file = 'iet.expected.testAddOptionUpdate.conf';

        foreach($this->dirs as $dir) {
            $dir = __DIR__ . DIRECTORY_SEPARATOR . $dir;

            $objects = $this->normalize($dir, 'iqn.2016-08.test.ing.host:server17');

            if ($objects['normalizer']->check()) {
                $objects['parser']->addOption('ImmediateData No')->write();
            } else {
                $this->fail("The normalizer did not properly normalize the file!");
            }

            $this->assertFileEquals($dir . DIRECTORY_SEPARATOR . $file, $dir . DIRECTORY_SEPARATOR . $this->testFile);
        }
    }

    public function testAddOptionNotFoundException()
    {
        $this->expectException('MrCrankHank\IetParser\Exceptions\NotFoundException');

        foreach($this->dirs as $dir) {
            $dir = __DIR__ . DIRECTORY_SEPARATOR . $dir;

            $objects = $this->normalize($dir, 'iqn.2016-08.test.ing.host:notFound');

            if ($objects['normalizer']->check()) {
                $objects['parser']->addOption('This is a awesome option')->write();
            } else {
                $this->fail("The normalizer did not properly normalize the file!");
            }
        }
    }

    public function testDeleteOption()
    {
        $file = 'iet.expected.testDeleteOption.conf';

        foreach($this->dirs as $dir) {
            $dir = __DIR__ . DIRECTORY_SEPARATOR . $dir;

            $objects = $this->normalize($dir, 'iqn.2016-08.test.ing.host:ex');

            if ($objects['normalizer']->check()) {
                $objects['parser']->deleteOption('MaxBurstLength')->write();
            } else {
                $this->fail("The normalizer did not properly normalize the file!");
            }

            $this->assertFileEquals($dir . DIRECTORY_SEPARATOR . $file, $dir . DIRECTORY_SEPARATOR . $this->testFile);
        }
    }

    public function testGetOptions()
    {
        foreach($this->dirs as $dir) {
            $dir = __DIR__ . DIRECTORY_SEPARATOR . $dir;

            $objects = $this->normalize($dir, 'iqn.2016-08.test.ing.host:server1');

            if ($objects['normalizer']->check()) {
                $data = $objects['parser']->getOptions();

                $this->assertEquals(collect([
                    'IncomingUser user2 password2',
                    'ImmediateData Yes',
                    'Lun 0 Type=fileio,Path=/dev/VG_Datastore02/LV_server2',
                    'Lun 1 Type=blockio,Path=/dev/VG_Datastore03/LV_server2'
                ]), $data);
            } else {
                $this->fail("The normalizer did not properly normalize the file!");
            }
        }
    }

    public function testGetLunSingle()
    {
        foreach($this->dirs as $dir) {
            $dir = __DIR__ . DIRECTORY_SEPARATOR . $dir;

            $objects = $this->normalize($dir, 'iqn.2016-08.test.ing.host:server1');

            if ($objects['normalizer']->check()) {
                $data = $objects['parser']->getLun(1);

                $this->assertEquals(collect([
                    'id' => '1',
                    'type' => 'blockio',
                    'path' => '/dev/VG_Datastore03/LV_server2'
                ]), $data);
            } else {
                $this->fail("The normalizer did not properly normalize the file!");
            }
        }
    }

    public function testGetLun()
    {
        foreach($this->dirs as $dir) {
            $dir = __DIR__ . DIRECTORY_SEPARATOR . $dir;

            $objects = $this->normalize($dir, 'iqn.2016-08.test.ing.host:server18');

            if ($objects['normalizer']->check()) {
                $data = $objects['parser']->getLun();

                $this->assertEquals(collect([
                    0 => [
                        'id' => '0',
                        'type' => 'fileio',
                        'iomode' => 'wt',
                        'path' => '/dev/VG_Datastore01/LV_server43'
                    ],
                    1 => [
                        'id' => '1',
                        'type' => 'fileio',
                        'path' => '/dev/VG_Datastore02/LV_server18'
                    ]
                ]), $data);
            } else {
                $this->fail("The normalizer did not properly normalize the file!");
            }
        }
    }

    public function testAddLun()
    {
        $file = 'iet.expected.testAddLun.conf';

        foreach($this->dirs as $dir) {
            $dir = __DIR__ . DIRECTORY_SEPARATOR . $dir;

            $objects = $this->normalize($dir, 'iqn.2016-08.test.ing.host:server1');

            if ($objects['normalizer']->check()) {
                $objects['parser']->addLun('/dev/null', 'blockio')->write();


            } else {
                $this->fail("The normalizer did not properly normalize the file!");
            }

            $this->assertFileEquals($dir . DIRECTORY_SEPARATOR . $file, $dir . DIRECTORY_SEPARATOR . $this->testFile);
        }
    }

    public function testDeleteLun()
    {
        $file = 'iet.expected.testDeleteLun.conf';

        foreach($this->dirs as $dir) {
            $dir = __DIR__ . DIRECTORY_SEPARATOR . $dir;

            $objects = $this->normalize($dir, 'iqn.2016-08.test.ing.host:server1');

            if ($objects['normalizer']->check()) {
                $objects['parser']->deleteLun(0)->write();


            } else {
                $this->fail("The normalizer did not properly normalize the file!");
            }

            $this->assertFileEquals($dir . DIRECTORY_SEPARATOR . $file, $dir . DIRECTORY_SEPARATOR . $this->testFile);
        }
    }

    public function tearDown()
    {
        foreach ($this->dirs as $dir) {
            $dir = __DIR__ . DIRECTORY_SEPARATOR . $dir;

            $file = $dir . DIRECTORY_SEPARATOR . $this->testFile;

            if (file_exists($file)) {
                unlink($file);
            }
        }
    }

    private function normalize($dir, $target)
    {
        // Create new filesystem adapter
        $local = new Local($dir, LOCK_EX);

        // create new filesystem
        $filesystem = new Filesystem($local);

        // for testing purposes: copy the sample file. So we don't change the real data
        $filesystem->copy($this->sampleFile, $this->testFile);

        // create normalizer instance
        $normalizer = new Normalizer($filesystem, $this->testFile);

        // normalize the file
        $normalizer->write();

        // create parser instance
        $parser = new TargetParser($filesystem, $this->testFile, $target);

        return [
            'normalizer' => $normalizer,
            'parser' => $parser,
            'filesystem' => $filesystem
        ];
    }
}