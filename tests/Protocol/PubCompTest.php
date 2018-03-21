<?php

declare(strict_types=1);

namespace tests\unreal4u\MQTT;

use PHPUnit\Framework\TestCase;
use tests\unreal4u\MQTT\Mocks\ClientMock;
use unreal4u\MQTT\Application\EmptyReadableResponse;
use unreal4u\MQTT\DataTypes\PacketIdentifier;
use unreal4u\MQTT\Exceptions\NonMatchingPacketIdentifiers;
use unreal4u\MQTT\Protocol\PubComp;
use unreal4u\MQTT\Protocol\PubRel;

class PubCompTest extends TestCase
{
    /**
     * @var PubComp
     */
    private $pubComp;

    protected function setUp()
    {
        $this->pubComp = new PubComp();
        parent::setUp();
    }

    public function test_getOriginControlPacketValue()
    {
        $this->assertSame(PubRel::getControlPacketValue(), $this->pubComp->getOriginControlPacket());
    }

    /**
     * May seem like a useless test, but if no exception is thrown, the object itself will be returned.
     *
     * This test will assert that no exception is actually being thrown.
     */
    public function test_emulateSuccessfulRequest()
    {
        $clientMock = new ClientMock();

        $this->assertInstanceOf(
            PubComp::class,
            $this->pubComp->fillObject(base64_decode('cAIAIw=='), $clientMock)
        );

        $pubrel = new PubRel();
        $pubrel->setPacketIdentifier(new PacketIdentifier(35));

        $this->assertTrue($this->pubComp->performSpecialActions($clientMock, $pubrel));
    }

    public function test_badPacketIdentifier()
    {
        $clientMock = new ClientMock();

        $this->assertInstanceOf(
            PubComp::class,
            // Packet identifier: 45
            $this->pubComp->fillObject(base64_decode('cAIAJg=='), $clientMock)
        );

        $pubrel = new PubRel();
        $pubrel->setPacketIdentifier(new PacketIdentifier(44));

        $this->expectException(NonMatchingPacketIdentifiers::class);
        $this->pubComp->performSpecialActions($clientMock, $pubrel);
    }

    public function test_shouldExpectAnswer()
    {
        $this->assertFalse($this->pubComp->shouldExpectAnswer());
    }

    public function test_expectAnswer()
    {
        $this->assertInstanceOf(EmptyReadableResponse::class, $this->pubComp->expectAnswer('', new ClientMock()));
    }

    public function test_createPayload()
    {
        $this->assertSame('', $this->pubComp->createPayload());
    }

    public function test_createVariableHeader()
    {
        $this->pubComp->setPacketIdentifier(new PacketIdentifier(46));
        $this->assertSame('AC4=', base64_encode($this->pubComp->createVariableHeader()));
    }
}
