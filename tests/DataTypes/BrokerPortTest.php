<?php

declare(strict_types=1);

namespace tests\unreal4u\MQTT\DataTypes;

use PHPUnit\Framework\TestCase;
use unreal4u\MQTT\DataTypes\BrokerPort;
use unreal4u\MQTT\Exceptions\InvalidBrokerPort;
use unreal4u\MQTT\Exceptions\InvalidBrokerProtocol;

class BrokerPortTest extends TestCase
{
    public function testNegativeBrokerPort(): void
    {
        $this->expectException(InvalidBrokerPort::class);
        new BrokerPort(-1);
    }

    public function testOutOfRangePort(): void
    {
        $this->expectException(InvalidBrokerPort::class);
        new BrokerPort(76543);
    }

    public function providerValidBrokerPorts(): array
    {
        $mapValues[] = [1];
        $mapValues[] = [1182];
        $mapValues[] = [65422];

        return $mapValues;
    }

    /**
     * @dataProvider providerValidBrokerPorts
     * @param int $port
     */
    public function testValidBrokerPorts(int $port): void
    {
        $brokerPort = new BrokerPort($port);
        $this->assertSame($port, $brokerPort->getBrokerPort());
    }

    public function providerValidProtocols(): array
    {
        $mapValues[] = ['tcp'];
        $mapValues[] = ['ssl'];
        $mapValues[] = ['tlsv1.0'];
        $mapValues[] = ['tlsv1.1'];
        $mapValues[] = ['tlsv1.2'];

        return $mapValues;
    }

    /**
     * @dataProvider providerValidProtocols
     * @param string $protocol
     */
    public function testValidProtocols(string $protocol): void
    {
        $brokerPort = new BrokerPort(112, $protocol);
        $this->assertSame($protocol, $brokerPort->getTransmissionProtocol());
    }

    public function testInvalidProtocol(): void
    {
        $this->expectException(InvalidBrokerProtocol::class);
        new BrokerPort(112, 'this-wont-ever-be-an-approved-protocol-name');
    }
}
