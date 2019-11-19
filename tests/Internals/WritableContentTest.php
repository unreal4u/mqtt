<?php

declare(strict_types=1);

namespace tests\unreal4u\MQTT\Internals;

use PHPUnit\Framework\TestCase;
use unreal4u\MQTT\Protocol\PingReq;

class WritableContentTest extends TestCase
{
    public function testCreateUTF8String(): void
    {
        $pingRequest = new PingReq();
        $this->assertSame('AA5UaGlzIGlzIGEgdGVzdA==', base64_encode($pingRequest->createUTF8String('This is a test')));
    }

    public function testEmptyString(): void
    {
        $pingRequest = new PingReq();
        $this->assertSame('', base64_encode($pingRequest->createUTF8String('')));
    }

    public function testCreateFixedHeader(): void
    {
        $pingRequest = new PingReq();

        $this->assertSame('wAI=', base64_encode($pingRequest->createFixedHeader(2)));
        $this->assertSame('wAQ=', base64_encode($pingRequest->createFixedHeader(4)));
    }

    public function testCreateSendableMessageWithSmallPayload(): void
    {
        $pingRequest = new PingReq();
        $this->assertSame('wAA=', base64_encode($pingRequest->createSendableMessage()));
    }

    /* This is not enough for a really big message to trigger some exceptions
    public function test_createSendableMessageWithBigPayload()
    {
        $publish = new Publish();
        $message = new Message(str_repeat('X', 64000), new TopicFilter(str_repeat('Y', 64000)));

        $publish->setMessage($message);
        var_dump(base64_encode($publish->createSendableMessage()));
    }
    */
}
