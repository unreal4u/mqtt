<?php

/**
 * Example file of how to connect to a broker
 */

declare(strict_types=1);

use unreal4u\MQTT\Client;
use unreal4u\MQTT\DataTypes\ClientId;
use unreal4u\MQTT\Protocol\Connect;
use unreal4u\MQTT\Protocol\Connect\Parameters;

include __DIR__ . '/00.basics.php';

// Instantiate the client
$client = new Client();

// Create a Connect object
$connect = new Connect();
// Set the most basic parameters possible: send just a ClientId
$connect->setConnectionParameters(new Parameters(new ClientId('uniqueClientId123'), BROKER_HOST));

// Example of invalid protocol which will throw an exception:
/** @var \unreal4u\MQTT\Protocol\ConnAck $connAck */
$connAck = $client->processObject($connect);

printf(
    'Connection return code: %d.%sClient is connected: %s',
    $connAck->getConnectReturnCode(),
    PHP_EOL,
    $client->isConnected() ? 'true' : 'false'
);
