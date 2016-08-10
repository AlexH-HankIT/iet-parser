<?php

namespace MrCrankHank\IetParser\OptionParserDelete;

use MrCrankHank\IetParser\TestTrait;
use PHPUnit_Framework_TestCase;
use MrCrankHank\IetParser\Parser\GlobalOptionParser;

class GlobalOptionParserDelete extends PHPUnit_Framework_TestCase {
    use TestTrait;

    public static function deleteProvider()
    {
        return [
            ['case1_files', 'iet.sample.conf', 'iet.expected.testDelete.conf'],
            ['case2_files', 'iet.sample.conf', 'iet.expected.testDelete.conf']
        ];
    }

    /**
     * @param $dir
     * @param $sourceFile
     * @param $expectedFile
     *
     * @dataProvider deleteProvider
     */
    public function testDelete($dir, $sourceFile, $expectedFile)
    {
        $dir = __DIR__ . DIRECTORY_SEPARATOR . $dir;

        $objects = $this->normalize($dir, $sourceFile);

        $parser = new GlobalOptionParser($objects['filesystem'], self::$testFile);

        if ($objects['normalizer']->check()) {
            $parser->delete('IncomingUser user2 password2')->write();
        } else {
            $this->fail("The normalizer did not properly normalize the file!");
        }

        $this->assertFileEquals($dir . DIRECTORY_SEPARATOR . $expectedFile, $dir . DIRECTORY_SEPARATOR . self::$testFile);
    }

    public static function notFoundErrorProvider()
    {
        return [
            ['case1_files', 'iet.sample.conf'],
            ['case2_files', 'iet.sample.conf']
        ];
    }

    /**
     * @param $dir
     * @param $sourceFile
     *
     * @dataProvider notFoundErrorProvider
     */
    public function testNotFoundError($dir, $sourceFile)
    {
        $this->expectException('MrCrankHank\IetParser\Exceptions\NotFoundException');

        $dir = __DIR__ . DIRECTORY_SEPARATOR . $dir;

        $objects = $this->normalize($dir, $sourceFile);

        $parser = new GlobalOptionParser($objects['filesystem'], self::$testFile);

        if ($objects['normalizer']->check()) {
            $parser->delete('This wont be found')->write();
        } else {
            $this->fail("The normalizer did not properly normalize the file!");
        }
    }

    public static function deleteIncomingUserHelperMethodProvider()
    {
        return [
            ['case1_files', 'iet.sample.conf', 'iet.expected.testDeleteIncomingUserHelperMethod.conf'],
            ['case2_files', 'iet.sample.conf', 'iet.expected.testDeleteIncomingUserHelperMethod.conf'],
        ];
    }

    /**
     * @param $dir
     * @param $sourceFile
     * @param $expectedFile
     *
     * @dataProvider deleteIncomingUserHelperMethodProvider
     */
    public function testDeleteIncomingUserHelperMethod($dir, $sourceFile, $expectedFile)
    {
        $dir = __DIR__ . DIRECTORY_SEPARATOR . $dir;

        $objects = $this->normalize($dir, $sourceFile);

        $parser = new GlobalOptionParser($objects['filesystem'], self::$testFile);

        if ($objects['normalizer']->check()) {
            $parser->deleteIncomingUser('user2', 'password2')->write();
        } else {
            $this->fail("The normalizer did not properly normalize the file!");
        }

        $this->assertFileEquals($dir . DIRECTORY_SEPARATOR . $expectedFile, $dir . DIRECTORY_SEPARATOR . self::$testFile);
    }

    public static function deleteOutgoingUserHelperMethodProver()
    {
        return [
            ['case1_files', 'iet.sample.conf', 'iet.expected.testDeleteOutgoingUserHelperMethod.conf'],
            ['case2_files', 'iet.sample.conf', 'iet.expected.testDeleteOutgoingUserHelperMethod.conf'],
        ];
    }

    /**
     * @param $dir
     * @param $sourceFile
     * @param $expectedFile
     *
     * @dataProvider deleteOutgoingUserHelperMethodProver
     */
    public function testDeleteOutgoingUserHelperMethod($dir, $sourceFile, $expectedFile)
    {
        $dir = __DIR__ . DIRECTORY_SEPARATOR . $dir;

        $objects = $this->normalize($dir, $sourceFile);

        $parser = new GlobalOptionParser($objects['filesystem'], self::$testFile);

        if ($objects['normalizer']->check()) {
            $parser->deleteOutgoingUser('user2', 'password2')->write();
        } else {
            $this->fail("The normalizer did not properly normalize the file!");
        }

        $this->assertFileEquals($dir . DIRECTORY_SEPARATOR . $expectedFile, $dir . DIRECTORY_SEPARATOR . self::$testFile);
    }
}