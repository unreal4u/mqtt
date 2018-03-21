<?php

declare(strict_types=1);


namespace tests\unreal4u\MQTT;

use PHPUnit\Framework\TestCase;
use tests\unreal4u\MQTT\Mocks\ClientMock;
use unreal4u\MQTT\Protocol\PingResp;

class PingRespTest extends TestCase
{
    public function testCheckControlPacketValue()
    {
        $success = \chr(208) . \chr(0);
        $pingResp = new PingResp();
        $pingResp->instantiateObject($success, new ClientMock());
        #$pingResp->checkControlPacketValue(\ord($success) >> 4, new ClientMock());

        // If nothing went wrong above and we are still here, the test is a pass
        $this->assertTrue(true);
    }
}