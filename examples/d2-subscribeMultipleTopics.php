<?php

/**
 * The subscription to multiple topics is basically the same as a single topic
 */

declare(strict_types=1);

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use unreal4u\MQTT\Client;
use unreal4u\MQTT\DataTypes\Topic;
use unreal4u\MQTT\Protocol\Connect;
use unreal4u\MQTT\Protocol\Connect\Parameters;
use unreal4u\MQTT\Protocol\Subscribe;

include __DIR__ . '/00.basics.php';

$logger = new Logger('main');
$logger->pushHandler(new StreamHandler('php://stdout', Logger::DEBUG));

$connectionParameters = new Parameters('subscribeToSomething');
// Keep alive period is used for connections that must ping the broker more or less frequently. It defaults to 60 secs.
$connectionParameters->setKeepAlivePeriod(5);
$connectionParameters->setUsername('testuser');
$connectionParameters->setPassword('userpass');

$connect = new Connect();
$connect->setConnectionParameters($connectionParameters);

try {
    $client = new Client($logger);
    $client->processObject($connect);
} catch (\Exception $e) {
    printf($e->getMessage());
    die();
}
$logger->info('Client connected, continuing...');

if ($client->isConnected() === false) {
    throw new DomainException('We are not connected, can not subscribe');
}

// We are subscribing in this example to 2 topics, so initialize the objects:
$mainTopic = new Topic(COMMON_TOPICNAME);
$secondaryTopic = new Topic(SECONDARY_TOPICNAME);

$subscribe = new Subscribe($logger);
// And provide multiple arguments to the subscription's addTopics() method
$subscribe->addTopics($mainTopic, $secondaryTopic);
/** @var \unreal4u\MQTT\Application\Message $message */
foreach ($subscribe->loop($client) as $message) {
    /*
     * Given the following conditions:
     *
     * - A retained message is present at the broker when subscribing
     * - You are subscribing to multiple topics in one go
     * - QoS level of the retained message is less than 2
     *
     * Then one in about ten attempts to subscribe will return as first message a possibly retained PUBLISH message,
     * following a SubAck and then the same published retained(?) message again. This isn't a bug of this library, but
     * rather an implementation detail of the MQTT protocol: a message may arrive or be sent out multiple times.
     */
    printf(
        '%s-- Payload detected on topic "%s" (QoS lvl %d): %s + %s%s',
        PHP_EOL,
        $message->getTopicName(),
        $message->getQoSLevel(),
        PHP_EOL,
        $message->getPayload(),
        PHP_EOL
    );
}
