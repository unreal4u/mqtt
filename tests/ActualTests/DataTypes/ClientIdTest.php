<?php

declare(strict_types=1);

namespace tests\unreal4u\MQTT\DataTypes;

use PHPUnit\Framework\TestCase;
use unreal4u\MQTT\DataTypes\ClientId;

class ClientIdTest extends TestCase
{
    public function test_perfectClientId()
    {
        $clientId = new ClientId('test');

        $this->assertSame('test', $clientId->getClientId());
    }

    public function provider_warnedClientIds(): array
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
     * @dataProvider provider_warnedClientIds
     * @param string $clientIdString
     * @param string $errorMessage
     */
    public function test_warnedClientIds(string $clientIdString, string $errorMessage)
    {
        $clientId = new ClientId($clientIdString);

        foreach ($clientId->performStrictValidationCheck() as $generatedError) {
            $this->assertSame($errorMessage, $generatedError);
        }
    }
}
