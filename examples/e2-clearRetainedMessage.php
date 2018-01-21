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

// Perform the following actions only if we are connected to the broker
if ($client->isConnected()) {
    // First, create a message object
    $message = new Message();
    // Set the topic
    $message->setTopic(new Topic(COMMON_TOPICNAME));
    // Set the retain flag to true
    $message->setRetainFlag(true);
    // Set the payload to an empty message (this will signal the broker to unset the retained message)
    $message->setPayload('');

    // Finally, make a Publish object
    $publish = new Publish();
    // Set the message
    $publish->setMessage($message);
    // And publish the object to the broker
    $client->sendData($publish);
    echo 'Cleared retained message';
}
echo PHP_EOL;
