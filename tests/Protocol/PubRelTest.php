<?php

declare(strict_types=1);

namespace tests\unreal4u\MQTT;

use PHPUnit\Framework\TestCase;
use unreal4u\MQTT\DataTypes\PacketIdentifier;
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
}
