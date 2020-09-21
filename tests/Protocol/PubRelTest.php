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

    protected function setUp(): void
    {
        $this->pubRel = new PubRel();
        parent::setUp();
    }

    public function testGetOriginControlPacketValue(): void
    {
        $this->assertSame(PubRec::getControlPacketValue(), $this->pubRel->getOriginControlPacket());
    }

    public function testShouldExpectAnswer(): void
    {
        $this->assertTrue($this->pubRel->shouldExpectAnswer());
    }

    public function testCreatePayload(): void
    {
        $this->assertSame('', $this->pubRel->createPayload());
    }

    public function testCreateVariableHeader(): void
    {
        $this->pubRel->setPacketIdentifier(new PacketIdentifier(88));
        $this->assertSame('AFg=', base64_encode($this->pubRel->createVariableHeader()));
    }

    public function testPerformSpecialActionsWithValidObject(): void
    {
        $clientMock = new ClientMock();
        $pubRec = new PubRec();
        $pubRec->setPacketIdentifier(new PacketIdentifier(999));
        $this->pubRel->setPacketIdentifier(new PacketIdentifier(999));

        $specialActionsPerformed = $this->pubRel->performSpecialActions($clientMock, $pubRec);
        $this->assertTrue($specialActionsPerformed);
        $this->assertSame(PubComp::class, $clientMock->processObjectWasCalledWithObjectType());
    }

    public function testPerformSpecialActionsWithInvalidObject(): void
    {
        $specialActionsPerformed = $this->pubRel->performSpecialActions(new ClientMock(), new PingReq());
        $this->assertFalse($specialActionsPerformed);
    }

    public function providerFillObject(): array
    {
        $mapValues[] = ['Yg==', 'AgAS', 18];
        $mapValues[] = ['YgI=', 'ABQ=', 20];
        $mapValues[] = ['YgIAFg==', '', 22];

        return $mapValues;
    }

    /**
     * @dataProvider providerFillObject
     * @param string $firstBytes
     * @param string $append
     * @param int $expectedPacketIdentifier
     */
    public function testFillObject(
        string $firstBytes,
        string $append,
        int $expectedPacketIdentifier
    ): void {
        $clientMock = new ClientMock();
        $clientMock->returnSpecificBrokerData([$append]);

        $this->pubRel->fillObject(base64_decode($firstBytes), $clientMock);
        $this->assertSame($expectedPacketIdentifier, $this->pubRel->getPacketIdentifier());
    }
}
