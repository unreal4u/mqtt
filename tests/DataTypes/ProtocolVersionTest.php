<?php

declare(strict_types=1);

namespace tests\unreal4u\MQTT\DataTypes;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use unreal4u\MQTT\DataTypes\ProtocolVersion;
use unreal4u\MQTT\Exceptions\Connect\UnacceptableProtocolVersion;

class ProtocolVersionTest extends TestCase
{
    public function testInvalidProtocolVersion(): void
    {
        $this->expectException(UnacceptableProtocolVersion::class);
        new ProtocolVersion('3.0.0');
    }

    public function testValidProtocolVersion(): void
    {
        $protocolVersion = new ProtocolVersion('3.1.1');
        $this->assertSame('3.1.1', $protocolVersion->getProtocolVersion());
    }

    public function testToString(): void
    {
        $protocolVersion = new ProtocolVersion('3.1.1');
        $this->assertSame('3.1.1', (string)$protocolVersion);
    }

    public function testValidProtocolVersionBinaryRepresentation(): void
    {
        $protocolVersion = new ProtocolVersion('3.1.1');
        $this->assertSame(\chr(4), $protocolVersion->getProtocolVersionBinaryRepresentation());
    }

    public function testInvalidProtocolVersionBinaryRepresentation(): void
    {
        $protocolVersion = new ProtocolVersion('3.1.1');
        $reflectionClass = new ReflectionClass(ProtocolVersion::class);

        $reflectionProperty = $reflectionClass->getProperty('protocolVersion');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($protocolVersion, '3.0.0');

        $this->assertSame(\chr(0), $protocolVersion->getProtocolVersionBinaryRepresentation());
    }
}
