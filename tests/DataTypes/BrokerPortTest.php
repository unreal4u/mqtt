<?php

declare(strict_types=1);

namespace tests\unreal4u\MQTT\DataTypes;

use PHPUnit\Framework\TestCase;
use unreal4u\MQTT\DataTypes\BrokerPort;
use unreal4u\MQTT\Exceptions\InvalidBrokerPort;

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
}
