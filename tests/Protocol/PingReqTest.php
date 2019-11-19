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


    public function testCreateVariableHeader(): void
    {
        $this->assertSame('', $this->pingReq->createVariableHeader());
    }

    public function testCreatePayload(): void
    {
        $this->assertSame('', $this->pingReq->createPayload());
    }

    public function testShouldExpectAnswer(): void
    {
        $this->assertTrue($this->pingReq->shouldExpectAnswer());
    }
}
