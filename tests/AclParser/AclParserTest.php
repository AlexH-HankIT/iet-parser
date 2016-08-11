<?php

namespace MrCrankHank\IetParser\AclParser;

use MrCrankHank\IetParser\Parser\AclParser;
use MrCrankHank\IetParser\TestTrait;
use PHPUnit_Framework_TestCase;

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
            ['sample/initiators.sample.testAdd.1.allow', 'expected/initiators.expected.testAdd.1.allow', 'iqn.2001-04.com.example:storage.disk1.sys5.xyz', '95.123.123.43'],
            ['sample/initiators.sample.testAdd.1.allow', 'expected/initiators.expected.testAdd.2.allow', 'iqn.2001-04.com.example:storage.disk1.sys8.xyz', '8.8.8.8'],
            ['sample/initiators.sample.testAdd.1.allow', 'expected/initiators.expected.testAdd.3.allow', 'iqn.2001-04.com.example:storage.disk1.sys4.xyz', '[3ffe:302:11:1:211:43ff:fe31:5ae2]'],
            ['sample/initiators.sample.testAdd.1.allow', 'expected/initiators.expected.testAdd.4.allow', 'iqn.2001-04.com.example:storage.disk1.sys10.xyz', '[fe80::f939:2dfe:5469:1bc6]'],
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

        $parser = new AclParser($objects['filesystem'], self::$testFile, $iqn);

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
            ['sample/initiators.sample.testAdd.1.allow', 'iqn.2001-04.com.example:storage.disk1.sys5.xyz', '95.123.123.43']
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

        $parser = new AclParser($objects['filesystem'], self::$testFile, $iqn);

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
            ['sample/initiators.sample.testAdd.1.allow', 'expected/initiators.expected.testDelete.1.allow', 'iqn.2001-04.com.example:storage.disk1.sys4.xyz', 'iqn\.1998-01\.com\.vmware:.*\.example\.com'],
            ['sample/initiators.sample.testAdd.1.allow', 'expected/initiators.expected.testDelete.2.allow', 'iqn.2001-04.com.example:storage.disk1.sys3.xyz', 'ALL'],
            ['sample/initiators.sample.testAdd.1.allow', 'expected/initiators.expected.testDelete.3.allow', 'iqn.2001-04.com.example:storage.disk1.sys7.xyz', '192.168.100.53'],
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

        $parser = new AclParser($objects['filesystem'], self::$testFile, $iqn);

        if ($objects['normalizer']->check()) {
            $parser->delete($acl)->write();
        } else {
            $this->fail("The normalizer did not properly normalize the file!");
        }

        $this->assertFileEquals($dir . DIRECTORY_SEPARATOR . $expectedFile, $dir . DIRECTORY_SEPARATOR . self::$testFile);
    }

    public function testDeleteParserErrorException()
    {

    }

    public function testDeleteNotFoundException()
    {

    }

    public function testGet()
    {

    }

    public function testGetParserErrorException()
    {

    }
}