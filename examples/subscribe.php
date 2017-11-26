<?php

declare(strict_types=1);

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use unreal4u\MQTT\Client;
use unreal4u\MQTT\Protocol\Connect;
use unreal4u\MQTT\Protocol\Connect\Parameters;
use unreal4u\MQTT\Protocol\Publish;
use unreal4u\MQTT\Protocol\Subscribe;

include __DIR__ . '/00.basics.php';

$keepAlivePeriod = 5;
$disconnectAutomatically = true;

$logger = new Logger('main');
$logger->pushHandler(new StreamHandler('php://stdout', Logger::DEBUG));

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
$subscribe->topic = 'firstTest';

$client->sendData($subscribe);
$now = time();
$shouldStayConnected = true;
while ($shouldStayConnected) {
    echo '.';
    $event = $subscribe->checkForEvent($client);
    if ($event instanceof Publish) {
        var_dump($event->getMessage()->getPayload());
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
