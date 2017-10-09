<?php

declare(strict_types=1);

namespace unreal4u\MQTT\Internals;

use unreal4u\MQTT\Exceptions\InvalidResponseType;

trait ReadableContent
{
    /**
     * The raw MQTT headers that initialized this object
     * @var string
     */
    protected $rawMQTTHeaders = '';

    /**
     * Length of variable header
     * @var int
     */
    protected $variableHeaderSize = 0;

    final public function populate(string $rawMQTTHeaders): ReadableContentInterface
    {
        $this->rawMQTTHeaders = $rawMQTTHeaders;
        $this->checkControlPacketValue()->fillObject();
        return $this;
    }

    final public function checkControlPacketValue(): ReadableContentInterface
    {
        // Check whether the first byte corresponds to the expected control packet value
        if ($this->rawMQTTHeaders !== '' && static::CONTROL_PACKET_VALUE !== (ord($this->rawMQTTHeaders[0]) >> 4)) {
            throw new InvalidResponseType(sprintf(
                'Value of received value does not correspond to response (Expected: %d, Actual: %d)',
                static::CONTROL_PACKET_VALUE,
                ord($this->rawMQTTHeaders[0]) >> 4
            ));
        }

        return $this;
    }

    /**
     * Returns the number of bytes we'll have to read out from
     * @return int
     */
    final public function readVariableHeader(): int
    {
        // TODO
    }
}
