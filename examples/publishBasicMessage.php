<?php

declare(strict_types = 1);

use unreal4u\MQTT\Application\Message;
use unreal4u\MQTT\Application\SimplePayload;
use unreal4u\MQTT\Client;
use unreal4u\MQTT\Protocol\Connect;
use unreal4u\MQTT\Protocol\Connect\Parameters;
use unreal4u\MQTT\Protocol\Publish;

include __DIR__ . '/00.basics.php';

$connect = new Connect();
$connect->setConnectionParameters(new Parameters('localhost', 'publishSomething'));

$client = new Client();
$client->sendData($connect);

define('MAXIMUM', 10);
if ($client->isConnected()) {
    $payload = new SimplePayload();
    $message = new Message();
    $message->setTopicName('firstTest');
    $publish = new Publish();

    for ($i = 1; $i <= MAXIMUM; $i++) {
        $payload->setPayload(sprintf('Hello world!! (%d / %d)', $i, MAXIMUM));
        $message->setPayload($payload);
        #$message->setQoSLevel(1);
        $publish->setMessage($message);
        $client->sendData($publish);
        echo '.';
    }
}
echo PHP_EOL;
