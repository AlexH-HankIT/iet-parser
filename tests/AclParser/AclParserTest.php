<?php

namespace MrCrankHank\IetParser\AclParser;

use PHPUnit_Framework_TestCase;
use MrCrankHank\IetParser\TestTrait;
use MrCrankHank\IetParser\Parser\AclParser;
use MrCrankHank\IetParser\File;

/**
 * Class AclParserTest
 * @package MrCrankHank\IetParser\tests
 */
class AclParserTest extends PHPUnit_Framework_TestCase
{
    use TestTrait;

    public static function addProvider()
    {
        return [
            ['sample/initiators.sample.1.allow', 'expected/initiators.expected.testAdd.1.allow', 'iqn.2001-04.com.example:storage.disk1.sys5.xyz', '95.123.123.43'],
            ['sample/initiators.sample.1.allow', 'expected/initiators.expected.testAdd.2.allow', 'iqn.2001-04.com.example:storage.disk1.sys8.xyz', '8.8.8.8'],
            ['sample/initiators.sample.1.allow', 'expected/initiators.expected.testAdd.3.allow', 'iqn.2001-04.com.example:storage.disk1.sys4.xyz', '[3ffe:302:11:1:211:43ff:fe31:5ae2]'],
            ['sample/initiators.sample.1.allow', 'expected/initiators.expected.testAdd.4.allow', 'iqn.2001-04.com.example:storage.disk1.sys10.xyz', '[fe80::f939:2dfe:5469:1bc6]'],
        ];
    }

    /**
     * @param $sourceFile
     * @param $expectedFile
     * @param $iqn
     * @param $acl
     *
     * @dataProvider addProvider
     */
    public function testAdd($sourceFile, $expectedFile, $iqn, $acl)
    {
        $dir = __DIR__ . DIRECTORY_SEPARATOR . 'files';

        $objects = $this->normalize($dir, $sourceFile);

        $file = (new File)->readContent($objects['filesystem'], self::$testFile);

        $parser = new AclParser($file, $iqn);

        if ($objects['normalizer']->check()) {
            $parser->add($acl)->write();
        } else {
            $this->fail("The normalizer did not properly normalize the file!");
        }

        $this->assertFileEquals($dir . DIRECTORY_SEPARATOR . $expectedFile, $dir . DIRECTORY_SEPARATOR . self::$testFile);
    }

    public static function addDuplicationErrorExceptionProvider()
    {
        return [
            ['sample/initiators.sample.1.allow', 'iqn.2001-04.com.example:storage.disk1.sys5.xyz', '95.123.123.43']
        ];
    }

    /**
     * @param $sourceFile
     * @param $iqn
     * @param $acl
     *
     * @dataProvider addDuplicationErrorExceptionProvider
     */
    public function testAddDuplicationErrorException($sourceFile, $iqn, $acl)
    {
        $this->expectException('MrCrankHank\IetParser\Exceptions\DuplicationErrorException');

        $dir = __DIR__ . DIRECTORY_SEPARATOR . 'files';

        $objects = $this->normalize($dir, $sourceFile);

        $file = (new File)->readContent($objects['filesystem'], self::$testFile);

        $parser = new AclParser($file, $iqn);

        if ($objects['normalizer']->check()) {
            $parser->add($acl)->write();
            $parser->add($acl)->write();
        } else {
            $this->fail("The normalizer did not properly normalize the file!");
        }
    }

    public static function deleteProvider()
    {
        return [
            ['sample/initiators.sample.1.allow', 'expected/initiators.expected.testDelete.1.allow', 'iqn.2001-04.com.example:storage.disk1.sys4.xyz', 'iqn\.1998-01\.com\.vmware:.*\.example\.com'],
            ['sample/initiators.sample.1.allow', 'expected/initiators.expected.testDelete.2.allow', 'iqn.2001-04.com.example:storage.disk1.sys3.xyz', 'ALL'],
            ['sample/initiators.sample.1.allow', 'expected/initiators.expected.testDelete.3.allow', 'iqn.2001-04.com.example:storage.disk1.sys7.xyz', '192.168.100.53'],
        ];
    }

    /**
     * @param $sourceFile
     * @param $iqn
     * @param $acl
     *
     * @dataProvider deleteProvider
     */
    public function testDelete($sourceFile, $expectedFile, $iqn, $acl)
    {
        $dir = __DIR__ . DIRECTORY_SEPARATOR . 'files';

        $objects = $this->normalize($dir, $sourceFile);

        $file = (new File)->readContent($objects['filesystem'], self::$testFile);

        $parser = new AclParser($file, $iqn);

        if ($objects['normalizer']->check()) {
            $parser->delete($acl)->write();
        } else {
            $this->fail("The normalizer did not properly normalize the file!");
        }

        $this->assertFileEquals($dir . DIRECTORY_SEPARATOR . $expectedFile, $dir . DIRECTORY_SEPARATOR . self::$testFile);
    }

    public static function deleteParserErrorExceptionProvider()
    {
        return [
            ['sample/initiators.sample.2.allow', 'iqn.2001-04.com.example:storage.disk1.sys4.xyz', '8.8.8.8']
        ];
    }

    /**
     * @param $sourceFile
     * @param $iqn
     * @param $acl
     *
     * @dataProvider deleteParserErrorExceptionProvider
     */
    public function testDeleteParserErrorException($sourceFile, $iqn, $acl)
    {
        $this->expectException('MrCrankHank\IetParser\Exceptions\ParserErrorException');

        $dir = __DIR__ . DIRECTORY_SEPARATOR . 'files';

        $objects = $this->normalize($dir, $sourceFile);

        $file = (new File)->readContent($objects['filesystem'], self::$testFile);

        $parser = new AclParser($file, $iqn);

        if ($objects['normalizer']->check()) {
            $parser->delete($acl)->write();
        } else {
            $this->fail("The normalizer did not properly normalize the file!");
        }
    }

    public static function deleteNotFoundExceptionProvider()
    {
        return [
            ['sample/initiators.sample.1.allow', 'iqn.2001-04.com.example:storage.disk1.sys4.xyz.notFound', '8.8.8.8'],
            ['sample/initiators.sample.1.allow', 'iqn.2001-04.com.example:storage.disk1.sys5.xyz', '8.8.8.8'],
        ];
    }

    /**
     * @param $sourceFile
     * @param $iqn
     * @param $acl
     *
     * @dataProvider deleteNotFoundExceptionProvider
     */
    public function testDeleteNotFoundException($sourceFile, $iqn, $acl)
    {
        $this->expectException('MrCrankHank\IetParser\Exceptions\NotFoundException');

        $dir = __DIR__ . DIRECTORY_SEPARATOR . 'files';

        $objects = $this->normalize($dir, $sourceFile);

        $file = (new File)->readContent($objects['filesystem'], self::$testFile);

        $parser = new AclParser($file, $iqn);

        if ($objects['normalizer']->check()) {
            $parser->delete($acl)->write();
        } else {
            $this->fail("The normalizer did not properly normalize the file!");
        }
    }

    public static function getProvider()
    {
        return [
            ['sample/initiators.sample.1.allow', 'iqn.2001-04.com.example:storage.disk1.sys5.xyz', false,
                [
                    0 => '192.168.100.61'
                ]
            ],
            ['sample/initiators.sample.1.allow', 'iqn.2001-04.com.example:storage.disk1.sys2.xyz', false,
                [
                    0 => '[3ffe:302:11:1:211:43ff:fe31:5ae2]',
                    1 => '[3ffe:505:2:1::]/64',
                    2 => '192.168.22.0/24'
                ]
            ],
            ['sample/initiators.sample.1.allow', null, true,
                [
                    'iqn.2001-04.com.example:storage.disk1.sys1.xyz' => collect([
                        0 => '192.168.0.0/16',
                        1 => '.*:mscs1-[1-4]\.example\.com'
                    ]),
                    'iqn.2001-04.com.example:storage.disk1.sys2.xyz' => collect([
                        0 => '[3ffe:302:11:1:211:43ff:fe31:5ae2]',
                        1 => '[3ffe:505:2:1::]/64',
                        2 => '192.168.22.0/24'
                    ]),
                    'iqn.2001-04.com.example:storage.disk1.sys3.xyz' => collect([
                        0 => 'ALL'
                    ]),
                    'iqn.2001-04.com.example:storage.disk1.sys4.xyz' => collect([
                        0 => '192.168.22.3',
                        1 => 'iqn\.1998-01\.com\.vmware:.*\.example\.com'
                    ]),
                    'ALL' => collect([
                        0 => '192.168.0.0/16'
                    ]),
                    'iqn.2001-04.com.example:storage.disk1.sys5.xyz' => collect([
                        0 => '192.168.100.61'
                    ]),
                    'iqn.2001-04.com.example:storage.disk1.sys6.xyz' => collect([
                        0 => '192.168.100.58'
                    ]),
                    'iqn.2001-04.com.example:storage.disk1.sys7.xyz' => collect([
                        0 => '192.168.100.53'
                    ])
                ]
            ],
        ];
    }


    /**
     * @param $sourceFile
     * @param $iqn
     * @param $all
     * @param $expectedData
     *
     * @dataProvider getProvider
     */
    public function testGet($sourceFile, $iqn, $all, $expectedData)
    {
        $dir = __DIR__ . DIRECTORY_SEPARATOR . 'files';

        $objects = $this->normalize($dir, $sourceFile);

        $file = (new File)->readContent($objects['filesystem'], self::$testFile);

        $parser = new AclParser($file, $iqn);

        if ($objects['normalizer']->check()) {
            $data = $parser->get($all);
        } else {
            $this->fail("The normalizer did not properly normalize the file!");
        }

        $this->assertEquals(collect($expectedData), $data);
    }

    public static function getParserErrorExceptionProvider()
    {
        return [
            ['sample/initiators.sample.3.allow', 'iqn.2001-04.com.example:storage.disk1.sys3.xyz', false],
        ];
    }

    /**
     * @param $sourceFile
     * @param $iqn
     * @param $all
     *
     * @dataProvider getParserErrorExceptionProvider
     */
    public function testGetParserErrorException($sourceFile, $iqn, $all)
    {
        $this->expectException('MrCrankHank\IetParser\Exceptions\ParserErrorException');

        $dir = __DIR__ . DIRECTORY_SEPARATOR . 'files';

        $objects = $this->normalize($dir, $sourceFile);

        $file = (new File)->readContent($objects['filesystem'], self::$testFile);

        $parser = new AclParser($file, $iqn);

        if ($objects['normalizer']->check()) {
            $parser->get($all);
        } else {
            $this->fail("The normalizer did not properly normalize the file!");
        }
    }
}