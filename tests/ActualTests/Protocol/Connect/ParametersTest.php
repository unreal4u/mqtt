<?php

declare(strict_types=1);

namespace tests\unreal4u\MQTT\Connect;

use PHPUnit\Framework\TestCase;
use unreal4u\MQTT\Protocol\Connect\Parameters;

class ParametersTest extends TestCase
{
    public function test_emptyHost()
    {
        $this->expectException(\InvalidArgumentException::class);
        new Parameters('');
    }

    public function test_createDefaultObject()
    {
        $parameters = new Parameters('localhost');
        $this->assertSame(0, $parameters->getFlags());
    }

    public function provider_createObjectWithOptions(): array
    {
        $mapValues[] = ['Username', 'asdf', 128];
        $mapValues[] = ['Password', 'asdf', 64];
        $mapValues[] = ['WillRetain', true, 32];
        $mapValues[] = ['WillTopic', 'Random/Topic', 4];
        $mapValues[] = ['WillMessage', 'A random message', 4];
        $mapValues[] = ['CleanSession', true, 2];

        return $mapValues;
    }

    /**
     * @dataProvider provider_createObjectWithOptions
     * @param $key
     * @param $value
     * @param $expected
     */
    public function test_createObjectWithOptions($key, $value, $expected)
    {
        $parameters = new Parameters('localhost');
        $setter = 'set' . $key;
        $getter = 'get' . $key;
        $parameters->$setter($value);

        // Basic validation: assert value is the same we have just set it to
        $this->assertSame($value, $parameters->$getter());
        // Assert flags are the same
        $this->assertSame($expected, $parameters->getFlags());
    }

    public function test_createObjectWithMultipleOptions()
    {
        $parameters = new Parameters('localhost', 'SpecialClientId');
        $parameters->setCleanSession(true);
        $this->assertSame(2, $parameters->getFlags());
        $parameters->setUsername('unreal4u');
        $this->assertSame(130, $parameters->getFlags());
        $parameters->setPassword('justT3st1ng');
        $this->assertSame(194, $parameters->getFlags());
        $parameters->setWillRetain(true);
        $this->assertSame(226, $parameters->getFlags());

        // Set will message and topic set the same bit
        $parameters->setWillMessage('You will see this if I disconnect');
        $this->assertSame(230, $parameters->getFlags());
        $parameters->setWillTopic('client/errors');
        $this->assertSame(230, $parameters->getFlags());

        $this->assertSame('unreal4u', $parameters->getUsername());
        $this->assertSame('justT3st1ng', $parameters->getPassword());
        $this->assertTrue($parameters->getCleanSession());
    }

    public function provider_revertBits()
    {
        $mapValues[] = ['Username', 'unreal4u', '', 128];
        $mapValues[] = ['Password', 'justT3st1ng', '', 64];
        $mapValues[] = ['WillRetain', true, false, 32];
        $mapValues[] = ['WillTopic', 'client/errors', '', 4];
        $mapValues[] = ['CleanSession', true, false, 2];

        return $mapValues;
    }

    /**
     * @param string $key
     * @param $filledValue
     * @param $emptyValue
     * @param int $expectedBit
     * @dataProvider provider_revertBits
     */
    public function test_revertBits(string $key, $filledValue, $emptyValue, int $expectedBit)
    {
        $parameters = new Parameters('localhost');

        $setter = 'set' . $key;

        $parameters->$setter($filledValue);
        $this->assertSame($expectedBit, $parameters->getFlags());
        $parameters->$setter($emptyValue);
        $this->assertSame(0, $parameters->getFlags());
    }
}
