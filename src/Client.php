<?php

declare(strict_types=1);

namespace unreal4u\MQTT;

use unreal4u\MQTT\Application\EmptyWritableResponse;
use unreal4u\MQTT\Exceptions\NotConnected;
use unreal4u\MQTT\Exceptions\ServerClosedConnection;
use unreal4u\MQTT\Internals\ClientInterface;
use unreal4u\MQTT\Internals\ProtocolBase;
use unreal4u\MQTT\Internals\ReadableContentInterface;
use unreal4u\MQTT\Internals\WritableContentInterface;
use unreal4u\MQTT\Protocol\Connect;
use unreal4u\MQTT\Protocol\Disconnect;

/**
 * Class Client
 * @package unreal4u\MQTT
 */
final class Client extends ProtocolBase implements ClientInterface
{
    /**
     * Where all the magic happens
     * @var Resource
     */
    private $socket;

    /**
     * Fast way to know whether we are connected or not
     * @var bool
     */
    private $isConnected = false;

    /**
     * Fast way to know whether we are currently in locked mode or not
     * @var bool
     */
    private $isCurrentlyLocked = false;

    /**
     * Annotates the last time there was known to be communication with the MQTT server
     * @var \DateTimeImmutable
     */
    private $lastCommunication;

    /**
     * Internal holder of connection parameters
     * @var Connect\Parameters
     */
    private $connectionParameters;

    /**
     * Temporary holder for async requests so that they can be handled synchronously
     * @var WritableContentInterface[]
     */
    private $objectStack = [];

    /**
     * @inheritdoc
     * @throws \LogicException
     * @throws \unreal4u\MQTT\Exceptions\NonMatchingPacketIdentifiers
     * @throws \unreal4u\MQTT\Exceptions\NotConnected
     * @throws \unreal4u\MQTT\Exceptions\Connect\NoConnectionParametersDefined
     * @throws \unreal4u\MQTT\Exceptions\ServerClosedConnection
     */
    public function __destruct()
    {
        if ($this->isConnected() === true) {
            $this->logger->info('Currently connected to broker, disconnecting from it');

            $this->processObject(new Disconnect($this->logger));
        }
    }

    /**
     * @inheritdoc
     */
    public function shutdownConnection(): bool
    {
        return stream_socket_shutdown($this->socket, STREAM_SHUT_RDWR);
    }

    /**
     * @inheritdoc
     */
    public function readBrokerData(int $bytes): string
    {
        $this->logger->debug('Reading bytes from socket', [
            'numberOfBytes' => $bytes,
            'isLocked' => $this->isCurrentlyLocked,
        ]);
        return fread($this->socket, $bytes);
    }

    /**
     * @inheritdoc
     */
    public function readBrokerHeader(): string
    {
        $this->logger->debug('Reading header from response');
        return $this->readBrokerData(4);
    }

    /**
     * @inheritdoc
     * @throws \unreal4u\MQTT\Exceptions\ServerClosedConnection
     * @throws \unreal4u\MQTT\Exceptions\NotConnected
     */
    public function sendBrokerData(WritableContentInterface $object): string
    {
        if ($this->socket === null) {
            $this->logger->alert('Not connected before sending data');
            throw new NotConnected('Please connect before performing any other request');
        }

        $writableString = $object->createSendableMessage();
        $sizeOfString = \strlen($writableString);
        $writtenBytes = fwrite($this->socket, $writableString, $sizeOfString);
        // $this->logger->debug('Sent string', ['binaryString' => str2bin($writableString)]); // Handy for debugging
        if ($writtenBytes !== $sizeOfString) {
            $this->logger->error('Written bytes do NOT correspond with size of string!', [
                'writtenBytes' => $writtenBytes,
                'sizeOfString' => $sizeOfString,
            ]);

            throw new ServerClosedConnection('The server may have disconnected the current client');
        }

        $this->logger->debug('Sent data to socket', ['writtenBytes' => $writtenBytes, 'sizeOfString' => $sizeOfString]);
        return $this->checkAndReturnAnswer($object);
    }

    /**
     * Checks on the writable object whether we should wait for an answer and either wait or return an empty string
     *
     * @param WritableContentInterface $object
     * @return string
     */
    private function checkAndReturnAnswer(WritableContentInterface $object): string
    {
        $returnValue = '';
        if ($object->shouldExpectAnswer() === true) {
            $this->enableSynchronousTransfer(true);
            $returnValue = $this->readBrokerHeader();
            $this->enableSynchronousTransfer(false);
        }

        return $returnValue;
    }

    /**
     * Checks for socket error connections, will throw an exception if any is found
     *
     * @param int $errorCode
     * @param string $errorDescription
     * @return Client
     * @throws \unreal4u\MQTT\Exceptions\NotConnected
     */
    private function checkForConnectionErrors(int $errorCode, string $errorDescription): self
    {
        if ($errorCode !== 0) {
            $this->logger->critical('Could not connect to broker', [
                'errorCode' => $errorCode,
                'errorDescription' => $errorDescription,
            ]);

            throw new NotConnected('Could not connect to broker: ' . $errorDescription, $errorCode);
        }

        return $this;
    }

    /**
     * Special handling of the connect part: create the socket
     *
     * @param Connect $connection
     * @return bool
     * @throws \unreal4u\MQTT\Exceptions\NotConnected
     * @throws \unreal4u\MQTT\Exceptions\Connect\NoConnectionParametersDefined
     */
    private function generateSocketConnection(Connect $connection): bool
    {
        $this->logger->debug('Creating socket connection');
        $this->connectionParameters = $connection->getConnectionParameters();
        $this->socket = stream_socket_client(
            $this->connectionParameters->getConnectionUrl(),
            $errorCode,
            $errorDescription,
            60,
            STREAM_CLIENT_CONNECT
        );

        $this->checkForConnectionErrors($errorCode, $errorDescription);

        stream_set_timeout($this->socket, (int)floor($this->connectionParameters->getKeepAlivePeriod() * 1.5));

        $this->logger->debug('Created socket connection successfully, continuing', stream_get_meta_data($this->socket));
        return true;
    }

    /**
     * @inheritdoc
     */
    public function enableSynchronousTransfer(bool $newStatus): ClientInterface
    {
        $this->logger->debug('Setting new blocking status', ['newStatus' => $newStatus]);
        stream_set_blocking($this->socket, $newStatus);
        $this->isCurrentlyLocked = $newStatus;
        return $this;
    }

    /**
     * Stuff that has to happen before we actually begin sending data through our socket
     *
     * @param WritableContentInterface $object
     * @return Client
     * @throws \unreal4u\MQTT\Exceptions\NotConnected
     * @throws \unreal4u\MQTT\Exceptions\Connect\NoConnectionParametersDefined
     */
    private function preSocketCommunication(WritableContentInterface $object): self
    {
        $this->objectStack[$object::getControlPacketValue()] = $object;

        if ($object instanceof Connect) {
            $this->generateSocketConnection($object);
        }

        return $this;
    }

    /**
     * Checks in the object stack whether there is some method that might issue the current ReadableContent
     *
     * @param ReadableContentInterface $readableContent
     * @return WritableContentInterface
     * @throws \LogicException
     */
    private function postSocketCommunication(ReadableContentInterface $readableContent): WritableContentInterface
    {
        $originPacket = null;

        $originPacketIdentifier = $readableContent->getOriginControlPacket();
        if (array_key_exists($originPacketIdentifier, $this->objectStack)) {
            $this->logger->debug('Origin packet found, returning it', ['originKey' => $originPacketIdentifier]);
            $originPacket = $this->objectStack[$originPacketIdentifier];
            unset($this->objectStack[$originPacketIdentifier]);
        } elseif ($originPacketIdentifier === 0) {
            $originPacket = new EmptyWritableResponse($this->logger);
        } else {
            $this->logger->warning('No origin packet found!', [
                'originKey' => $originPacketIdentifier,
                'stack' => array_keys($this->objectStack),
            ]);
            #throw new \LogicException('No origin instance could be found in the stack, please check');
            $originPacket = new EmptyWritableResponse($this->logger);
        }

        return $originPacket;
    }

    /**
     * @inheritdoc
     * @throws \LogicException
     * @throws \unreal4u\MQTT\Exceptions\NonMatchingPacketIdentifiers
     * @throws \unreal4u\MQTT\Exceptions\ServerClosedConnection
     * @throws \unreal4u\MQTT\Exceptions\Connect\NoConnectionParametersDefined
     * @throws \unreal4u\MQTT\Exceptions\NotConnected
     */
    public function processObject(WritableContentInterface $object): ReadableContentInterface
    {
        $currentObject = \get_class($object);
        $this->logger->debug('Validating object', ['object' => $currentObject]);

        $this->preSocketCommunication($object);

        $this->logger->info('About to send data', ['object' => $currentObject]);
        $readableContent = $object->expectAnswer($this->sendBrokerData($object), $this);
        /*
         * Some objects must perform certain actions on the connection, for example:
         * - ConnAck must set the connected bit
         * - PingResp must reset the internal last-communication datetime
         */
        $this->logger->debug('Checking stack and performing special operations', [
            'originObject' => $currentObject,
            'responseObject' => \get_class($readableContent),
        ]);

        $readableContent->performSpecialActions($this, $this->postSocketCommunication($readableContent));

        return $readableContent;
    }

    /**
     * @inheritdoc
     */
    public function isItPingTime(): bool
    {
        $secondsDifference = (new \DateTime('now'))->getTimestamp() - $this->lastCommunication->getTimestamp();
        $this->logger->debug('Checking time difference', [
            'secondsDifference' => $secondsDifference,
            'keepAlivePeriod' => $this->connectionParameters->getKeepAlivePeriod(),
        ]);

        return
            $this->isConnected() &&
            $this->connectionParameters->getKeepAlivePeriod() > 0 &&
            $secondsDifference >= $this->connectionParameters->getKeepAlivePeriod()# &&
            #!array_key_exists(PingReq::CONTROL_PACKET_VALUE, $this->objectStack)
            ;
    }

    /**
     * @inheritdoc
     */
    public function updateLastCommunication(): ClientInterface
    {
        $lastCommunication = null;
        if ($this->lastCommunication !== null) {
            $lastCommunication = $this->lastCommunication->format('Y-m-d H:i:s.u');
        }
        // "now" does not support microseconds, so create the timestamp with a format that does
        $this->lastCommunication = \DateTimeImmutable::createFromFormat('U.u', sprintf('%.6F', microtime(true)));
        $this->logger->debug('Updating internal last communication timestamp', [
            'previousValue' => $lastCommunication,
            'currentValue' => $this->lastCommunication->format('Y-m-d H:i:s.u'),
        ]);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setConnected(bool $isConnected): ClientInterface
    {
        $this->logger->debug('Setting internal connected property', ['connected' => $isConnected]);
        $this->isConnected = $isConnected;
        if ($this->isConnected() === false) {
            $this->socket = null;
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function isConnected(): bool
    {
        return $this->isConnected;
    }
}
