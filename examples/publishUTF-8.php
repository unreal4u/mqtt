<?php

declare(strict_types=1);

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use unreal4u\MQTT\Application\Message;
use unreal4u\MQTT\Application\Topic;
use unreal4u\MQTT\Client;
use unreal4u\MQTT\Protocol\Connect;
use unreal4u\MQTT\Protocol\Connect\Parameters;
use unreal4u\MQTT\Protocol\Publish;

include __DIR__ . '/00.basics.php';

$logger = new Logger('main');
$logger->pushHandler(new StreamHandler('php://stdout', Logger::DEBUG));

$connect = new Connect();
$connect->setConnectionParameters(new Parameters('publishSomething'));

$client = new Client();
$client->sendData($connect);

$message = new Message();
$publish = new Publish($logger);

$message
    ->setTopic(new Topic(COMMON_TOPICNAME))
    ->setPayload('汉A字BC')#->setPayload(str_repeat('𠜎', 65534))
;
$publish->setMessage($message);
$client->sendData($publish);
echo PHP_EOL;
