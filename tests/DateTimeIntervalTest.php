<?php

declare(strict_types=1);

namespace Speicher210\BusinessHours\Test;

use DateTime;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Psl\Json;
use Speicher210\BusinessHours\DateTimeInterval;

class DateTimeIntervalTest extends TestCase
{
    public function testConstructorOpeningEqualClosing(): void
    {
        $this->expectExceptionMessage('The opening date and time "2016-03-07 13:00:00" must be before the closing date and time "2016-03-07 13:00:00".');
        $this->expectException(InvalidArgumentException::class);

        new DateTimeInterval(new DateTime('2016-03-07 13:00:00'), new DateTime('2016-03-07 13:00:00'));
    }

    public function testConstructorOpeningAfterClosing(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The opening date and time "2016-03-07 15:00:00" must be before the closing date and time "2016-03-07 13:00:00".');

        new DateTimeInterval(new DateTime('2016-03-07 15:00:00'), new DateTime('2016-03-07 13:00:00'));
    }

    public function testJsonSerialize(): void
    {
        $interval = new DateTimeInterval(new DateTime('2016-03-07 11:20:50'), new DateTime('2016-03-07 13:33:50'));

        self::assertJsonStringEqualsJsonFile(
            __DIR__ . '/Expected/DateTimeInterval/testJsonSerialize.json',
            Json\encode($interval),
        );
    }
}
