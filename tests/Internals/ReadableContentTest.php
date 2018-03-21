<?php

declare(strict_types=1);

namespace tests\unreal4u\MQTT\Internals;

use PHPUnit\Framework\TestCase;
use tests\unreal4u\MQTT\Mocks\ClientMock;
use unreal4u\MQTT\Exceptions\InvalidResponseType;
use unreal4u\MQTT\Protocol\PingResp;

class ReadableContentTest extends TestCase
{
    public function test_incorrectControlPacketValue()
    {
        $success = \chr(100) . \chr(0);
        $pingResp = new PingResp();

        $this->expectException(InvalidResponseType::class);
        $pingResp->instantiateObject($success, new ClientMock());
    }
}
