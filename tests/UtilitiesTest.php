<?php

declare(strict_types=1);

namespace tests\unreal4u\MQTT;

use PHPUnit\Framework\TestCase;
use unreal4u\MQTT\Exceptions\MessageTooBig;
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

    /**
     * Tests whether a message that is too big will throw an exception
     */
    public function test_formatRemainingLengthOutputIsTooLarge()
    {
        $this->expectException(MessageTooBig::class);
        Utilities::formatRemainingLengthOutput(268435456);
    }

    /**
     * @return array
     */
    public function provider_remainingLength(): array
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
     * @dataProvider provider_remainingLength
     * @param int $lengthInBytes
     * @param string $expectedOutput
     */
    public function test_formatRemainingLengthOutput(int $lengthInBytes, string $expectedOutput)
    {
        $this->assertSame($expectedOutput, base64_encode(Utilities::formatRemainingLengthOutput($lengthInBytes)));
    }

    /**
     * @dataProvider provider_remainingLength
     * @param string $encodedLength
     * @param int $expectedOutput
     */
    public function test_convertRemainingLengthStringToInt(int $expectedOutput, string $encodedLength)
    {
        $this->assertSame($expectedOutput, Utilities::convertRemainingLengthStringToInt(base64_decode($encodedLength)));
    }
}
