<?php

declare(strict_types=1);

namespace tests\unreal4u\MQTT\Application;

use PHPUnit\Framework\TestCase;
use tests\unreal4u\MQTT\Mocks\ClientMock;
use unreal4u\MQTT\Application\EmptyReadableResponse;

class EmptyReadableResponseTest extends TestCase
{
    public function test_originPacketIdentifier()
    {
        $emptyReadableResponse = new EmptyReadableResponse();
        $this->assertSame(0, $emptyReadableResponse->getOriginControlPacket());
    }

    public function test_correctPacket()
    {
        $emptyReadableResponse = new EmptyReadableResponse();
        $this->assertInstanceOf(EmptyReadableResponse::class, $emptyReadableResponse->fillObject('', new ClientMock()));
    }
}
