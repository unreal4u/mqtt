<?php

declare(strict_types=1);

namespace tests\unreal4u\MQTT;

use PHPUnit\Framework\TestCase;
use unreal4u\MQTT\Utilities;

class UtilitiesTest extends TestCase
{
    public function provider_convertEndianness(): array
    {
        $mapValues[] = [16, 4096];
        $mapValues[] = [32, 8192];
        $mapValues[] = [256, 1];
        $mapValues[] = [258, 513];
        $mapValues[] = [265, 2305];
        $mapValues[] = [2305, 265];
        $mapValues[] = [2417, 28937];
        $mapValues[] = [6530, 33305];

        return $mapValues;
    }

    /**
     * @dataProvider provider_convertEndianness
     * @param int $number
     * @param int $expectedNumber
     */
    public function test_convertEndianness(int $number, int $expectedNumber)
    {
        $this->assertSame($expectedNumber, Utilities::convertEndianness($number));
    }

    public function test_maximumIntReached()
    {
        $this->expectException(\OutOfRangeException::class);
        Utilities::convertEndianness(65537);
    }

    public function provider_convertNumberToBinaryString(): array
    {
        $mapValues[] = [1, 'AAE='];
        $mapValues[] = [15, 'AA8='];
        $mapValues[] = [16, 'ABA='];
        $mapValues[] = [254, 'AP4='];
        $mapValues[] = [256, 'AQA='];
        $mapValues[] = [2305, 'CQE='];
        $mapValues[] = [2417, 'CXE='];
        $mapValues[] = [6530, 'GYI='];
        $mapValues[] = [32535, 'fxc='];
        $mapValues[] = [62535, '9Ec='];
        $mapValues[] = [65535, '//8='];

        return $mapValues;
    }

    /**
     * @dataProvider provider_convertNumberToBinaryString
     * @param int $number
     * @param string $expectedOutput base64_encoded representation of the outputted number
     */
    public function test_convertNumberToBinaryString(int $number, string $expectedOutput)
    {
        $this->assertSame($expectedOutput, base64_encode(Utilities::convertNumberToBinaryString($number)));
    }

    public function test_convertNumberToBinaryStringHighNumber()
    {
        $this->expectException(\OutOfRangeException::class);
        Utilities::convertNumberToBinaryString(65537);
    }

    /**
     * @dataProvider provider_convertNumberToBinaryString
     * @param string $binaryString
     * @param int $expectedOutput
     */
    public function test_convertBinaryStringToNumber(int $expectedOutput, string $binaryString)
    {
        $this->assertSame($expectedOutput, Utilities::convertBinaryStringToNumber(base64_decode($binaryString)));
    }
}
