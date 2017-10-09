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
        $mapValues[] = [6530, -32231];

        return $mapValues;
    }

    /**
     * @dataProvider provider_convertEndianness
     * @param int $number
     * @param int $expectedNumber
     */
    public function test_convertEndianness(int $number, int $expectedNumber)
    {
        $convertedNumber = Utilities::convertEndianness($number);
        $this->assertSame($expectedNumber, $convertedNumber);
    }
}
