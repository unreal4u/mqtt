<?php

declare(strict_types = 1);

use unreal4u\MQTT\Client;
use unreal4u\MQTT\Protocol\Connect;
use unreal4u\MQTT\Protocol\Connect\Parameters;

include __DIR__ . '/00.basics.php';

$client = new Client();

$connect = new Connect();
$connect->setConnectionParameters(new Parameters('uniqueClientId123'));

// Example of invalid protocol which will throw an exception:
/** @var \unreal4u\MQTT\Protocol\ConnAck $connAck */
$connAck = $client->sendData($connect);

var_dump(
    'connect return code:', $connAck->connectReturnCode,
    'client is connected?:', $client->isConnected()
);
