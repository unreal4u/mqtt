<?php

namespace unreal4u\MQTT\Exceptions;

class InvalidResponseType extends \InvalidArgumentException
{
    public $expectedResponse = 0;

    public $actualResponse = 0;
}
