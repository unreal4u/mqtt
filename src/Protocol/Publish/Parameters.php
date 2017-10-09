<?php

declare(strict_types=1);

namespace unreal4u\MQTT\Protocol\Publish;

final class Parameters
{
    /**
     * The 1st byte can contain some bits
     *
     * The order of these flags are:
     *
     *   7-6-5-4-3-2-1-0
     * b'0-0-0-0-0-0-0-0'
     *
     * Bit 7-4: Control packet value ID (3 for PUBLISH)
     * Bit 3: Duplicate delivery of a PUBLISH Control Packet
     * Bit 2 & 1: PUBLISH Quality of Service
     * Bit 0: PUBLISH Retain flag
     *
     * @see http://docs.oasis-open.org/mqtt/mqtt/v3.1.1/os/mqtt-v3.1.1-os.html#_Table_2.2_-
     * @var string
     */
    private $bitFlag = b'00000000';

    /**
     * The QoS lvl, choose between 0 and 2
     * @var int
     */
    private $qosLevel = 0;

    /**
     * Returns the set of flags we are making the connection with
     *
     * @return int
     */
    public function getFlags(): int
    {
        return (int)$this->bitFlag;
    }

    private function setDuplicate(bool $enabled = false): Parameters
    {
        $this->bitFlag &= ~16;
        if ($enabled === true) {
            $this->bitFlag |= 16;
        }
        return $this;
    }

    public function checkDuplicate(): Parameters
    {
        if ($this->qosLevel === 0) {
            return $this->setDuplicate();
        }

        return $this;
    }
}
