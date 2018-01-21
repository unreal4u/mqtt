<?php

/**
 * This example shows how to publish messages with the retain bit on
 */

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
    $publish = new Publish();

    // So basically, everything is pretty standard
    $message = new Message();
    $message->setTopic(new Topic(COMMON_TOPICNAME));
    // Except that we set the retain flag
    $message->setRetainFlag(true);
    $message->setPayload('Message from ' . $now->format('d-m-Y H:i:s') . ' will be retained');
    $publish->setMessage($message);
    $client->sendData($publish);

    // Exactly the same for the SECONDARY_TOPICNAME
    $message = new Message();
    $message->setTopic(new Topic(SECONDARY_TOPICNAME));
    $message->setRetainFlag(true);
    $message->setPayload('Message from ' . $now->format('d-m-Y H:i:s') . ' will be retained');
    $publish->setMessage($message);
    $client->sendData($publish);

    echo 'Both messages sent';
}
echo PHP_EOL;
