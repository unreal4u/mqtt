<?php

declare(strict_types=1);

namespace unreal4u\MQTT\Internals;

use Psr\Log\LoggerInterface;

interface WritableContentInterface
{
    /**
     * Ensure to provide a logger on the constructor
     * @param LoggerInterface|null $logger
     */
    public function __construct(LoggerInterface $logger = null);

    /**
     * Creates the fixed header each method has
     *
     * @param int $variableHeaderLength
     * @return string
     */
    public function createFixedHeader(int $variableHeaderLength): string;

    /**
     * Creates the variable header that each method has
     * @return string
     */
    public function createVariableHeader(): string;

    /**
     * Creates the actual payload to be sent
     * @return string
     */
    public function createPayload(): string;

    /**
     * What specific kind of post we should expect back from this request
     *
     * @param string $data
     * @param ClientInterface $client
     * @return ReadableContentInterface
     */
    public function expectAnswer(string $data, ClientInterface $client): ReadableContentInterface;

    /**
     * Some responses won't expect an answer back, others do in some situations
     * @return bool
     */
    public function shouldExpectAnswer(): bool;

    /**
     * Creates the message to be sent
     * @return string
     */
    public function createSendableMessage(): string;
}
