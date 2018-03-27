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

    public function provider_fillObject(): array
    {
        $mapValues[] = ['Yg==', 'AgAS', 18];
        $mapValues[] = ['YgI=', 'ABQ=', 20];
        $mapValues[] = ['YgIAFg==', '', 22];

        return $mapValues;
    }

    /**
     * @dataProvider provider_fillObject
     * @param string $firstBytes
     * @param string $append
     * @param int $expectedPacketIdentifier
     */
    public function test_fillObject(
        string $firstBytes,
        string $append,
        int $expectedPacketIdentifier
    ) {
        $clientMock = new ClientMock();
        $clientMock->returnSpecificBrokerData(base64_decode($append));

        $this->pubRel->fillObject(base64_decode($firstBytes), $clientMock);
        $this->assertSame($expectedPacketIdentifier, $this->pubRel->getPacketIdentifier());
    }
}
