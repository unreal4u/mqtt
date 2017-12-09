<?php

declare(strict_types=1);

namespace tests\unreal4u\MQTT\Application;

use PHPUnit\Framework\TestCase;
use unreal4u\MQTT\Application\SimplePayload;

class SimplePayloadTest extends TestCase
{
    public function test_createDefault()
    {
        $simplePayload = new SimplePayload();

        $this->assertInstanceOf(SimplePayload::class, $simplePayload);
    }

    public function test_setPayload()
    {
        $simplePayload = new SimplePayload();
        $simplePayload->setPayload('This is a test');

        $this->assertSame('This is a test', $simplePayload->getPayload());
    }

    public function test_createWithMessage()
    {
        $simplePayload = new SimplePayload('This is a test');

        $this->assertSame('This is a test', $simplePayload->getPayload());
    }

    public function test_processIncomingPayload()
    {
        $simplePayload = new SimplePayload();
        $simplePayload->processIncomingPayload('This is a test');

        $this->assertSame('This is a test', $simplePayload->getPayload());
    }
}
