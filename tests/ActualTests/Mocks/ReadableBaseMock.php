<?php

declare(strict_types=1);

namespace tests\unreal4u\MQTT\Mocks;

use unreal4u\MQTT\Internals\ProtocolBase;
use unreal4u\MQTT\Internals\ReadableContent;
use unreal4u\MQTT\Internals\ReadableContentInterface;

class ReadableBaseMock extends ProtocolBase implements ReadableContentInterface
{
    use ReadableContent;
}
