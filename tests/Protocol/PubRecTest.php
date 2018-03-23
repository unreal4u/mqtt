<?php

declare(strict_types=1);

namespace tests\unreal4u\MQTT;

use PHPUnit\Framework\TestCase;
use unreal4u\MQTT\Protocol\PubRec;
use unreal4u\MQTT\Protocol\PubRel;

class PubRecTest extends TestCase
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
}
