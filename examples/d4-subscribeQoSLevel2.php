<?php

/**
 * Example file of a subscription listening only to level 2 QoS messages
 */

declare(strict_types=1);

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use unreal4u\MQTT\Client;
use unreal4u\MQTT\DataTypes\ClientId;
use unreal4u\MQTT\DataTypes\TopicFilter;
use unreal4u\MQTT\Protocol\Connect;
use unreal4u\MQTT\Protocol\Connect\Parameters;
use unreal4u\MQTT\Protocol\Subscribe;

include __DIR__ . '/00.basics.php';

$logger = new Logger('main');
$logger->pushHandler(new StreamHandler('php://stdout', Logger::DEBUG));

$connectionParameters = new Parameters(new ClientId(basename(__FILE__)));
$connectionParameters->setCredentials('testuser', 'userpass');
$connectionParameters->setKeepAlivePeriod(6);

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

$subscribe = new Subscribe($logger);
$subscribe->addTopics(new TopicFilter(COMMON_TOPICNAME));

/** @var \unreal4u\MQTT\DataTypes\Message $message */
foreach ($subscribe->loop($client) as $message) {
    // Any message here should NOT be within the SECONDARY_TOPICNAME topic
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
