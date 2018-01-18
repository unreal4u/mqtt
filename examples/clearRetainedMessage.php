<?php

declare(strict_types=1);

use unreal4u\MQTT\Application\Message;
use unreal4u\MQTT\Application\Topic;
use unreal4u\MQTT\Client;
use unreal4u\MQTT\Protocol\Connect;
use unreal4u\MQTT\Protocol\Connect\Parameters;
use unreal4u\MQTT\Protocol\Publish;

include __DIR__ . '/00.basics.php';

$connectionParameters = new Parameters('publishSomething');
$connectionParameters->setUsername('testuser');
$connectionParameters->setPassword('userpass');

$connect = new Connect();
$connect->setConnectionParameters($connectionParameters);

$client = new Client();
$client->sendData($connect);

$now = new \DateTimeImmutable('now');

if ($client->isConnected()) {
    $message = new Message();
    $message->setTopic(new Topic(COMMON_TOPICNAME));
    $message->setRetainFlag(true);
    $message->setPayload('');
    $publish = new Publish();
    $publish->setMessage($message);
    $client->sendData($publish);
    echo 'Cleared retained message';
}
echo PHP_EOL;
