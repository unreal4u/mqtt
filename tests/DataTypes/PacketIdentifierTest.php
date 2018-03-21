<?php

declare(strict_types=1);

namespace tests\unreal4u\MQTT\DataTypes;

use PHPUnit\Framework\TestCase;
use unreal4u\MQTT\DataTypes\PacketIdentifier;

class PacketIdentifierTest extends TestCase
{
    public function provider_test_outOfRange(): array
    {
        $mapValues[] = [-322];
        $mapValues[] = [0];
        $mapValues[] = [65536];
        $mapValues[] = [65999];

        return $mapValues;
    }

    /**
     * @dataProvider provider_test_outOfRange
     * @param int $number
     */
    public function test_outOfRange(int $number)
    {
        $this->expectException(\OutOfRangeException::class);
        new PacketIdentifier($number);
    }

    public function provider_validPacketIdentifier(): array
    {
        $mapValues[] = [1];
        $mapValues[] = [35];
        $mapValues[] = [32768];
        $mapValues[] = [65535];

        return $mapValues;
    }

    /**
     * @dataProvider provider_validPacketIdentifier
     * @param int $number
     */
    public function test_validPacketIdentifier(int $number)
    {
        $packetIdentifier = new PacketIdentifier($number);
        $this->assertSame($number, $packetIdentifier->getPacketIdentifierValue());
    }

    public function test_toString()
    {
        $packetIdentifier = new PacketIdentifier(35);
        $this->assertSame('ACM=', base64_encode((string)$packetIdentifier));
    }
}
