<?php

declare(strict_types=1);

namespace unreal4u\MQTT\Application;

/**
 * In order to add functionality to an Application Message, you can implement this Interface so that it complies to the
 * basic stuff this will do.
 * @package unreal4u\MQTT\Application
 */
interface PayloadInterface
{
    /**
     * When called with contents, it should set the payload to that content
     * @param string $messageContents
     */
    public function __construct(string $messageContents = null);

    /**
     * Called by the user with the contents we want the message to be filled with
     *
     * @param string $contents
     * @return PayloadInterface
     */
    public function setPayload(string $contents): PayloadInterface;

    /**
     * Should always return the raw payload without processing
     * @return string
     */
    public function getPayload(): string;

    /**
     * Any transformation to the payload can be done here
     *
     * @return string
     */
    public function getProcessedPayload(): string;

    /**
     * Called when we are filling in an incoming payload, should do the reverse of getProcessedPayload
     *
     * @param string $contents
     * @return PayloadInterface
     */
    public function processIncomingPayload(string $contents): PayloadInterface;
}
