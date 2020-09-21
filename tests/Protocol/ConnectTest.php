<?php

/**
 * Tests the CONNECT object
 *
 * This was part of the actual first tests of this library, and because of that, it will contain some integration tests
 * instead of pure unit tests.
 *
 * I won't be removing these mainly because they are also useful and because the system as a whole should work well :)
 */

declare(strict_types=1);

namespace tests\unreal4u\MQTT;

use PHPUnit\Framework\TestCase;
use tests\unreal4u\MQTT\Mocks\ClientMock;
use unreal4u\MQTT\DataTypes\ClientId;
use unreal4u\MQTT\DataTypes\Message;
use unreal4u\MQTT\DataTypes\TopicName;
use unreal4u\MQTT\Exceptions\Connect\NoConnectionParametersDefined;
use unreal4u\MQTT\Exceptions\MustProvideUsername;
use unreal4u\MQTT\Protocol\ConnAck;
use unreal4u\MQTT\Protocol\Connect;
use unreal4u\MQTT\Protocol\Connect\Parameters;

class ConnectTest extends TestCase
{
    /**
     * @var Connect
     */
    private $connect;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connect = new Connect();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->connect = null;
    }

    public function testCreateVariableHeaderDefaultValues(): void
    {
        $this->connect->setConnectionParameters(new Parameters(new ClientId('UnitTestClientId')));
        $connectVariableHeader = $this->connect->createVariableHeader();
        $this->assertSame(10, \strlen($connectVariableHeader));
        $this->assertSame('AARNUVRUBAAAPA==', base64_encode($connectVariableHeader));
    }

    public function testUserAndPassword(): void
    {
        $parameters = new Parameters(new ClientId('UnitTestClientId'));
        $parameters->setCredentials('unreal4u', 'justT3st1ng');

        $this->connect->setConnectionParameters($parameters);
        $connectPayload = $this->connect->createPayload();

        $this->assertSame(41, \strlen($connectPayload));
        $this->assertSame('ABBVbml0VGVzdENsaWVudElkAAh1bnJlYWw0dQALanVzdFQzc3Qxbmc=', base64_encode($connectPayload));
    }

    public function testPasswordWithoutUsername(): void
    {
        $parameters = new Parameters(new ClientId('UnitTestClientId'));
        $parameters->setCredentials('', 'justT3st1ng');

        $this->connect->setConnectionParameters($parameters);
        $this->expectException(MustProvideUsername::class);
        $this->connect->createPayload();
    }

    public function testCompleteWill(): void
    {
        $parameters = new Parameters(new ClientId('UnitTestClientId'));
        $parameters->setWill(new Message('Testing', new TopicName('topic')));
        $this->connect->setConnectionParameters($parameters);
        $connectPayload = $this->connect->createPayload();

        $this->assertSame(34, \strlen($connectPayload));
        $this->assertSame('ABBVbml0VGVzdENsaWVudElkAAV0b3BpYwAHVGVzdGluZw==', base64_encode($connectPayload));
        $this->assertSame(
            'ECwABE1RVFQEBAA8ABBVbml0VGVzdENsaWVudElkAAV0b3BpYwAHVGVzdGluZw==',
            base64_encode($this->connect->createSendableMessage())
        );
    }

    public function testShouldExpectAnswer(): void
    {
        $this->assertTrue($this->connect->shouldExpectAnswer());
    }

    public function testNoConnectionParametersDefinedException(): void
    {
        $this->expectException(NoConnectionParametersDefined::class);
        $this->connect->getConnectionParameters();
    }

    public function testGetConnectionParameters(): void
    {
        $parameters = new Parameters(new ClientId('UnitTestClientId'));

        $this->connect->setConnectionParameters($parameters);
        $this->assertSame($parameters, $this->connect->getConnectionParameters());
    }

    public function testExpectAnswer(): void
    {
        $result = $this->connect->expectAnswer(base64_decode('IAIBAA=='), new ClientMock());
        $this->assertInstanceOf(ConnAck::class, $result);
    }
}
