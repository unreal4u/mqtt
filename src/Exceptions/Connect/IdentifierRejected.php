<?php

namespace unreal4u\MQTT\Exceptions\Connect;

class IdentifierRejected extends \InvalidArgumentException
{
    /**
     * @var string
     */
    private $possibleReason = '';

    public function fillPossibleReason(string $possibleReason): self
    {
        $this->possibleReason = $possibleReason;
        return $this;
    }

    public function getPossibleReason(): string
    {
        return $this->possibleReason;
    }
}
