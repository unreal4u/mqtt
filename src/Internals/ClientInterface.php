<?php

declare(strict_types=1);

namespace unreal4u\MQTT\Internals;

use Psr\Log\LoggerInterface;
use unreal4u\MQTT\Client;

/**
 * Interface ClientInterface
 * @package unreal4u\MQTT\Internals
 */
interface ClientInterface
{
    /**
     * ClientInterface constructor.
     * @param LoggerInterface|null $logger
     */
    public function __construct(LoggerInterface $logger = null);

    /**
     * Give an opportunity to disconnect gracefully should this class be destroyed
     */
    public function __destruct();

    /**
     * Returns a direct connection to the socket
     *
     * @return Resource
     */
    public function getSocket();

    /**
     * Allows us to read an arbitrary number of bytes from the socket connection
     *
     * @param int $bytes
     * @return string
     */
    public function readSocketData(int $bytes): string;

    /**
     * The first 4 bytes will _always_ contain basic information with which we'll know what to do afterwards
     * @return string
     */
    public function readSocketHeader(): string;

    /**
     * Sends the data to the socket and waits for an answer from the broker
     *
     * @param WritableContentInterface $object
     * @return string
     */
    public function sendSocketData(WritableContentInterface $object): string;

    /**
     * Defines whether the following request(s) should block further processing
     *
     * If blocking should occur, we will wait for the socket to deliver some information. Some requests don't need a
     * confirmation, so enable those to just omit waiting for some answer to come back and give the control back to the
     * user as soon as possible.
     *
     * @param bool $newStatus
     * @return Client
     */
    public function setBlocking(bool $newStatus): ClientInterface;

    /**
     * Prepares and sends the given request to the MQTT broker
     *
     * @param WritableContentInterface $object
     * @return ReadableContentInterface
     */
    public function sendData(WritableContentInterface $object): ReadableContentInterface;

    /**
     * Will let us know if we are approaching the time limit in which the broker will disconnect us
     *
     * As per protocol, the broker will disconnect us after 1.5 times the configured keepAlive period, so it is safe to
     * assume that between the keep alive period and that same period * 1.5 we must send in a PINGREQ packet.
     *
     * @return bool
     */
    public function isItPingTime(): bool;

    /**
     * Updates the internal counter to know when was the last known communication with the MQTT broker
     *
     * This will create a timestamp with support for microseconds
     * @see https://gist.github.com/graste/47a4a6433dfe0acf64b7
     *
     * @return Client
     */
    public function updateLastCommunication(): ClientInterface;

    /**
     * Sets an easy bit for us to know whether we are connected to an MQTT broker or not
     *
     * @param bool $isConnected
     * @return Client
     */
    public function setConnected(bool $isConnected): ClientInterface;

    /**
     * Will return the status of the connection in an easy way
     *
     * @return bool
     */
    public function isConnected(): bool;
}
