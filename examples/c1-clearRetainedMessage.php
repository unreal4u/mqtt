<?php

declare(strict_types=1);

use unreal4u\MQTT\DataTypes\ClientId;
use unreal4u\MQTT\DataTypes\Message;
use unreal4u\MQTT\Client;
use unreal4u\MQTT\DataTypes\TopicName;
use unreal4u\MQTT\Protocol\Connect;
use unreal4u\MQTT\Protocol\Connect\Parameters;
use unreal4u\MQTT\Protocol\Publish;

include __DIR__ . '/00.basics.php';

$connect = new Connect();
$connect->setConnectionParameters(new Parameters(new ClientId(basename(__FILE__)), BROKER_HOST));

$client = new Client();
$client->processObject($connect);

$now = new \DateTimeImmutable('now');

// Perform the following actions only if we are connected to the broker
if ($client->isConnected()) {
    // Set the payload to an empty message (this will signal the broker to unset the retained message)
    $message = new Message('', new TopicName(COMMON_TOPICNAME));
    // Set the retain flag to true
    $message->setRetainFlag(true);

    // Finally, make a Publish object
    $publish = new Publish();
    // Set the message
    $publish->setMessage($message);
    // And publish the object to the broker
    $client->processObject($publish);
    printf('Cleared retained message on topic "%s"', COMMON_TOPICNAME);
}
echo PHP_EOL;
