<?php

namespace Speicher210\BusinessHours\Test;

use Speicher210\BusinessHours\AllDayTimeInterval;
use Speicher210\BusinessHours\Time;

/**
 * Test case for AllDayTimeInterval.
 */
class AllDayTimeIntervalTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateAllDayTimeInterval()
    {
        $timeInterval = new AllDayTimeInterval();

        $this->assertEquals(new Time(0, 0, 0), $timeInterval->getStart());
        $this->assertEquals(new Time(24, 0, 0), $timeInterval->getEnd());
    }
}
