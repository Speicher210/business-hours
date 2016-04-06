<?php

namespace Speicher210\BusinessHours\Test\Day;

use Speicher210\BusinessHours\Day\Day;
use Speicher210\BusinessHours\Day\DayBuilder;
use Speicher210\BusinessHours\Day\DayInterface;
use Speicher210\BusinessHours\Day\Time\Time;
use Speicher210\BusinessHours\Day\Time\TimeInterval;

/**
 * Test class for Day.
 */
class DayTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructorOverlappingIntervals()
    {
        $day = DayBuilder::fromArray(
            Day::WEEK_DAY_MONDAY,
            [
                ['09:00', '10:00'],
                ['14:30', '18:30'],
                ['11:00', '11:30'],
                ['12:45', '15:00'],
                ['18:30', '19:00'],
                ['18:35', '18:45'],
                ['09:15', '12:00'],
            ]
        );

        $expected = array(
            TimeInterval::fromString('09:00', '12:00'),
            TimeInterval::fromString('12:45', '19:00'),
        );
        $this->assertEquals(
            $expected,
            $day->getOpeningHoursIntervals()
        );
    }

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
        $day = DayBuilder::fromArray(Day::WEEK_DAY_MONDAY, [['09:00', '10 AM'], ['12:15', '2 pm'], ['14:30', '18:30']]);
        $closestInterval = $day->getClosestOpeningHoursInterval(new Time('13', '00'));

        $this->assertSame(12, $closestInterval->getStart()->getHours());
        $this->assertSame(15, $closestInterval->getStart()->getMinutes());
        $this->assertSame(14, $closestInterval->getEnd()->getHours());
        $this->assertSame(0, $closestInterval->getEnd()->getMinutes());
    }

    public function testGetClosestOpeningHoursIntervalWhileBetweenIntervals()
    {
        $day = DayBuilder::fromArray(Day::WEEK_DAY_MONDAY, [['09:00', '10 AM'], ['12:00', '2 pm'], ['14:30', '18:25']]);
        $closestInterval = $day->getClosestOpeningHoursInterval(new Time('14', '20'));

        $this->assertSame(14, $closestInterval->getStart()->getHours());
        $this->assertSame(30, $closestInterval->getStart()->getMinutes());
        $this->assertSame(18, $closestInterval->getEnd()->getHours());
        $this->assertSame(25, $closestInterval->getEnd()->getMinutes());
    }

    public function testGetClosestOpeningHoursIntervalWhileOutsideIntervals()
    {
        $day = DayBuilder::fromArray(Day::WEEK_DAY_MONDAY, [['09:00', '10 AM'], ['12:00', '2 pm'], ['14:30', '18:30']]);
        $closestInterval = $day->getClosestOpeningHoursInterval(new Time('19', '00'));

        $this->assertNull($closestInterval);
    }

    public function testGetNextOpeningHoursIntervalWhileInsideInterval()
    {
        $day = DayBuilder::fromArray(Day::WEEK_DAY_MONDAY, [['09:00', '10 AM'], ['12:15', '2 pm'], ['14:30', '18:20']]);
        $nextInterval = $day->getNextOpeningHoursInterval(new Time('13', '00'));

        $this->assertSame(14, $nextInterval->getStart()->getHours());
        $this->assertSame(30, $nextInterval->getStart()->getMinutes());
        $this->assertSame(18, $nextInterval->getEnd()->getHours());
        $this->assertSame(20, $nextInterval->getEnd()->getMinutes());
    }

    public function testGetNextOpeningHoursIntervalWhileBetweenIntervals()
    {
        $day = DayBuilder::fromArray(Day::WEEK_DAY_MONDAY, [['09:00', '10 AM'], ['12:00', '2 pm'], ['14:30', '18:25']]);
        $nextInterval = $day->getNextOpeningHoursInterval(new Time('14', '20'));

        $this->assertSame(14, $nextInterval->getStart()->getHours());
        $this->assertSame(30, $nextInterval->getStart()->getMinutes());
        $this->assertSame(18, $nextInterval->getEnd()->getHours());
        $this->assertSame(25, $nextInterval->getEnd()->getMinutes());
    }

    public function testGetNextOpeningHoursIntervalWhileOutsideIntervals()
    {
        $day = DayBuilder::fromArray(Day::WEEK_DAY_MONDAY, [['09:00', '10 AM'], ['12:00', '2 pm'], ['14:30', '18:30']]);
        $nextInterval = $day->getNextOpeningHoursInterval(new Time('19', '00'));

        $this->assertNull($nextInterval);
    }

    public function testGetOpeningTime()
    {
        $day = DayBuilder::fromArray(Day::WEEK_DAY_MONDAY, [['12:00', '2 pm'], ['14:30', '18:30'], ['09:00', '10 AM']]);
        $this->assertEquals(9, $day->getOpeningTime()->getHours());
        $this->assertEquals(0, $day->getOpeningTime()->getMinutes());
    }

    public function testGetClosingTime()
    {
        $day = DayBuilder::fromArray(Day::WEEK_DAY_MONDAY, [['12:00', '2 pm'], ['14:30', '18:30'], ['09:00', '10 AM']]);
        $this->assertEquals(18, $day->getClosingTime()->getHours());
        $this->assertEquals(30, $day->getClosingTime()->getMinutes());
    }

    public static function dataProviderTestIsWithinOpeningHours()
    {
        $day = DayBuilder::fromArray(Day::WEEK_DAY_MONDAY, [['12:00', '2 pm'], ['14:30', '18:30'], ['09:00', '10 AM']]);

        return array(
            array($day, '14', '00', true),
            array($day, '13', '00', true),
            array($day, '18', '30', true),
            array($day, '15', '00', true),
            array($day, '09', '30', true),
            array($day, '08', '00', false),
            array($day, '20', '00', false),
        );
    }

    /**
     * @dataProvider dataProviderTestIsWithinOpeningHours
     *
     * @param Day $day The day to test.
     * @param integer $hours The hours to test.
     * @param integer $minutes The minutes to test.
     * @param boolean $expected The expected value.
     */
    public function testIsWithinOpeningHours(Day $day, $hours, $minutes, $expected)
    {
        $this->assertEquals($expected, $day->isWithinOpeningHours(new Time($hours, $minutes)));
    }

    public function testGetDayOfWeekName()
    {
        $day = DayBuilder::fromArray(Day::WEEK_DAY_MONDAY, [['14:30', '18:30']]);
        $this->assertSame('Monday', $day->getDayOfWeekName());
    }

    public function testJsonSerialize()
    {
        $day = DayBuilder::fromArray(DayInterface::WEEK_DAY_MONDAY, [['12:00', '2 pm'], ['14:30', '18:30'], ['09:00', '10 AM']]);

        $this->assertJsonStringEqualsJsonFile(
            __DIR__ . '/Expected/Day/testJsonSerialize.json',
            json_encode($day)
        );
    }
}
