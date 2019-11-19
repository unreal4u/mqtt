<?php

declare(strict_types=1);

namespace tests\unreal4u\MQTT;

use PHPUnit\Framework\TestCase;
use unreal4u\MQTT\Exceptions\MessageTooBig;
use unreal4u\MQTT\Utilities;

class UtilitiesTest extends TestCase
{
    public function providerConvertEndianness(): array
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
     * @dataProvider providerConvertEndianness
     * @param int $number
     * @param int $expectedNumber
     */
    public function testConvertEndianness(int $number, int $expectedNumber): void
    {
        $this->assertSame($expectedNumber, Utilities::convertEndianness($number));
    }

    public function testMaximumIntReached(): void
    {
        $this->expectException(\OutOfRangeException::class);
        Utilities::convertEndianness(65537);
    }

    public function providerConvertNumberToBinaryString(): array
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
     * @dataProvider providerConvertNumberToBinaryString
     * @param int $number
     * @param string $expectedOutput base64_encoded representation of the outputted number
     */
    public function testConvertNumberToBinaryString(int $number, string $expectedOutput): void
    {
        $this->assertSame($expectedOutput, base64_encode(Utilities::convertNumberToBinaryString($number)));
    }

    public function testConvertNumberToBinaryStringHighNumber(): void
    {
        $this->expectException(\OutOfRangeException::class);
        Utilities::convertNumberToBinaryString(65537);
    }

    /**
     * @dataProvider providerConvertNumberToBinaryString
     * @param string $binaryString
     * @param int $expectedOutput
     */
    public function testConvertBinaryStringToNumber(int $expectedOutput, string $binaryString): void
    {
        $this->assertSame($expectedOutput, Utilities::convertBinaryStringToNumber(base64_decode($binaryString)));
    }

    /**
     * Tests whether a message that is too big will throw an exception
     */
    public function testFormatRemainingLengthOutputIsTooLarge(): void
    {
        $this->expectException(MessageTooBig::class);
        Utilities::formatRemainingLengthOutput(268435456);
    }

    /**
     * @return array
     */
    public function providerRemainingLength(): array
    {
        // Min. possible values, 1 byte long
        $mapValues[] = [0, 'AA=='];
        $mapValues[] = [1, 'AQ=='];
        $mapValues[] = [2, 'Ag=='];
        // Most probable cases, 1 to 2 bytes long
        $mapValues[] = [24, 'GA=='];
        $mapValues[] = [127, 'fw=='];
        $mapValues[] = [128, 'gAE='];
        $mapValues[] = [129, 'gQE='];
        $mapValues[] = [200, 'yAE='];
        $mapValues[] = [364, '7AI='];
        $mapValues[] = [1023, '/wc='];
        // One extra byte
        $mapValues[] = [16385, 'gYAB'];
        $mapValues[] = [25897, 'qcoB'];
        // Maximum number of bytes used in remaining length: 4
        $mapValues[] = [2097153, 'gYCAAQ=='];
        $mapValues[] = [268435455, '////fw=='];

        return $mapValues;
    }

    /**
     * @dataProvider providerRemainingLength
     * @param int $lengthInBytes
     * @param string $expectedOutput
     */
    public function testFormatRemainingLengthOutput(int $lengthInBytes, string $expectedOutput): void
    {
        $this->assertSame($expectedOutput, base64_encode(Utilities::formatRemainingLengthOutput($lengthInBytes)));
    }

    /**
     * @dataProvider providerRemainingLength
     * @param string $encodedLength
     * @param int $expectedOutput
     */
    public function testConvertRemainingLengthStringToInt(int $expectedOutput, string $encodedLength): void
    {
        $this->assertSame($expectedOutput, Utilities::convertRemainingLengthStringToInt(base64_decode($encodedLength)));
    }
}
