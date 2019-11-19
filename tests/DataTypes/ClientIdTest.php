<?php

declare(strict_types=1);

namespace tests\unreal4u\MQTT\DataTypes;

use PHPUnit\Framework\TestCase;
use unreal4u\MQTT\DataTypes\ClientId;

class ClientIdTest extends TestCase
{
    public function testPerfectClientId(): void
    {
        $clientId = new ClientId('test');
        $this->assertSame('test', $clientId->getClientId());
        // Assert that printing the object as a string will deliver the same result as above
        $this->assertSame('test', (string)$clientId);
    }

    public function providerWarnedClientIds(): array
    {
        $mapValues[] = ['', 'ClientId size is 0 bytes. This has several implications, check comments'];
        $mapValues[] = [
            'abcdefghijklmnopqrstuvwxyz',
            'The broker MAY reject the connection because the ClientId is too long'
        ];
        $mapValues[] = ['ClientId𠜎𠜱UTF8Chars', 'The broker MAY reject the connection because of invalid characters'];

        return $mapValues;
    }

    /**
     * @dataProvider providerWarnedClientIds
     * @param string $clientIdString
     * @param string $errorMessage
     */
    public function testWarnedClientIds(string $clientIdString, string $errorMessage): void
    {
        $clientId = new ClientId($clientIdString);

        foreach ($clientId->performStrictValidationCheck() as $generatedError) {
            $this->assertSame($errorMessage, $generatedError);
        }
    }
}
