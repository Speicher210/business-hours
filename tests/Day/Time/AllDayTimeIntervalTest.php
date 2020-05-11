<?php

declare(strict_types=1);

namespace Speicher210\BusinessHours\Test\Day\Time;

use PHPUnit\Framework\TestCase;
use Speicher210\BusinessHours\Day\Time\AllDayTimeInterval;
use Speicher210\BusinessHours\Day\Time\Time;

class AllDayTimeIntervalTest extends TestCase
{
    public function testCreateAllDayTimeInterval() : void
    {
        $timeInterval = new AllDayTimeInterval();

        self::assertEquals(new Time(0, 0, 0), $timeInterval->getStart());
        self::assertEquals(new Time(24, 0, 0), $timeInterval->getEnd());
    }
}
