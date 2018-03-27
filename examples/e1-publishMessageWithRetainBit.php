<?php

/**
 * This example shows how to publish messages with the retain bit on
 */

declare(strict_types=1);

use unreal4u\MQTT\Client;
use unreal4u\MQTT\DataTypes\ClientId;
use unreal4u\MQTT\DataTypes\Message;
use unreal4u\MQTT\DataTypes\Topic;
use unreal4u\MQTT\Protocol\Connect;
use unreal4u\MQTT\Protocol\Connect\Parameters;
use unreal4u\MQTT\Protocol\Publish;

include __DIR__ . '/00.basics.php';

$connectionParameters = new Parameters(new ClientId(basename(__FILE__)));
$connectionParameters->setCredentials('testuser', 'userpass');

$connect = new Connect();
$connect->setConnectionParameters($connectionParameters);

$client = new Client();
$client->processObject($connect);

$now = new \DateTimeImmutable('now');

if ($client->isConnected()) {
    $publish = new Publish();

    // So basically, everything is pretty standard
    $message = new Message(
        'Message from ' . $now->format('d-m-Y H:i:s') . ' will be retained',
        new Topic(COMMON_TOPICNAME)
    );
    // Except that we set the retain flag
    $message->setRetainFlag(true);
    $publish->setMessage($message);
    $client->processObject($publish);

    // Exactly the same, but for the SECONDARY_TOPICNAME
    $message = new Message(
        'Message from ' . $now->format('d-m-Y H:i:s') . ' will be retained',
        new Topic(SECONDARY_TOPICNAME)
    );
    // Except that we set the retain flag
    $message->setRetainFlag(true);
    $publish->setMessage($message);
    $client->processObject($publish);

    echo 'Both messages sent';
}
echo PHP_EOL;
