<?php

declare(strict_types = 1);

use unreal4u\MQTT\Client;
use unreal4u\MQTT\Protocol\Connect;
use unreal4u\MQTT\Protocol\Connect\Parameters;
use unreal4u\MQTT\Application\SimplePayload;
use unreal4u\MQTT\Application\Message;

include __DIR__ . '/00.basics.php';

$willMessage = new Message();
$willMessage->setPayload(new SimplePayload('If I die unexpectedly, please print this message'));
$willMessage->setTopicName('client/errors');

$parameters = new Parameters('uniqueClientId123');
$parameters->setWill($willMessage);
$connect = new Connect();
$connect->setConnectionParameters($parameters);
// Example of invalid protocol which will throw an exception:
#$connect->protocolLevel = '0.0.1';
/** @var \unreal4u\MQTT\Protocol\ConnAck $connAck */
$client = new Client();
$connAck = $client->sendData($connect);

var_dump(
    'connect return code:', $connAck->connectReturnCode,
    'client is connected?:', $client->isConnected()
);

for ($i = 0; $i < 3; $i++) {
    sleep(1);
    if ($i === 2) {
        throw new \LogicException('Throwing an exception unexpectedly will not trigger the destructor');
    }
}
