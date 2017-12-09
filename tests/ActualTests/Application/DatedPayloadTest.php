<?php

declare(strict_types=1);

namespace tests\unreal4u\MQTT\Application;

use PHPUnit\Framework\TestCase;
use unreal4u\MQTT\Application\DatedPayload;

class DatedPayloadTest extends TestCase
{
    public function test_createDefault()
    {
        $datedPayload = new DatedPayload();

        $this->assertInstanceOf(DatedPayload::class, $datedPayload);
    }

    public function test_setPayload()
    {
        $datedPayload = new DatedPayload();
        $datedPayload->setPayload('This is a test');

        $this->assertSame('This is a test', $datedPayload->getPayload());
    }

    public function test_createWithMessage()
    {
        $datedPayload = new DatedPayload('This is a test');
        $now = new \DateTimeImmutable('now');

        $this->assertSame('This is a test', $datedPayload->getPayload());
        $this->assertInstanceOf(\DateTimeImmutable::class, $datedPayload->originalPublishDateTime);
        $this->assertSame($now->format('d-m-Y H'), $datedPayload->originalPublishDateTime->format('d-m-Y H'));
    }

    public function test_processIncomingPayload()
    {
        $datedPayload = new DatedPayload();
        $datedPayload->processIncomingPayload('{"publishDateTime":"Sat, 09 Dec 2017 14:58:58 +0000","payload":"This is a test"}');
        $publishedDateTime = new \DateTimeImmutable('2017-12-09 14:58:58', new \DateTimeZone('UTC'));

        $this->assertSame('This is a test', $datedPayload->getPayload());
        $this->assertInstanceOf(\DateTimeImmutable::class, $datedPayload->originalPublishDateTime);
        $this->assertSame($publishedDateTime->format('d-m-Y H:i:s'), $datedPayload->originalPublishDateTime->format('d-m-Y H:i:s'));
    }

    public function test_getProcessedPayload()
    {
        $datedPayload = new DatedPayload('This is a test');
        $decodedOutput = json_decode($datedPayload->getProcessedPayload(), true);

        $this->assertSame('This is a test', $decodedOutput['payload']);
        $this->assertArrayHasKey('publishDateTime', $decodedOutput);
    }
}
