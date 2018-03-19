<?php

declare(strict_types=1);

namespace tests\unreal4u\MQTT;

use PHPUnit\Framework\TestCase;
use unreal4u\MQTT\Protocol\PingReq;

class PingReqTest extends TestCase
{
    /**
     * @var PingReq
     */
    private $pingReq;

    protected function setUp()
    {
        parent::setUp();
        $this->pingReq = new PingReq();
    }

    protected function tearDown()
    {
        parent::tearDown();
        $this->pingReq = null;
    }


    public function test_createVariableHeader()
    {
        $this->assertSame('', $this->pingReq->createVariableHeader());
    }

    public function test_createPayload()
    {
        $this->assertSame('', $this->pingReq->createPayload());
    }

    public function test_shouldExpectAnswer()
    {
        $this->assertTrue($this->pingReq->shouldExpectAnswer());
    }
}
