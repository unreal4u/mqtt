<?php

declare(strict_types=1);

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use unreal4u\MQTT\Application\Topic;
use unreal4u\MQTT\Client;
use unreal4u\MQTT\Protocol\Connect;
use unreal4u\MQTT\Protocol\Connect\Parameters;
use unreal4u\MQTT\Protocol\Subscribe;
use unreal4u\MQTT\Protocol\Unsubscribe;

include __DIR__ . '/00.basics.php';

$logger = new Logger('main');
$logger->pushHandler(new StreamHandler('php://stdout', Logger::DEBUG));

$connectionParameters = new Parameters('subscribeAndUnsubscribe');
$connectionParameters->setUsername('testuser');
$connectionParameters->setPassword('userpass');

$connect = new Connect();
$connect->setConnectionParameters($connectionParameters);

try {
    $client = new Client($logger);
    $client->sendData($connect);
} catch (\Exception $e) {
    printf($e->getMessage());
    die();
}
$logger->info('Client connected, continuing...');

if ($client->isConnected() === false) {
    throw new DomainException('We are not connected, can not subscribe');
}

$mainTopic = new Topic(COMMON_TOPICNAME);
$secondaryTopic = new Topic(SECONDARY_TOPICNAME);

$subscribe = new Subscribe($logger);
$subscribe->addTopics($mainTopic, $secondaryTopic);
/** @var \unreal4u\MQTT\Application\Message $message */
foreach ($subscribe->loop($client, 100000, function (LoggerInterface $logger) use ($client) {
    $unsubscribe = new Unsubscribe($logger);
    $unsubscribe->addTopics(new Topic(COMMON_TOPICNAME));
    $client->sendData($unsubscribe);
}) as $message) {
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
