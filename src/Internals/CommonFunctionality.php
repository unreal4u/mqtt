<?php

declare(strict_types=1);

namespace unreal4u\MQTT\Internals;

use Psr\Log\LoggerInterface;
use unreal4u\MQTT\DummyLogger;

trait CommonFunctionality
{
    /**
     * The actual logger object
     * @var LoggerInterface
     */
    protected $logger;

    final public function __construct(LoggerInterface $logger = null)
    {
        if ($logger === null) {
            $logger = new DummyLogger();
        }

        $this->logger = $logger;
    }
}
