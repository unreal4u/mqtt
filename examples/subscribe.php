<?php

declare(strict_types=1);

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use unreal4u\MQTT\Application\SimplePayload;
use unreal4u\MQTT\Client;
use unreal4u\MQTT\Protocol\Connect;
use unreal4u\MQTT\Protocol\Connect\Parameters;
use unreal4u\MQTT\Protocol\Publish;
use unreal4u\MQTT\Protocol\Subscribe;

include __DIR__ . '/00.basics.php';

$keepAlivePeriod = 5;
$disconnectAutomatically = false;

$logger = new Logger('main');
$logger->pushHandler(new StreamHandler('php://stdout', Logger::ERROR));

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

$subscribe = new Subscribe($logger);
$subscribe->topic = COMMON_TOPICNAME;

$client->sendData($subscribe);
$now = time();
$shouldStayConnected = true;
while ($shouldStayConnected) {
    // To show some progress, we print out a dot every time we check for an event
    echo '.';
    $event = $subscribe->checkForEvent($client, new SimplePayload());
    if ($event instanceof Publish) {
        printf(
            '%s-- Payload detected on topic "%s": %s + %s%s',
            PHP_EOL,
            $event->getMessage()->getTopicName(),
            PHP_EOL,
            $event->getMessage()->getPayload(),
            PHP_EOL
        );
    } else {
        // Only wait if there was nothing in the queue
        usleep(100000);
    }

    if ($disconnectAutomatically === true && time() - $now > ($keepAlivePeriod * 2)) {
        $shouldStayConnected = false;
        $logger->emergency('Development fail-safe: disconnecting automatically');
        printf(
            '%s-------------- Development fail-safe: disconnecting now! Check flags ------------------%s%s',
            PHP_EOL, PHP_EOL, PHP_EOL
        );
    }
}
