<?php

declare(strict_types=1);

namespace tests\unreal4u\MQTT\DataTypes;

use PHPUnit\Framework\TestCase;
use unreal4u\MQTT\DataTypes\PacketIdentifier;

class PacketIdentifierTest extends TestCase
{
    public function providerOutOfRange(): array
    {
        $mapValues[] = [-322];
        $mapValues[] = [0];
        $mapValues[] = [65536];
        $mapValues[] = [65999];

        return $mapValues;
    }

    /**
     * @dataProvider providerOutOfRange
     * @param int $number
     */
    public function testOutOfRange(int $number): void
    {
        $this->expectException(\OutOfRangeException::class);
        new PacketIdentifier($number);
    }

    public function providerValidPacketIdentifier(): array
    {
        $mapValues[] = [1];
        $mapValues[] = [35];
        $mapValues[] = [32768];
        $mapValues[] = [65535];

        return $mapValues;
    }

    /**
     * @dataProvider providerValidPacketIdentifier
     * @param int $number
     */
    public function testValidPacketIdentifier(int $number): void
    {
        $packetIdentifier = new PacketIdentifier($number);
        $this->assertSame($number, $packetIdentifier->getPacketIdentifierValue());
    }

    public function testToString(): void
    {
        $packetIdentifier = new PacketIdentifier(35);
        $this->assertSame('ACM=', base64_encode((string)$packetIdentifier));
    }
}
