<?php

declare(strict_types=1);

namespace tests\unreal4u\MQTT\Application;

use PHPUnit\Framework\TestCase;
use unreal4u\MQTT\DataTypes\ProtocolVersion;
use unreal4u\MQTT\Exceptions\Connect\UnacceptableProtocolVersion;

class ProtocolVersionTest extends TestCase
{
    public function test_invalidProtocolVersion()
    {
        $this->expectException(UnacceptableProtocolVersion::class);
        new ProtocolVersion('3.0.0');
    }

    public function test_validProtocolVersion()
    {
        $protocolVersion = new ProtocolVersion('3.1.1');
        $this->assertSame('3.1.1', $protocolVersion->getProtocolVersion());
    }

    public function test_validDefaultValue()
    {
        $protocolVersion = new ProtocolVersion();
        $this->assertSame('3.1.1', $protocolVersion->getProtocolVersion());
    }

    public function test_toString()
    {
        $protocolVersion = new ProtocolVersion('3.1.1');
        $this->assertSame('3.1.1', (string)$protocolVersion);
    }
}
