<?php

declare(strict_types=1);

namespace tests\unreal4u\MQTT;

use PHPUnit\Framework\TestCase;
use unreal4u\MQTT\Application\Message;
use unreal4u\MQTT\Application\SimplePayload;
use unreal4u\MQTT\Exceptions\MustProvideUsername;
use unreal4u\MQTT\Protocol\Connect;
use unreal4u\MQTT\Protocol\Connect\Parameters;

class ConnectTest extends TestCase
{
    /**
     * @var Connect
     */
    private $connect;

    protected function setUp()
    {
        parent::setUp();
        $this->connect = new Connect();
    }

    protected function tearDown()
    {
        parent::tearDown();
        $this->connect = null;
    }

    public function test_createVariableHeaderDefaultValues()
    {
        $this->connect->setConnectionParameters(new Parameters('UnitTestClientId'));
        $connectVariableHeader = $this->connect->createVariableHeader();
        $this->assertSame(10, mb_strlen($connectVariableHeader));
        $this->assertSame('AARNUVRUBAAAPA==', base64_encode($connectVariableHeader));
    }

    public function test_userAndPassword()
    {
        $parameters = new Parameters('UnitTestClientId');
        $parameters->setUsername('unreal4u');
        $parameters->setPassword('justT3st1ng');

        $this->connect->setConnectionParameters($parameters);
        $connectPayload = $this->connect->createPayload();

        $this->assertSame(41, mb_strlen($connectPayload));
        $this->assertSame('ABBVbml0VGVzdENsaWVudElkAAh1bnJlYWw0dQALanVzdFQzc3Qxbmc=', base64_encode($connectPayload));
    }

    public function test_passwordWithoutUsername()
    {
        $parameters = new Parameters('UnitTestClientId');
        $parameters->setPassword('justT3st1ng');

        $this->connect->setConnectionParameters($parameters);
        $this->expectException(MustProvideUsername::class);
        $this->connect->createPayload();
    }

    public function test_completeWill()
    {
        $message = new Message();
        $message->setPayload(new SimplePayload('Testing'));
        $message->setTopicName('topic');

        $parameters = new Parameters('UnitTestClientId');
        $parameters->setWill($message);
        $this->connect->setConnectionParameters($parameters);
        $connectPayload = $this->connect->createPayload();

        $this->assertSame(34, mb_strlen($connectPayload));
        $this->assertSame('ABBVbml0VGVzdENsaWVudElkAAV0b3BpYwAHVGVzdGluZw==', base64_encode($connectPayload));
    }
}
