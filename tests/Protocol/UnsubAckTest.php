<?php

declare(strict_types=1);

namespace tests\unreal4u\MQTT;

use PHPUnit\Framework\TestCase;
use tests\unreal4u\MQTT\Mocks\ClientMock;
use unreal4u\MQTT\DataTypes\PacketIdentifier;
use unreal4u\MQTT\Protocol\UnsubAck;
use unreal4u\MQTT\Protocol\Unsubscribe;

class UnsubAckTest extends TestCase
{
    /**
     * @var UnsubAck
     */
    private $unsuback;

    protected function setUp()
    {
        $this->unsuback = new UnsubAck();
        parent::setUp();
    }

    public function test_getOriginControlPacket()
    {
        $this->assertSame(Unsubscribe::getControlPacketValue(), $this->unsuback->getOriginControlPacket());
    }

    public function test_performSpecialActions()
    {
        $clientMock = new ClientMock();

        $unsubscribe = new Unsubscribe();
        $unsubscribe->setPacketIdentifier(new PacketIdentifier(66));

        $this->unsuback->setPacketIdentifier(new PacketIdentifier(66));
        $this->assertTrue($this->unsuback->performSpecialActions($clientMock, $unsubscribe));
        $this->assertTrue($clientMock->updateLastCommunicationWasCalled());
    }

    public function test_fillObjectWithFullHeaders()
    {
        $this->unsuback->fillObject(base64_decode('sAIWvA=='), new ClientMock());
        $this->assertSame(5820, $this->unsuback->getPacketIdentifier());
    }

    public function test_fillObjectWithPartialHeaders()
    {
        $clientMock = new ClientMock();
        $clientMock->returnSpecificBrokerData(['Aq6/']);

        $this->unsuback->fillObject(base64_decode('sA=='), $clientMock);
        $this->assertSame(44735, $this->unsuback->getPacketIdentifier());
    }
}
