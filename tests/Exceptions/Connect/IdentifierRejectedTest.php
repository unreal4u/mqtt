<?php

declare(strict_types=1);

namespace tests\unreal4u\MQTT\Exceptions;

use PHPUnit\Framework\TestCase;
use unreal4u\MQTT\Exceptions\Connect\IdentifierRejected;

class IdentifierRejectedTest extends TestCase
{
    /**
     * @var IdentifierRejected
     */
    private $exception;

    protected function setUp()
    {
        $this->exception = new IdentifierRejected();
        parent::setUp();
    }

    public function testFillPossibleReasonIsSetAndReturnedCorrectly(): void
    {
        $this->exception->fillPossibleReason('test reason');
        $this->assertSame('test reason', $this->exception->getPossibleReason());
    }
}
