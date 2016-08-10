<?php

namespace MrCrankHank\IetParser\TargetParser;

use MrCrankHank\IetParser\TestTrait;
use PHPUnit_Framework_TestCase;
use MrCrankHank\IetParser\Parser\TargetParser;

class TargetParserDelete extends PHPUnit_Framework_TestCase
{
    use TestTrait;

    public static function addTargetProvider()
    {
        return [
            ['case1_files', 'iet.sample.conf', 'iet.expected.testAddTarget.conf'],
            ['case2_files', 'iet.sample.conf', 'iet.expected.testAddTarget.conf'],
        ];
    }

    /**
     * @param $dir
     * @param $sourceFile
     * @param $expectedFile
     *
     * @dataProvider addTargetProvider
     */
    public function testAddTarget($dir, $sourceFile, $expectedFile)
    {
            $dir = __DIR__ . DIRECTORY_SEPARATOR . $dir;

            $objects = $this->normalize($dir, $sourceFile);

            $parser = new TargetParser($objects['filesystem'], self::$testFile, 'iqn.2016-08.test.ing.host:testAdd');

            if ($objects['normalizer']->check()) {
                $parser->addTarget()->write();
            } else {
                $this->fail("The normalizer did not properly normalize the file!");
            }

            $this->assertFileEquals($dir . DIRECTORY_SEPARATOR . $expectedFile, $dir . DIRECTORY_SEPARATOR . self::$testFile);
    }

    public static function addTargetDuplicationErrorProvider()
    {
        return [
            ['case1_files', 'iet.sample.conf'],
            ['case2_files', 'iet.sample.conf'],
        ];
    }

    /**
     * @param $dir
     * @param $sourceFile
     *
     * @dataProvider addTargetDuplicationErrorProvider
     */
    public function testAddTargetDuplicationError($dir, $sourceFile)
    {
        $this->expectException('MrCrankHank\IetParser\Exceptions\DuplicationErrorException');

        $dir = __DIR__ . DIRECTORY_SEPARATOR . $dir;

        $objects = $this->normalize($dir, $sourceFile);

        $parser = new TargetParser($objects['filesystem'], self::$testFile, 'iqn.2016-08.test.ing.host:server1');

        if ($objects['normalizer']->check()) {
            $parser->addTarget()->write();
        } else {
            $this->fail("The normalizer did not properly normalize the file!");
        }
    }

    public static function deleteTargetProvider()
    {
        return [
            ['case1_files', 'iet.sample.conf', 'iet.expected.testDeleteTarget.conf'],
            ['case2_files', 'iet.sample.conf', 'iet.expected.testDeleteTarget.conf'],
        ];
    }

    /**
     * @param $dir
     * @param $sourceFile
     * @param $expectedFile
     *
     * @dataProvider deleteTargetProvider
     */
    public function testDeleteTarget($dir, $sourceFile, $expectedFile)
    {
        $dir = __DIR__ . DIRECTORY_SEPARATOR . $dir;

        $objects = $this->normalize($dir, $sourceFile);

        $parser = new TargetParser($objects['filesystem'], self::$testFile, 'iqn.2016-08.test.ing.host:delete');

        if ($objects['normalizer']->check()) {
            $parser->deleteTarget()->write();
        } else {
            $this->fail("The normalizer did not properly normalize the file!");
        }

        $this->assertFileEquals($dir . DIRECTORY_SEPARATOR . $expectedFile, $dir . DIRECTORY_SEPARATOR . self::$testFile);
    }

    public static function deleteTargetNotFoundExceptionProvider()
    {
        return [
            ['case1_files', 'iet.sample.conf'],
            ['case2_files', 'iet.sample.conf'],
        ];
    }

    /**
     * @param $dir
     * @param $sourceFile
     *
     * @dataProvider deleteTargetNotFoundExceptionProvider
     */
    public function testDeleteTargetNotFoundException($dir, $sourceFile)
    {
        $this->expectException('MrCrankHank\IetParser\Exceptions\NotFoundException');

        $dir = __DIR__ . DIRECTORY_SEPARATOR . $dir;

        $objects = $this->normalize($dir, $sourceFile);

        $parser = new TargetParser($objects['filesystem'], self::$testFile, 'iqn.2016-08.test.ing.host:notFound');

        if ($objects['normalizer']->check()) {
            $parser->deleteTarget()->write();
        } else {
            $this->fail("The normalizer did not properly normalize the file!");
        }
    }

    public static function deleteTargetNotEmptyExceptionProvider()
    {
        return [
            ['case1_files', 'iet.sample.conf'],
            ['case2_files', 'iet.sample.conf'],
        ];
    }

    /**
     * @param $dir
     * @param $sourceFile
     *
     * @dataProvider deleteTargetNotEmptyExceptionProvider
     */
    public function testDeleteTargetNotEmptyException($dir, $sourceFile)
    {
        $this->expectException('MrCrankHank\IetParser\Exceptions\TargetNotEmptyException');

        $dir = __DIR__ . DIRECTORY_SEPARATOR . $dir;

        $objects = $this->normalize($dir, $sourceFile);

        $parser = new TargetParser($objects['filesystem'], self::$testFile, 'iqn.2016-08.test.ing.host:server1');

        if ($objects['normalizer']->check()) {
            $parser->deleteTarget()->write();
        } else {
            $this->fail("The normalizer did not properly normalize the file!");
        }
    }

    public static function addOptionProvider()
    {
        return [
            ['case1_files', 'iet.sample.conf', 'iet.expected.testAddOption.conf'],
            ['case2_files', 'iet.sample.conf', 'iet.expected.testAddOption.conf'],
        ];
    }

    /**
     * @param $dir
     * @param $sourceFile
     * @param $expectedFile
     *
     * @dataProvider addOptionProvider
     */
    public function testAddOption($dir, $sourceFile, $expectedFile)
    {
        $dir = __DIR__ . DIRECTORY_SEPARATOR . $dir;

        $objects = $this->normalize($dir, $sourceFile);

        $parser = new TargetParser($objects['filesystem'], self::$testFile, 'iqn.2016-08.test.ing.host:server1');

        if ($objects['normalizer']->check()) {
            $parser->addOption('This is a awesome option')->write();
        } else {
            $this->fail("The normalizer did not properly normalize the file!");
        }

        $this->assertFileEquals($dir . DIRECTORY_SEPARATOR . $expectedFile, $dir . DIRECTORY_SEPARATOR . self::$testFile);
    }

    public static function addOptionUpdateProvider()
    {
        return [
            ['case1_files', 'iet.sample.conf', 'iet.expected.testAddOptionUpdate.conf'],
            ['case2_files', 'iet.sample.conf', 'iet.expected.testAddOptionUpdate.conf'],
        ];
    }

    /**
     * @param $dir
     * @param $sourceFile
     * @param $expectedFile
     *
     * @dataProvider addOptionUpdateProvider
     */
    public function testAddOptionUpdate($dir, $sourceFile, $expectedFile)
    {
        $dir = __DIR__ . DIRECTORY_SEPARATOR . $dir;

        $objects = $this->normalize($dir, $sourceFile);

        $parser = new TargetParser($objects['filesystem'], self::$testFile, 'iqn.2016-08.test.ing.host:server17');

        if ($objects['normalizer']->check()) {
            $parser->addOption('ImmediateData No')->write();
        } else {
            $this->fail("The normalizer did not properly normalize the file!");
        }

        $this->assertFileEquals($dir . DIRECTORY_SEPARATOR . $expectedFile, $dir . DIRECTORY_SEPARATOR . self::$testFile);
    }

    public static function addOptionNotFoundExceptionProvider()
    {
        return [
            ['case1_files', 'iet.sample.conf'],
            ['case2_files', 'iet.sample.conf'],
        ];
    }

    /**
     * @param $dir
     * @param $sourceFile
     *
     * @dataProvider addOptionNotFoundExceptionProvider
     */
    public function testAddOptionNotFoundException($dir, $sourceFile)
    {
        $this->expectException('MrCrankHank\IetParser\Exceptions\NotFoundException');

        $dir = __DIR__ . DIRECTORY_SEPARATOR . $dir;

        $objects = $this->normalize($dir, $sourceFile);

        $parser = new TargetParser($objects['filesystem'], self::$testFile, 'iqn.2016-08.test.ing.host:notFound');

        if ($objects['normalizer']->check()) {
            $parser->addOption('This is a awesome option')->write();
        } else {
            $this->fail("The normalizer did not properly normalize the file!");
        }
    }

    public static function deleteOptionProvider()
    {
        return [
            ['case1_files', 'iet.sample.conf', 'iet.expected.testDeleteOption.conf'],
            ['case2_files', 'iet.sample.conf', 'iet.expected.testDeleteOption.conf'],
        ];
    }

    /**
     * @param $dir
     * @param $sourceFile
     * @param $expectedFile
     *
     * @dataProvider deleteOptionProvider
     */
    public function testDeleteOption($dir, $sourceFile, $expectedFile)
    {
        $dir = __DIR__ . DIRECTORY_SEPARATOR . $dir;

        $objects = $this->normalize($dir, $sourceFile);

        $parser = new TargetParser($objects['filesystem'], self::$testFile, 'iqn.2016-08.test.ing.host:ex');

        if ($objects['normalizer']->check()) {
            $parser->deleteOption('MaxBurstLength')->write();
        } else {
            $this->fail("The normalizer did not properly normalize the file!");
        }

        $this->assertFileEquals($dir . DIRECTORY_SEPARATOR . $expectedFile, $dir . DIRECTORY_SEPARATOR . self::$testFile);
    }

    public static function getOptionsProvider()
    {
        return [
            ['case1_files', 'iet.sample.conf',
                [
                    'IncomingUser user2 password2',
                    'ImmediateData Yes',
                    'Lun 0 Type=fileio,Path=/dev/VG_Datastore02/LV_server2',
                    'Lun 1 Type=blockio,Path=/dev/VG_Datastore03/LV_server2'
                ]
            ],
            ['case2_files', 'iet.sample.conf',
                [
                    'ImmediateData Yes',
                    'Lun 0 Type=fileio,Path=/dev/VG_Datastore02/LV_server2',
                ]
            ],
        ];
    }

    /**
     * @param $dir
     * @param $sourceFile
     * @param $expectedData
     *
     * @dataProvider getOptionsProvider
     */
    public function testGetOptions($dir, $sourceFile, $expectedData)
    {
        $dir = __DIR__ . DIRECTORY_SEPARATOR . $dir;

        $objects = $this->normalize($dir, $sourceFile);

        $parser = new TargetParser($objects['filesystem'], self::$testFile, 'iqn.2016-08.test.ing.host:server1');

        if ($objects['normalizer']->check()) {
            $data = $parser->getOptions();

            $this->assertEquals(collect($expectedData), $data);
        } else {
            $this->fail("The normalizer did not properly normalize the file!");
        }
    }

    public static function getLunSingleProvider()
    {
        return [
            ['case1_files', 'iet.sample.conf',
                [
                    'id' => '1',
                    'type' => 'blockio',
                    'path' => '/dev/VG_Datastore03/LV_server2'
                ]
            ],
            ['case2_files', 'iet.sample.conf',
                [
                    'id' => '0',
                    'type' => 'fileio',
                    'path' => '/dev/VG_Datastore02/LV_server2'
                ]
            ]
        ];
    }

    /**
     * @param $dir
     * @param $sourceFile
     * @param $expectedData
     *
     * @dataProvider getLunSingleProvider
     */
    public function testGetLunSingle($dir, $sourceFile, $expectedData)
    {
        $dir = __DIR__ . DIRECTORY_SEPARATOR . $dir;

        $objects = $this->normalize($dir, $sourceFile);

        $parser = new TargetParser($objects['filesystem'], self::$testFile, 'iqn.2016-08.test.ing.host:server1');

        if ($objects['normalizer']->check()) {
            $data = $parser->getLun($expectedData['id']);

            $this->assertEquals(collect($expectedData), $data);
        } else {
            $this->fail("The normalizer did not properly normalize the file!");
        }
    }

    public static function getLunProvider()
    {
        return [
            ['case1_files', 'iet.sample.conf', [
                [
                    'id' => '0',
                    'type' => 'fileio',
                    'iomode' => 'wt',
                    'path' => '/dev/VG_Datastore01/LV_server43'
                ],
                [
                    'id' => '1',
                    'type' => 'fileio',
                    'path' => '/dev/VG_Datastore02/LV_server18'
                ]
            ]
            ],
            ['case2_files', 'iet.sample.conf', [
                [
                    'id' => '0',
                    'type' => 'fileio',
                    'iomode' => 'wt',
                    'path' => '/dev/VG_Datastore01/LV_server43'
                ],
                [
                    'id' => '1',
                    'type' => 'fileio',
                    'path' => '/dev/VG_Datastore02/LV_server18'
                ]
            ]
            ]
        ];
    }

    /**
     * @param $dir
     * @param $sourceFile
     * @param $expectedData
     *
     * @dataProvider getLunProvider
     */
    public function testGetLun($dir, $sourceFile, $expectedData)
    {
        $dir = __DIR__ . DIRECTORY_SEPARATOR . $dir;

        $objects = $this->normalize($dir, $sourceFile);

        $parser = new TargetParser($objects['filesystem'], self::$testFile, 'iqn.2016-08.test.ing.host:server18');

        if ($objects['normalizer']->check()) {
            $data = $parser->getLun();

            $this->assertEquals(collect($expectedData), $data);
        } else {
            $this->fail("The normalizer did not properly normalize the file!");
        }
    }

    public static function addLunProvider()
    {
        return [
            ['case1_files', 'iet.sample.conf', 'iet.expected.testAddLun.conf'],
            ['case2_files', 'iet.sample.conf', 'iet.expected.testAddLun.conf'],
        ];
    }

    /**
     * @param $dir
     * @param $sourceFile
     * @param $expectedData
     *
     * @dataProvider addLunProvider
     */
    public function testAddLun($dir, $sourceFile, $expectedData)
    {
        $dir = __DIR__ . DIRECTORY_SEPARATOR . $dir;

        $objects = $this->normalize($dir, $sourceFile);

        $parser = new TargetParser($objects['filesystem'], self::$testFile, 'iqn.2016-08.test.ing.host:server1');

        if ($objects['normalizer']->check()) {
            $parser->addLun('/dev/null', 'blockio')->write();
        } else {
            $this->fail("The normalizer did not properly normalize the file!");
        }

        $this->assertFileEquals($dir . DIRECTORY_SEPARATOR . $expectedData, $dir . DIRECTORY_SEPARATOR . self::$testFile);
    }

    public static function deleteLunProvider()
    {
        return [
            ['case1_files', 'iet.sample.conf', 'iet.expected.testDeleteLun.conf'],
            ['case2_files', 'iet.sample.conf', 'iet.expected.testDeleteLun.conf'],
        ];
    }

    /**
     * @param $dir
     * @param $sourceFile
     * @param $expectedData
     *
     * @dataProvider deleteLunProvider
     */
    public function testDeleteLun($dir, $sourceFile, $expectedData)
    {
        $dir = __DIR__ . DIRECTORY_SEPARATOR . $dir;

        $objects = $this->normalize($dir, $sourceFile);

        $parser = new TargetParser($objects['filesystem'], self::$testFile, 'iqn.2016-08.test.ing.host:server1');

        if ($objects['normalizer']->check()) {
            $parser->deleteLun(0)->write();


        } else {
            $this->fail("The normalizer did not properly normalize the file!");
        }

        $this->assertFileEquals($dir . DIRECTORY_SEPARATOR . $expectedData, $dir . DIRECTORY_SEPARATOR . self::$testFile);
    }
}