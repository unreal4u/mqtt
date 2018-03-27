<?php

declare(strict_types=1);

namespace tests\unreal4u\MQTT;

use PHPUnit\Framework\TestCase;
use tests\unreal4u\MQTT\Mocks\ClientMock;
use unreal4u\MQTT\DataTypes\PacketIdentifier;
use unreal4u\MQTT\Protocol\PingReq;
use unreal4u\MQTT\Protocol\PubComp;
use unreal4u\MQTT\Protocol\PubRec;
use unreal4u\MQTT\Protocol\PubRel;

class PubRelTest extends TestCase
{
    /**
     * @var PubRel
     */
    private $pubRel;

    protected function setUp()
    {
        $this->pubRel = new PubRel();
        parent::setUp();
    }

    public function test_getOriginControlPacketValue()
    {
        $this->assertSame(PubRec::getControlPacketValue(), $this->pubRel->getOriginControlPacket());
    }

    public function test_shouldExpectAnswer()
    {
        $this->assertTrue($this->pubRel->shouldExpectAnswer());
    }

    public function test_createPayload()
    {
        $this->assertSame('', $this->pubRel->createPayload());
    }

    public function test_createVariableHeader()
    {
        $this->pubRel->setPacketIdentifier(new PacketIdentifier(88));
        $this->assertSame('AFg=', base64_encode($this->pubRel->createVariableHeader()));
    }

    public function test_performSpecialActionsWithValidObject()
    {
        $clientMock = new ClientMock();
        $pubRec = new PubRec();
        $pubRec->setPacketIdentifier(new PacketIdentifier(999));

        $this->pubRel->setPacketIdentifier(new PacketIdentifier(999));
        $specialActionsPerformed = $this->pubRel->performSpecialActions($clientMock, $pubRec);
        $this->assertTrue($specialActionsPerformed);
        $this->assertSame(PubComp::class, $clientMock->processObjectWasCalledWithObjectType());
    }

    public function test_performSpecialActionsWithInvalidObject()
    {
        $specialActionsPerformed = $this->pubRel->performSpecialActions(new ClientMock(), new PingReq());
        $this->assertFalse($specialActionsPerformed);
    }
}
