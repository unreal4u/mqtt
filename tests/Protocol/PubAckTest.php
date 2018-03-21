<?php

declare(strict_types=1);

namespace tests\unreal4u\MQTT;

use PHPUnit\Framework\TestCase;
use tests\unreal4u\MQTT\Mocks\ClientMock;
use unreal4u\MQTT\DataTypes\PacketIdentifier;
use unreal4u\MQTT\Protocol\PubAck;
use unreal4u\MQTT\Protocol\Publish;

class PubAckTest extends TestCase
{
    /**
     * @var PubAck
     */
    private $pubAck;

    protected function setUp()
    {
        $this->pubAck = new PubAck();
        parent::setUp();
    }

    public function test_getOriginControlPacketValue()
    {
        $this->assertSame(Publish::getControlPacketValue(), $this->pubAck->getOriginControlPacket());
    }

    /**
     * May seem like a useless test, but if no exception is thrown, the object itself will be returned.
     *
     * This test will assert that no exception is actually being thrown.
     */
    public function test_emulateSuccessfulConnection()
    {
        $clientMock = new ClientMock();

        $this->assertInstanceOf(
            PubAck::class,
            $this->pubAck->fillObject(base64_decode('QAIALA=='), $clientMock)
        );

        $publish = new Publish();
        $publish->setPacketIdentifier(new PacketIdentifier(44));

        $this->assertTrue($this->pubAck->performSpecialActions($clientMock, $publish));
    }

    public function test_badPacketIdentifier()
    {
        $clientMock = new ClientMock();

        $this->assertInstanceOf(
            PubAck::class,
            // Packet identifier: 45
            $this->pubAck->fillObject(base64_decode('QAIALQ=='), $clientMock)
        );

        $publish = new Publish();
        $publish->setPacketIdentifier(new PacketIdentifier(44));

        $this->expectException(\LogicException::class);
        $this->assertTrue($this->pubAck->performSpecialActions($clientMock, $publish));
    }

    public function test_shouldExpectAnswer()
    {
        $this->assertFalse($this->pubAck->shouldExpectAnswer());
    }

    public function test_expectAnswer()
    {
        $this->assertInstanceOf(PubAck::class, $this->pubAck->expectAnswer('', new ClientMock()));
    }

    public function test_createPayload()
    {
        $this->assertSame('', $this->pubAck->createPayload());
    }

    public function test_createVariableHeader()
    {
        $this->pubAck->setPacketIdentifier(new PacketIdentifier(46));
        $this->assertSame('AC4=', base64_encode($this->pubAck->createVariableHeader()));
    }
}
