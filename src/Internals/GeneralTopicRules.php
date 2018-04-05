<?php

declare(strict_types=1);

namespace unreal4u\MQTT\Internals;

abstract class GeneralTopicRules
{
    /**
     * @param string $topic
     * @return bool
     * @throws \OutOfBoundsException
     * @throws \InvalidArgumentException
     */
    protected function generalRulesCheck(string $topic): bool
    {
        if ($topic === '') {
            throw new \InvalidArgumentException('Topics must be at least 1 character long');
        }

        // UTF-8 topic names and filters must not be more than 65535 bytes in size
        if (\strlen($topic) > 65535) {
            throw new \OutOfBoundsException('Topics can not exceed 65535 bytes in size');
        }

        if (strpos($topic, \chr("\n")) !== false) {
            throw new \InvalidArgumentException('Topics can not contain the termination character');
        }

        return true;
    }
}
