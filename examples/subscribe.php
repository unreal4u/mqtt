<?php

declare(strict_types=1);

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use unreal4u\MQTT\Application\SimplePayload;
use unreal4u\MQTT\Client;
use unreal4u\MQTT\Protocol\Connect;
use unreal4u\MQTT\Protocol\Connect\Parameters;
use unreal4u\MQTT\Protocol\Subscribe;
use unreal4u\MQTT\Protocol\Subscribe\Topic;

include __DIR__ . '/00.basics.php';

$keepAlivePeriod = 5;
$disconnectAutomatically = false;

$logger = new Logger('main');
$logger->pushHandler(new StreamHandler('php://stdout', Logger::INFO));

$connectionParameters = new Parameters('subscribeToSomething');
$connectionParameters->setKeepAlivePeriod($keepAlivePeriod);
$connectionParameters->setUsername('testuser');
$connectionParameters->setPassword('userpass');

$connect = new Connect($logger);
$connect->setConnectionParameters($connectionParameters);

$client = new Client($logger);
$client->sendData($connect);

if ($client->isConnected() === false) {
    throw new DomainException('We are not connected, can not subscribe');
}

$mainTopic = new Topic(COMMON_TOPICNAME);
$secondaryTopic = new Topic(SECONDARY_TOPICNAME);

$subscribe = new Subscribe($logger);
$subscribe->addTopics($mainTopic, $secondaryTopic);
foreach ($subscribe->loop($client, new SimplePayload()) as $message) {
    printf(
        '%s-- Payload detected on topic "%s": %s + %s%s',
        PHP_EOL,
        $message->getTopicName(),
        PHP_EOL,
        $message->getPayload(),
        PHP_EOL
    );
}
