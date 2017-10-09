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

if ($client->isConnected()) {
    $payload = new SimplePayload();
    $payload->setPayload('Hello world!!');

    $message = new Message();
    $message->setTopicName('firstTest');
    $message->setPayload($payload);
    #$message->setQoSLevel(1);

    $publish = new Publish();
    $publish->setMessage($message);

    $response = $client->sendData($publish);
    #var_dump($response);
}
