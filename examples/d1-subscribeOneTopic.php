<?php

/**
 * This example will show you how to connect to a single topic, it is the most basic example for a subscription
 */

declare(strict_types=1);

use unreal4u\MQTT\Client;
use unreal4u\MQTT\DataTypes\QoSLevel;
use unreal4u\MQTT\Application\Topic;
use unreal4u\MQTT\Protocol\Connect;
use unreal4u\MQTT\Protocol\Connect\Parameters;
use unreal4u\MQTT\Protocol\Subscribe;

include __DIR__ . '/00.basics.php';

// First, we must connect to the broker
$connect = new Connect();
$connect->setConnectionParameters(new Parameters('SubscribeOneTopic'));

$client = new Client();
$client->processObject($connect);

// Then, we will initialize a new subscription
$subscribe = new Subscribe();
// Adding a certain topic is done by providing a Topic object to the addTopics() method of the subscription
$subscribe->addTopics(new Topic(COMMON_TOPICNAME, new QoSLevel(0)));

// Handy function: a loop. This will yield any messages that arrive at the topic.
/** @var \unreal4u\MQTT\Application\Message $message */
foreach ($subscribe->loop($client) as $message) {
    // Now that we have a message, we can do whatever we like with it
    printf(
        '%s-- Payload detected on topic "%s": %s + %s%s',
        PHP_EOL,
        $message->getTopicName(),
        PHP_EOL,
        $message->getPayload(),
        PHP_EOL
    );
}
