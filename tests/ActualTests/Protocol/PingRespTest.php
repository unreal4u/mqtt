<?php

declare(strict_types=1);


namespace tests\unreal4u\MQTT;

use PHPUnit\Framework\TestCase;
use unreal4u\MQTT\Protocol\PingResp;

class PingRespTest extends TestCase
{
    public function testCheckControlPacketValue()
    {
        $success = \chr(208) . \chr(0);
        $pingResp = new PingResp();
        $pingResp->populate($success);
        $pingResp->checkControlPacketValue();

        $this->assertTrue(true);
    }
}
