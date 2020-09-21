<?php

namespace unreal4u\MQTT\Exceptions;

class InvalidResponseType extends \InvalidArgumentException
{
    /**
     * @var int
     */
    public $expectedResponse = 0;

    /**
     * @var int
     */
    public $actualResponse = 0;
}
