<?php

declare(strict_types=1);

namespace tests\unreal4u\MQTT\DataTypes;

use PHPUnit\Framework\TestCase;
use unreal4u\MQTT\DataTypes\BrokerPort;
use unreal4u\MQTT\Exceptions\InvalidBrokerPort;
use unreal4u\MQTT\Exceptions\InvalidBrokerProtocol;

class BrokerPortTest extends TestCase
{
    public function test_negativeBrokerPort()
    {
        $this->expectException(InvalidBrokerPort::class);
        new BrokerPort(-1);
    }

    public function test_outOfRangePort()
    {
        $this->expectException(InvalidBrokerPort::class);
        new BrokerPort(76543);
    }

    public function provider_validBrokerPorts(): array
    {
        $mapValues[] = [1];
        $mapValues[] = [1182];
        $mapValues[] = [65422];

        return $mapValues;
    }

    /**
     * @dataProvider provider_validBrokerPorts
     * @param int $port
     */
    public function test_validBrokerPorts(int $port)
    {
        $brokerPort = new BrokerPort($port);
        $this->assertSame($port, $brokerPort->getBrokerPort());
    }

    public function provider_validProtocols(): array
    {
        $mapValues[] = ['tcp'];
        $mapValues[] = ['ssl'];
        $mapValues[] = ['tlsv1.0'];
        $mapValues[] = ['tlsv1.1'];
        $mapValues[] = ['tlsv1.2'];

        return $mapValues;
    }

    /**
     * @dataProvider provider_validProtocols
     * @param string $protocol
     */
    public function test_validProtocols(string $protocol)
    {
        $brokerPort = new BrokerPort(112, $protocol);
        $this->assertSame($protocol, $brokerPort->getTransmissionProtocol());
    }

    public function test_invalidProtocol()
    {
        $this->expectException(InvalidBrokerProtocol::class);
        new BrokerPort(112, 'this-wont-ever-be-an-approved-protocol-name');
    }
}
