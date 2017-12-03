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
/** @var \unreal4u\MQTT\Protocol\Connack $connack */
$connack = $client->sendData($connect);

var_dump(
    'connect return code:', $connack->connectReturnCode,
    'client is connected?:', $client->isConnected()
);
