<?php

declare(strict_types = 1);

namespace Speicher210\BusinessHours\Test;

use PHPUnit\Framework\TestCase;
use Speicher210\BusinessHours\DateTimeInterval;

class DateTimeIntervalTest extends TestCase
{
    public function testConstructorOpeningEqualClosing()
    {
        $this->expectExceptionMessage("The opening date and time \"2016-03-07 13:00:00\" must be before the closing date and time \"2016-03-07 13:00:00\".");
        $this->expectException(\InvalidArgumentException::class);

        new DateTimeInterval(new \DateTime('2016-03-07 13:00:00'), new \DateTime('2016-03-07 13:00:00'));
    }

    public function testConstructorOpeningAfterClosing()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("The opening date and time \"2016-03-07 15:00:00\" must be before the closing date and time \"2016-03-07 13:00:00\".");

        new DateTimeInterval(new \DateTime('2016-03-07 15:00:00'), new \DateTime('2016-03-07 13:00:00'));
    }

    public function testJsonSerialize()
    {
        $interval = new DateTimeInterval(new \DateTime('2016-03-07 11:20:50'), new \DateTime('2016-03-07 13:33:50'));

        $this->assertJsonStringEqualsJsonFile(
            __DIR__ . '/Expected/DateTimeInterval/testJsonSerialize.json',
            \json_encode($interval)
        );
    }
}
