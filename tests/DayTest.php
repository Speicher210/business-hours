<?php

namespace Speicher210\BusinessHours\Test;

use Speicher210\BusinessHours\Day;
use Speicher210\BusinessHours\Time;

/**
 * Test class for Day.
 */
class DayTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \OutOfBoundsException
     * @expectedExceptionMessage Invalid day of week "152".
     */
    public function testExceptionInvalidDayOfWeek()
    {
        new Day(152, []);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The day must have at least one opening interval.
     */
    public function testExceptionEmptyOpeningInterval()
    {
        new Day(Day::WEEK_DAY_MONDAY, []);
    }

    public function testGetClosestOpeningHoursIntervalWhileInsideInterval()
    {
        $day = new Day(Day::WEEK_DAY_MONDAY, [['09:00', '10 AM'], ['12:15', '2 pm'], ['14:30', '18:30']]);
        $closestInterval = $day->getClosestOpeningHoursInterval(new Time('13', '00'));

        $this->assertSame(12, $closestInterval->getStart()->getHours());
        $this->assertSame(15, $closestInterval->getStart()->getMinutes());
        $this->assertSame(14, $closestInterval->getEnd()->getHours());
        $this->assertSame(0, $closestInterval->getEnd()->getMinutes());
    }

    public function testGetClosestOpeningHoursIntervalWhileBetweenIntervals()
    {
        $day = new Day(Day::WEEK_DAY_MONDAY, [['09:00', '10 AM'], ['12:00', '2 pm'], ['14:30', '18:25']]);
        $closestInterval = $day->getClosestOpeningHoursInterval(new Time('14', '20'));

        $this->assertSame(14, $closestInterval->getStart()->getHours());
        $this->assertSame(30, $closestInterval->getStart()->getMinutes());
        $this->assertSame(18, $closestInterval->getEnd()->getHours());
        $this->assertSame(25, $closestInterval->getEnd()->getMinutes());
    }

    public function testGetClosestOpeningHoursIntervalWhileOutsideIntervals()
    {
        $day = new Day(Day::WEEK_DAY_MONDAY, [['09:00', '10 AM'], ['12:00', '2 pm'], ['14:30', '18:30']]);
        $closestInterval = $day->getClosestOpeningHoursInterval(new Time('19', '00'));

        $this->assertNull($closestInterval);
    }

    public function testGetNextOpeningHoursInterval()
    {
        $this->markTestIncomplete();
    }

    public function testGetOpeningTime()
    {
        $this->markTestIncomplete();
    }

    public function testGetClosingTime()
    {
        $this->markTestIncomplete();
    }

    public function testIsWithinOpeningHours()
    {
        $this->markTestIncomplete();
    }

    public function testGetDayOfWeekName()
    {
        $this->markTestIncomplete();
    }
}
