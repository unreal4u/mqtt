<?php

/*
 * QoS level 1 and 2 refer to the confirmation the sent message is received by the other party, either broker or client
 *
 * In the first case and provided a QoS level 1, if we sent a message to the broker, the broker will respond with a
 * confirmation message (PubAck) and within that, the packetIdentifier which must match the packetIdentifier we sent to
 * the broker during Publish.
 */

declare(strict_types = 1);

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use unreal4u\MQTT\Application\Message;
use unreal4u\MQTT\Application\SimplePayload;
use unreal4u\MQTT\Client;
use unreal4u\MQTT\Protocol\Connect;
use unreal4u\MQTT\Protocol\Connect\Parameters;
use unreal4u\MQTT\Protocol\Publish;

include __DIR__ . '/00.basics.php';

$logger = new Logger('main');
$logger->pushHandler(new StreamHandler('php://stdout', Logger::DEBUG));

$connectionParameters = new Parameters('publishSomething');
$connectionParameters->setUsername('testuser');
$connectionParameters->setPassword('userpass');

$connect = new Connect($logger);
$connect->setConnectionParameters($connectionParameters);

$client = new Client($logger);
$client->sendData($connect);

define('MAXIMUM', 3);
if ($client->isConnected()) {
    $payload = new SimplePayload();
    $message = new Message();
    $message->setTopicName(COMMON_TOPICNAME);
    // QoS level is set per message, so set it here
    $message->setQoSLevel(1);
    $publish = new Publish($logger);

    for ($i = 1; $i <= MAXIMUM; $i++) {
        $payload->setPayload(sprintf('Hello world!! (%d / %d)', $i, MAXIMUM));
        $message->setPayload($payload);
        $publish->setMessage($message);
        // The client will perform the check whether the packet identifier is correctly set or not
        $client->sendData($publish);
        echo '.';
    }
}
echo PHP_EOL;
