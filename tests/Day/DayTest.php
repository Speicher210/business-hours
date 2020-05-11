<?php

declare(strict_types=1);

namespace Speicher210\BusinessHours\Test\Day;

use InvalidArgumentException;
use OutOfBoundsException;
use PHPUnit\Framework\TestCase;
use Speicher210\BusinessHours\Day\Day;
use Speicher210\BusinessHours\Day\DayBuilder;
use Speicher210\BusinessHours\Day\DayInterface;
use Speicher210\BusinessHours\Day\Time\Time;
use Speicher210\BusinessHours\Day\Time\TimeInterval;
use function json_encode;

class DayTest extends TestCase
{
    public function testConstructorOverlappingIntervals() : void
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

        $expected = [
            TimeInterval::fromString('09:00', '12:00'),
            TimeInterval::fromString('12:45', '19:00'),
        ];
        $this->assertEquals(
            $expected,
            $day->getOpeningHoursIntervals()
        );
    }

    public function testExceptionInvalidDayOfWeek() : void
    {
        $this->expectExceptionMessage('Invalid day of week "152".');
        $this->expectException(OutOfBoundsException::class);

        new Day(152, []);
    }

    public function testExceptionIsThrownIfOpeningHoursIntervalsIsEmpty() : void
    {
        $this->expectExceptionMessage('The day must have at least one opening interval.');
        $this->expectException(InvalidArgumentException::class);

        new Day(Day::WEEK_DAY_MONDAY, []);
    }

    public function testExceptionIsThrownIfOpeningHoursIntervalsArrayDoesNotContainTimeIntervals() : void
    {
        $this->expectExceptionMessage('Interval must be a Speicher210\BusinessHours\Day\Time\TimeIntervalInterface');
        $this->expectException(InvalidArgumentException::class);

        new Day(
            Day::WEEK_DAY_MONDAY,
            ['non time interval']
        );
    }

    public function testGetClosestPreviousOpeningHoursIntervalWhileInsideInterval() : void
    {
        $day             = DayBuilder::fromArray(Day::WEEK_DAY_MONDAY, [['09:00', '10 AM'], ['12:15', '2 pm'], ['14:30', '18:30']]);
        $closestInterval = $day->getClosestPreviousOpeningHoursInterval(new Time(13, 0));

        $this->assertSame(12, $closestInterval->getStart()->getHours());
        $this->assertSame(15, $closestInterval->getStart()->getMinutes());
        $this->assertSame(14, $closestInterval->getEnd()->getHours());
        $this->assertSame(0, $closestInterval->getEnd()->getMinutes());
    }

    public function testGetClosestPreviousOpeningHoursIntervalWhileBetweenIntervals() : void
    {
        $day             = DayBuilder::fromArray(Day::WEEK_DAY_MONDAY, [['09:00', '10 AM'], ['12:12', '2 pm'], ['14:30', '18:25']]);
        $closestInterval = $day->getClosestPreviousOpeningHoursInterval(new Time(14, 20));

        $this->assertSame(12, $closestInterval->getStart()->getHours());
        $this->assertSame(12, $closestInterval->getStart()->getMinutes());
        $this->assertSame(14, $closestInterval->getEnd()->getHours());
        $this->assertSame(00, $closestInterval->getEnd()->getMinutes());
    }

    public function testGetClosestPreviousOpeningHoursIntervalWhileBeingBeforeAllIntervals() : void
    {
        $day             = DayBuilder::fromArray(Day::WEEK_DAY_MONDAY, [['09:00', '10 AM'], ['12:00', '2 pm'], ['14:30', '18:30']]);
        $closestInterval = $day->getClosestPreviousOpeningHoursInterval(new Time(8, 0));

        $this->assertNull($closestInterval);
    }

    public function testGetClosestNextOpeningHoursIntervalWhileInsideInterval() : void
    {
        $day             = DayBuilder::fromArray(Day::WEEK_DAY_MONDAY, [['09:00', '10 AM'], ['12:15', '2 pm'], ['14:30', '18:30']]);
        $closestInterval = $day->getClosestNextOpeningHoursInterval(new Time(13, 0));

        $this->assertSame(12, $closestInterval->getStart()->getHours());
        $this->assertSame(15, $closestInterval->getStart()->getMinutes());
        $this->assertSame(14, $closestInterval->getEnd()->getHours());
        $this->assertSame(0, $closestInterval->getEnd()->getMinutes());
    }

    public function testGetClosestNextOpeningHoursIntervalWhileBetweenIntervals() : void
    {
        $day             = DayBuilder::fromArray(Day::WEEK_DAY_MONDAY, [['09:00', '10 AM'], ['12:00', '2 pm'], ['14:30', '18:25']]);
        $closestInterval = $day->getClosestNextOpeningHoursInterval(new Time(14, 20));

        $this->assertSame(14, $closestInterval->getStart()->getHours());
        $this->assertSame(30, $closestInterval->getStart()->getMinutes());
        $this->assertSame(18, $closestInterval->getEnd()->getHours());
        $this->assertSame(25, $closestInterval->getEnd()->getMinutes());
    }

    public function testGetClosestNextOpeningHoursIntervalWhileBeingAfterAllIntervals() : void
    {
        $day             = DayBuilder::fromArray(Day::WEEK_DAY_MONDAY, [['09:00', '10 AM'], ['12:00', '2 pm'], ['14:30', '18:30']]);
        $closestInterval = $day->getClosestNextOpeningHoursInterval(new Time(19, 0));

        $this->assertNull($closestInterval);
    }

    public function testGetPreviousOpeningHoursIntervalWhileInsideInterval() : void
    {
        $day          = DayBuilder::fromArray(
            Day::WEEK_DAY_MONDAY,
            [['09:30', '10 AM'], ['12:15', '2 pm'], ['14:30', '18:20'], ['19:25', '20:00']]
        );
        $nextInterval = $day->getPreviousOpeningHoursInterval(new Time(13, 0));

        $this->assertSame(9, $nextInterval->getStart()->getHours());
        $this->assertSame(30, $nextInterval->getStart()->getMinutes());
        $this->assertSame(10, $nextInterval->getEnd()->getHours());
        $this->assertSame(00, $nextInterval->getEnd()->getMinutes());
    }

    public function testGetPreviousOpeningHoursIntervalWhileBetweenIntervals() : void
    {
        $day          = DayBuilder::fromArray(Day::WEEK_DAY_MONDAY, [['09:00', '10 AM'], ['12:15', '2 pm'], ['14:30', '18:25']]);
        $nextInterval = $day->getPreviousOpeningHoursInterval(new Time(14, 20));

        $this->assertSame(12, $nextInterval->getStart()->getHours());
        $this->assertSame(15, $nextInterval->getStart()->getMinutes());
        $this->assertSame(14, $nextInterval->getEnd()->getHours());
        $this->assertSame(00, $nextInterval->getEnd()->getMinutes());
    }

    public function testGetPreviousOpeningHoursIntervalWhileOutsideIntervals() : void
    {
        $day          = DayBuilder::fromArray(Day::WEEK_DAY_MONDAY, [['09:00', '10 AM'], ['12:00', '2 pm'], ['14:30', '18:30']]);
        $nextInterval = $day->getPreviousOpeningHoursInterval(new Time(8, 0));

        $this->assertNull($nextInterval);
    }

    public function testGetNextOpeningHoursIntervalWhileInsideInterval() : void
    {
        $day          = DayBuilder::fromArray(
            Day::WEEK_DAY_MONDAY,
            [['09:00', '10 AM'], ['12:15', '2 pm'], ['14:30', '18:20'], ['19:25', '20:00']]
        );
        $nextInterval = $day->getNextOpeningHoursInterval(new Time(13, 0));

        $this->assertSame(14, $nextInterval->getStart()->getHours());
        $this->assertSame(30, $nextInterval->getStart()->getMinutes());
        $this->assertSame(18, $nextInterval->getEnd()->getHours());
        $this->assertSame(20, $nextInterval->getEnd()->getMinutes());
    }

    public function testGetNextOpeningHoursIntervalWhileBetweenIntervals() : void
    {
        $day          = DayBuilder::fromArray(Day::WEEK_DAY_MONDAY, [['09:00', '10 AM'], ['12:00', '2 pm'], ['14:30', '18:25']]);
        $nextInterval = $day->getNextOpeningHoursInterval(new Time(14, 20));

        $this->assertSame(14, $nextInterval->getStart()->getHours());
        $this->assertSame(30, $nextInterval->getStart()->getMinutes());
        $this->assertSame(18, $nextInterval->getEnd()->getHours());
        $this->assertSame(25, $nextInterval->getEnd()->getMinutes());
    }

    public function testGetNextOpeningHoursIntervalWhileOutsideIntervals() : void
    {
        $day          = DayBuilder::fromArray(Day::WEEK_DAY_MONDAY, [['09:00', '10 AM'], ['12:00', '2 pm'], ['14:30', '18:30']]);
        $nextInterval = $day->getNextOpeningHoursInterval(new Time(19, 0));

        $this->assertNull($nextInterval);
    }

    public function testGetOpeningTime() : void
    {
        $day = DayBuilder::fromArray(Day::WEEK_DAY_MONDAY, [['12:00', '2 pm'], ['14:30', '18:30'], ['09:00', '10 AM']]);
        $this->assertEquals(9, $day->getOpeningTime()->getHours());
        $this->assertEquals(0, $day->getOpeningTime()->getMinutes());
    }

    public function testGetClosingTime() : void
    {
        $day = DayBuilder::fromArray(Day::WEEK_DAY_MONDAY, [['12:00', '2 pm'], ['14:30', '18:30'], ['09:00', '10 AM']]);
        $this->assertEquals(18, $day->getClosingTime()->getHours());
        $this->assertEquals(30, $day->getClosingTime()->getMinutes());
    }

    /**
     * @return mixed[]
     */
    public static function dataProviderTestIsWithinOpeningHours() : array
    {
        $day = DayBuilder::fromArray(Day::WEEK_DAY_MONDAY, [['12:00', '2 pm'], ['14:30', '18:30'], ['09:00', '10 AM']]);

        return [
            [$day, 14, 0, true],
            [$day, 13, 0, true],
            [$day, 18, 30, true],
            [$day, 15, 0, true],
            [$day, 9, 30, true],
            [$day, 8, 0, false],
            [$day, 20, 0, false],
        ];
    }

    /**
     * @param Day  $day      The day to test.
     * @param int  $hours    The hours to test.
     * @param int  $minutes  The minutes to test.
     * @param bool $expected The expected value.
     *
     * @dataProvider dataProviderTestIsWithinOpeningHours
     */
    public function testIsWithinOpeningHours(Day $day, int $hours, int $minutes, bool $expected) : void
    {
        $this->assertEquals($expected, $day->isWithinOpeningHours(new Time($hours, $minutes)));
    }

    public function testGetDayOfWeekName() : void
    {
        $day = DayBuilder::fromArray(Day::WEEK_DAY_MONDAY, [['14:30', '18:30']]);
        $this->assertSame('Monday', $day->getDayOfWeekName());
    }

    public function testJsonSerialize() : void
    {
        $day = DayBuilder::fromArray(
            DayInterface::WEEK_DAY_MONDAY,
            [['12:00', '2 pm'], ['14:30', '18:30'], ['09:00', '10 AM']]
        );

        $this->assertJsonStringEqualsJsonFile(
            __DIR__ . '/Expected/Day/testJsonSerialize.json',
            json_encode($day)
        );
    }

    public function testCloning() : void
    {
        $original = $day = DayBuilder::fromArray(DayInterface::WEEK_DAY_MONDAY, [['12:00', '2 pm']]);
        $clone    = clone $original;

        $this->assertEquals($original, $clone);
        $this->assertNotSame($original, $clone);

        $originalOpeningHours = $original->getOpeningHoursIntervals();
        $cloneOpeningHours    = $clone->getOpeningHoursIntervals();
        $this->assertEquals($originalOpeningHours, $cloneOpeningHours);

        $this->assertNotSame($originalOpeningHours[0], $cloneOpeningHours[0]);
    }
}
