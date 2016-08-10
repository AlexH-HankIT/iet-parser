<?php

namespace MrCrankHank\IetParser\OptionParserAdd;

use MrCrankHank\IetParser\Exceptions\DuplicationErrorException;
use MrCrankHank\IetParser\Parser\GlobalOptionParser;
use PHPUnit_Framework_TestCase;
use MrCrankHank\IetParser\TestTrait;

/**
 * Class GlobalOptionParserTestAdd
 * @package MrCrankHank\IetParser\tests
 */
class GlobalOptionParserTestAdd extends PHPUnit_Framework_TestCase {
    use TestTrait;

    public static function addProvider()
    {
        return [
            ['case1_files', 'iet.sample.conf', 'iet.expected.testAdd.conf'],
            ['case2_files', 'iet.sample.conf', 'iet.expected.testAdd.conf']
        ];
    }

    /**
     * Test if I can add a parameter to the file
     *
     * @param $dir
     * @param $sourceFile
     * @param $expectedFile
     *
     * @dataProvider addProvider
     */
    public function testAdd($dir, $sourceFile, $expectedFile)
    {
        $dir = __DIR__ . DIRECTORY_SEPARATOR . $dir;

        $objects = $this->normalize($dir, $sourceFile);

        $parser = new GlobalOptionParser($objects['filesystem'], self::$testFile);

        if ($objects['normalizer']->check()) {
            $parser->add("IncomingUser user password")->write();
        } else {
            $this->fail("The normalizer did not properly normalize the file!");
        }

        $this->assertFileEquals($dir . DIRECTORY_SEPARATOR . $expectedFile, $dir . DIRECTORY_SEPARATOR . self::$testFile);
    }

    public static function duplicationErrorProvider()
    {
        return [
            ['case1_files', 'iet.sample.conf'],
            ['case2_files', 'iet.sample.conf']
        ];
    }

    /**
     * Test if the duplication check is working
     *
     * @param $dir
     * @param $sourceFile
     *
     * @dataProvider duplicationErrorProvider
     */
    public function testDuplicationError($dir, $sourceFile)
    {
        $dir = __DIR__ . DIRECTORY_SEPARATOR . $dir;

        $objects = $this->normalize($dir, $sourceFile);

        $parser = new GlobalOptionParser($objects['filesystem'], self::$testFile);

        if ($objects['normalizer']->check()) {
            try {
                $parser->add("IncomingUser user password")->write();
                $parser->add("IncomingUser user password")->write();
                $this->fail("Test did not throw DuplicationError exception!");
            } catch (DuplicationErrorException $e) {
                $this->assertEquals($e->getMessage(), 'The option IncomingUser user password is already set.');
            }
        } else {
            $this->fail("The normalizer did not properly normalize the file!");
        }
    }

    public static function addOutgoingUserHelperMethodProvider()
    {
        return [
            ['case1_files', 'iet.sample.conf', 'iet.expected.testAddOutgoingUserHelperMethod.conf'],
            ['case2_files', 'iet.sample.conf', 'iet.expected.testAddOutgoingUserHelperMethod.conf'],
        ];
    }

    /**
     * Test the addOutgoingUser() helper method
     *
     * @param $dir
     * @param $sourceFile
     * @param $expectedFile
     *
     * @dataProvider addOutgoingUserHelperMethodProvider
     */
    public function testAddOutgoingUserHelperMethod($dir, $sourceFile, $expectedFile)
    {
        $dir = __DIR__ . DIRECTORY_SEPARATOR . $dir;

        $objects = $this->normalize($dir, $sourceFile);

        $parser = new GlobalOptionParser($objects['filesystem'], self::$testFile);

        if ($objects['normalizer']->check()) {
            try {
                $parser->addOutgoingUser("user", "password")->write();
            } catch (DuplicationErrorException $e) {
                $this->assertEquals($e->getMessage(), 'The option OutgoingUser user password is already set.');
            }
        } else {
            $this->fail("The normalizer did not properly normalize the file!");
        }

        $this->assertFileEquals($dir . DIRECTORY_SEPARATOR . $expectedFile, $dir . DIRECTORY_SEPARATOR . self::$testFile);
    }

    public static function addIncomingUserHelperMethodProvider()
    {
        return [
            ['case1_files', 'iet.sample.conf', 'iet.expected.testAddIncomingUserHelperMethod.conf'],
            ['case2_files', 'iet.sample.conf', 'iet.expected.testAddIncomingUserHelperMethod.conf'],
        ];
    }

    /**
     * Test the addIncomingUser() helper method
     *
     * @param $dir
     * @param $sourceFile
     * @param $expectedFile
     *
     * @dataProvider addIncomingUserHelperMethodProvider
     */
    public function testAddIncomingUserHelperMethod($dir, $sourceFile, $expectedFile)
    {
        $dir = __DIR__ . DIRECTORY_SEPARATOR . $dir;

        $objects = $this->normalize($dir, $sourceFile);

        $parser = new GlobalOptionParser($objects['filesystem'], self::$testFile);

        if ($objects['normalizer']->check()) {
            try {
                $parser->addIncomingUser("user", "password")->write();
            } catch (DuplicationErrorException $e) {
                $this->assertEquals($e->getMessage(), 'The option IncomingUser user password is already set.');
            }
        } else {
            $this->fail("The normalizer did not properly normalize the file!");
        }

        $this->assertFileEquals($dir . DIRECTORY_SEPARATOR . $expectedFile, $dir . DIRECTORY_SEPARATOR . self::$testFile);
    }
}