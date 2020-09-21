<?php

declare(strict_types=1);

namespace tests\unreal4u\MQTT;

use PHPUnit\Framework\TestCase;
use tests\unreal4u\MQTT\Mocks\ClientMock;
use unreal4u\MQTT\DataTypes\PacketIdentifier;
use unreal4u\MQTT\Protocol\Publish;
use unreal4u\MQTT\Protocol\PubRec;
use unreal4u\MQTT\Protocol\PubRel;

class PubRecTest extends TestCase
{
    /**
     * @var PubRec
     */
    private $pubRec;

    protected function setUp(): void
    {
        $this->pubRec = new PubRec();
        parent::setUp();
    }

    public function testGetOriginControlPacketValue(): void
    {
        $this->assertSame(Publish::getControlPacketValue(), $this->pubRec->getOriginControlPacket());
    }

    public function testShouldExpectAnswer(): void
    {
        $this->assertTrue($this->pubRec->shouldExpectAnswer());
    }

    public function testCreatePayload(): void
    {
        $this->assertSame('', $this->pubRec->createPayload());
    }

    public function testCreateVariableHeader(): void
    {
        $this->pubRec->setPacketIdentifier(new PacketIdentifier(446));
        $this->assertSame('Ab4=', base64_encode($this->pubRec->createVariableHeader()));
    }

    public function testPerformSpecialActions(): void
    {
        $clientMock = new ClientMock();
        $publish = new Publish();
        $publish->setPacketIdentifier(new PacketIdentifier(567));
        $this->pubRec->setPacketIdentifier(new PacketIdentifier(567));

        $specialActionsPerformed = $this->pubRec->performSpecialActions($clientMock, $publish);
        $this->assertSame(PubRel::class, $clientMock->processObjectWasCalledWithObjectType());
        $this->assertTrue($specialActionsPerformed);
    }
}
