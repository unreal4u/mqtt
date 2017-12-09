<?php

declare(strict_types=1);

namespace tests\unreal4u\MQTT\Internals;

use PHPUnit\Framework\TestCase;
use unreal4u\MQTT\Protocol\PingReq;

class WritableContentTest extends TestCase
{
    public function test_validProtocolLevel()
    {
        $pingRequest = new PingReq();

        $this->assertSame(\chr(4), $pingRequest->getProtocolLevel());
    }

    public function test_invalidProtocolLevel()
    {
        $pingRequest = new PingReq();
        $pingRequest->protocolLevel = '2.0.0';

        // This must, per RFC, still be possible and will not throw an exception
        $this->assertSame(\chr(0), $pingRequest->getProtocolLevel());
    }

    public function test_createUTF8String()
    {
        $pingRequest = new PingReq();

        $this->assertSame('AA5UaGlzIGlzIGEgdGVzdA==', base64_encode($pingRequest->createUTF8String('This is a test')));
    }

    public function test_createFixedHeader()
    {
        $pingRequest = new PingReq();

        $this->assertSame('wAI=', base64_encode($pingRequest->createFixedHeader(2)));
        $this->assertSame('wAQ=', base64_encode($pingRequest->createFixedHeader(4)));
    }

    public function test_createSendableMessage()
    {
        $pingRequest = new PingReq();

        $this->assertSame('wAA=', base64_encode($pingRequest->createSendableMessage()));
    }
}
