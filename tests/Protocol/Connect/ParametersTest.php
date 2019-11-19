<?php

declare(strict_types=1);

namespace tests\unreal4u\MQTT\Connect;

use PHPUnit\Framework\TestCase;
use unreal4u\MQTT\DataTypes\BrokerPort;
use unreal4u\MQTT\DataTypes\ClientId;
use unreal4u\MQTT\DataTypes\Message;
use unreal4u\MQTT\DataTypes\QoSLevel;
use unreal4u\MQTT\DataTypes\TopicName;
use unreal4u\MQTT\Protocol\Connect\Parameters;

class ParametersTest extends TestCase
{
    public function testDefaultSettings(): void
    {
        $parameters = new Parameters();
        $this->assertSame('tcp://localhost:1883', $parameters->getConnectionUrl());
        $this->assertSame(2, $parameters->getFlags());
    }

    public function testSetHost(): void
    {
        $parameters = new Parameters(new ClientId('uniqueClientId'), '192.168.255.3');
        $this->assertSame('tcp://192.168.255.3:1883', $parameters->getConnectionUrl());
    }

    public function testSetHostWithDifferentBrokerPort(): void
    {
        $parameters = new Parameters(new ClientId('uniqueClientId'), '192.168.255.4');
        $parameters->setBrokerPort(new BrokerPort(5445));
        $this->assertSame('tcp://192.168.255.4:5445', $parameters->getConnectionUrl());
    }

    public function testSetNonUTF8ClientName(): void
    {
        $parameters = new Parameters(new ClientId('uniqueClientId𠜎𠜱WithComplexUTF8Chars'));
        $this->assertSame('uniqueClientId𠜎𠜱WithComplexUTF8Chars', (string)$parameters->getClientId());
    }

    public function testCreateObjectWithCleanSessionBit(): void
    {
        $parameters = new Parameters();
        $parameters->setCleanSession(true);

        // Basic validation: assert value is the same we have just set it to
        $this->assertTrue($parameters->getCleanSession());
        // Assert flags are the same
        $this->assertSame(2, $parameters->getFlags());
    }

    public function testCreateObjectWithMultipleOptions(): void
    {
        $parameters = new Parameters(new ClientId('SpecialClientId'));
        $parameters->setCleanSession(true);
        $this->assertSame(2, $parameters->getFlags());
        $parameters->setCredentials('unreal4u', 'justT3st1ng');
        $this->assertSame(194, $parameters->getFlags());

        $this->assertSame('unreal4u', $parameters->getUsername());
        $this->assertSame('justT3st1ng', $parameters->getPassword());
        $this->assertTrue($parameters->getCleanSession());
    }

    public function testRevertCleanSessionBit(): void
    {
        $parameters = new Parameters(new ClientId('unittest'));
        $parameters->setCleanSession(true);
        $this->assertSame(2, $parameters->getFlags());
        $parameters->setCleanSession(false);
        $this->assertSame(0, $parameters->getFlags());
    }

    public function testRevertCredentialBits(): void
    {
        $parameters = new Parameters(new ClientId('unittest'));
        $parameters->setCredentials('unreal4u', 'justT3st1ng');
        $this->assertSame(192, $parameters->getFlags());

        $parameters->setCredentials('', '');
        $this->assertSame(0, $parameters->getFlags());
    }

    /**
     * Tests whether setting up a will message goes correctly or not
     */
    public function testValidBasicWillMessage(): void
    {
        $parameters = new Parameters();
        $parameters->setWill(new Message(
            'You will see this if I disconnect without notice',
            new TopicName('client/errors')
        ));

        $this->assertSame(6, $parameters->getFlags());
        $this->assertFalse($parameters->getWillRetain());
    }

    public function testValidRetainedWillMessage(): void
    {
        $willMessage = new Message('You will see this if I disconnect without notice', new TopicName('client/errors'));
        $willMessage->setRetainFlag(true);

        $parameters = new Parameters();
        $parameters->setWill($willMessage);

        $this->assertSame(38, $parameters->getFlags());
        $this->assertTrue($parameters->getWillRetain());
    }

    public function providerValidQosLevelWillMessage(): array
    {
        $mapValues[] = [0, 4];
        $mapValues[] = [1, 12];
        $mapValues[] = [2, 20];

        return $mapValues;
    }

    /**
     * @dataProvider providerValidQosLevelWillMessage
     * @param int $QoSLevel
     * @param int $parameterFlagResult
     */
    public function testValidQoSLevelWillMessage(int $QoSLevel, int $parameterFlagResult): void
    {
        $willMessage = new Message('You will see this if I disconnect without notice', new TopicName('client/errors'));
        $willMessage->setQoSLevel(new QoSLevel($QoSLevel));

        $parameters = new Parameters(new ClientId('unittest'));
        $parameters->setWill($willMessage);

        $this->assertSame($parameterFlagResult, $parameters->getFlags());
    }

    public function testInvalidTimeoutSetting(): void
    {
        $parameters = new Parameters();
        $this->expectException(\InvalidArgumentException::class);
        $parameters->setKeepAlivePeriod(-10);
    }

    public function testValidTimeoutSetting(): void
    {
        $parameters = new Parameters();
        $parameters->setKeepAlivePeriod(65530);

        $this->assertSame(65530, $parameters->getKeepAlivePeriod());
    }
}
