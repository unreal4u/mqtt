<?php

declare(strict_types=1);

namespace tests\unreal4u\MQTT;

use PHPUnit\Framework\TestCase;
use tests\unreal4u\MQTT\Mocks\ClientMock;
use unreal4u\MQTT\DataTypes\PacketIdentifier;
use unreal4u\MQTT\Protocol\SubAck;
use unreal4u\MQTT\Protocol\Subscribe;

class SubAckTest extends TestCase
{
    /**
     * @var SubAck
     */
    private $suback;

    protected function setUp(): void
    {
        $this->suback = new SubAck();
        parent::setUp();
    }

    public function testGetOriginControlPacket(): void
    {
        $this->assertSame(Subscribe::getControlPacketValue(), $this->suback->getOriginControlPacket());
    }

    public function testPerformSpecialActions(): void
    {
        $clientMock = new ClientMock();

        $subscribe = new Subscribe();
        $subscribe->setPacketIdentifier(new PacketIdentifier(66));

        $this->suback->setPacketIdentifier(new PacketIdentifier(66));
        $this->assertTrue($this->suback->performSpecialActions($clientMock, $subscribe));
        $this->assertTrue($clientMock->updateLastCommunicationWasCalled());
    }
}
