<?php

declare(strict_types=1);

namespace Speicher210\BusinessHours\Test\Day;

use InvalidArgumentException;
use OutOfBoundsException;
use PHPUnit\Framework\TestCase;
use Psl\Json;
use Speicher210\BusinessHours\Day\Day;
use Speicher210\BusinessHours\Day\DayBuilder;
use Speicher210\BusinessHours\Day\DayInterface;
use Speicher210\BusinessHours\Day\Time\Time;
use Speicher210\BusinessHours\Day\Time\TimeInterval;

class DayTest extends TestCase
{
    public function testConstructorOverlappingIntervals(): void
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
            ],
        );

        $expected = [
            TimeInterval::fromString('09:00', '12:00'),
            TimeInterval::fromString('12:45', '19:00'),
        ];
        self::assertEquals(
            $expected,
            $day->getOpeningHoursIntervals(),
        );
    }

    public function testExceptionInvalidDayOfWeek(): void
    {
        $this->expectExceptionMessage('Invalid day of week "152".');
        $this->expectException(OutOfBoundsException::class);

        new Day(152, []);
    }

    public function testExceptionIsThrownIfOpeningHoursIntervalsIsEmpty(): void
    {
        $this->expectExceptionMessage('The day must have at least one opening interval.');
        $this->expectException(InvalidArgumentException::class);

        new Day(Day::WEEK_DAY_MONDAY, []);
    }

    public function testGetClosestPreviousOpeningHoursIntervalWhileInsideInterval(): void
    {
        $day             = DayBuilder::fromArray(Day::WEEK_DAY_MONDAY, [['09:00', '10 AM'], ['12:15', '2 pm'], ['14:30', '18:30']]);
        $closestInterval = $day->getClosestPreviousOpeningHoursInterval(new Time(13, 0));

        self::assertSame(12, $closestInterval->getStart()->hours());
        self::assertSame(15, $closestInterval->getStart()->minutes());
        self::assertSame(14, $closestInterval->getEnd()->hours());
        self::assertSame(0, $closestInterval->getEnd()->minutes());
    }

    public function testGetClosestPreviousOpeningHoursIntervalWhileBetweenIntervals(): void
    {
        $day             = DayBuilder::fromArray(Day::WEEK_DAY_MONDAY, [['09:00', '10 AM'], ['12:12', '2 pm'], ['14:30', '18:25']]);
        $closestInterval = $day->getClosestPreviousOpeningHoursInterval(new Time(14, 20));

        self::assertSame(12, $closestInterval->getStart()->hours());
        self::assertSame(12, $closestInterval->getStart()->minutes());
        self::assertSame(14, $closestInterval->getEnd()->hours());
        self::assertSame(00, $closestInterval->getEnd()->minutes());
    }

    public function testGetClosestPreviousOpeningHoursIntervalWhileBeingBeforeAllIntervals(): void
    {
        $day             = DayBuilder::fromArray(Day::WEEK_DAY_MONDAY, [['09:00', '10 AM'], ['12:00', '2 pm'], ['14:30', '18:30']]);
        $closestInterval = $day->getClosestPreviousOpeningHoursInterval(new Time(8, 0));

        self::assertNull($closestInterval);
    }

    public function testGetClosestNextOpeningHoursIntervalWhileInsideInterval(): void
    {
        $day             = DayBuilder::fromArray(Day::WEEK_DAY_MONDAY, [['09:00', '10 AM'], ['12:15', '2 pm'], ['14:30', '18:30']]);
        $closestInterval = $day->getClosestNextOpeningHoursInterval(new Time(13, 0));

        self::assertSame(12, $closestInterval->getStart()->hours());
        self::assertSame(15, $closestInterval->getStart()->minutes());
        self::assertSame(14, $closestInterval->getEnd()->hours());
        self::assertSame(0, $closestInterval->getEnd()->minutes());
    }

    public function testGetClosestNextOpeningHoursIntervalWhileBetweenIntervals(): void
    {
        $day             = DayBuilder::fromArray(Day::WEEK_DAY_MONDAY, [['09:00', '10 AM'], ['12:00', '2 pm'], ['14:30', '18:25']]);
        $closestInterval = $day->getClosestNextOpeningHoursInterval(new Time(14, 20));

        self::assertSame(14, $closestInterval->getStart()->hours());
        self::assertSame(30, $closestInterval->getStart()->minutes());
        self::assertSame(18, $closestInterval->getEnd()->hours());
        self::assertSame(25, $closestInterval->getEnd()->minutes());
    }

    public function testGetClosestNextOpeningHoursIntervalWhileBeingAfterAllIntervals(): void
    {
        $day             = DayBuilder::fromArray(Day::WEEK_DAY_MONDAY, [['09:00', '10 AM'], ['12:00', '2 pm'], ['14:30', '18:30']]);
        $closestInterval = $day->getClosestNextOpeningHoursInterval(new Time(19, 0));

        self::assertNull($closestInterval);
    }

    public function testGetPreviousOpeningHoursIntervalWhileInsideInterval(): void
    {
        $day          = DayBuilder::fromArray(
            Day::WEEK_DAY_MONDAY,
            [['09:30', '10 AM'], ['12:15', '2 pm'], ['14:30', '18:20'], ['19:25', '20:00']],
        );
        $nextInterval = $day->getPreviousOpeningHoursInterval(new Time(13, 0));

        self::assertSame(9, $nextInterval->getStart()->hours());
        self::assertSame(30, $nextInterval->getStart()->minutes());
        self::assertSame(10, $nextInterval->getEnd()->hours());
        self::assertSame(00, $nextInterval->getEnd()->minutes());
    }

    public function testGetPreviousOpeningHoursIntervalWhileBetweenIntervals(): void
    {
        $day          = DayBuilder::fromArray(Day::WEEK_DAY_MONDAY, [['09:00', '10 AM'], ['12:15', '2 pm'], ['14:30', '18:25']]);
        $nextInterval = $day->getPreviousOpeningHoursInterval(new Time(14, 20));

        self::assertSame(12, $nextInterval->getStart()->hours());
        self::assertSame(15, $nextInterval->getStart()->minutes());
        self::assertSame(14, $nextInterval->getEnd()->hours());
        self::assertSame(00, $nextInterval->getEnd()->minutes());
    }

    public function testGetPreviousOpeningHoursIntervalWhileOutsideIntervals(): void
    {
        $day          = DayBuilder::fromArray(Day::WEEK_DAY_MONDAY, [['09:00', '10 AM'], ['12:00', '2 pm'], ['14:30', '18:30']]);
        $nextInterval = $day->getPreviousOpeningHoursInterval(new Time(8, 0));

        self::assertNull($nextInterval);
    }

    public function testGetNextOpeningHoursIntervalWhileInsideInterval(): void
    {
        $day          = DayBuilder::fromArray(
            Day::WEEK_DAY_MONDAY,
            [['09:00', '10 AM'], ['12:15', '2 pm'], ['14:30', '18:20'], ['19:25', '20:00']],
        );
        $nextInterval = $day->getNextOpeningHoursInterval(new Time(13, 0));

        self::assertSame(14, $nextInterval->getStart()->hours());
        self::assertSame(30, $nextInterval->getStart()->minutes());
        self::assertSame(18, $nextInterval->getEnd()->hours());
        self::assertSame(20, $nextInterval->getEnd()->minutes());
    }

    public function testGetNextOpeningHoursIntervalWhileBetweenIntervals(): void
    {
        $day          = DayBuilder::fromArray(Day::WEEK_DAY_MONDAY, [['09:00', '10 AM'], ['12:00', '2 pm'], ['14:30', '18:25']]);
        $nextInterval = $day->getNextOpeningHoursInterval(new Time(14, 20));

        self::assertSame(14, $nextInterval->getStart()->hours());
        self::assertSame(30, $nextInterval->getStart()->minutes());
        self::assertSame(18, $nextInterval->getEnd()->hours());
        self::assertSame(25, $nextInterval->getEnd()->minutes());
    }

    public function testGetNextOpeningHoursIntervalWhileOutsideIntervals(): void
    {
        $day          = DayBuilder::fromArray(Day::WEEK_DAY_MONDAY, [['09:00', '10 AM'], ['12:00', '2 pm'], ['14:30', '18:30']]);
        $nextInterval = $day->getNextOpeningHoursInterval(new Time(19, 0));

        self::assertNull($nextInterval);
    }

    public function testGetOpeningTime(): void
    {
        $day = DayBuilder::fromArray(Day::WEEK_DAY_MONDAY, [['12:00', '2 pm'], ['14:30', '18:30'], ['09:00', '10 AM']]);
        self::assertEquals(9, $day->getOpeningTime()->hours());
        self::assertEquals(0, $day->getOpeningTime()->minutes());
    }

    public function testGetClosingTime(): void
    {
        $day = DayBuilder::fromArray(Day::WEEK_DAY_MONDAY, [['12:00', '2 pm'], ['14:30', '18:30'], ['09:00', '10 AM']]);
        self::assertEquals(18, $day->getClosingTime()->hours());
        self::assertEquals(30, $day->getClosingTime()->minutes());
    }

    /**
     * @return mixed[]
     */
    public static function dataProviderTestIsWithinOpeningHours(): array
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
    public function testIsWithinOpeningHours(Day $day, int $hours, int $minutes, bool $expected): void
    {
        self::assertEquals($expected, $day->isWithinOpeningHours(new Time($hours, $minutes)));
    }

    public function testGetDayOfWeekName(): void
    {
        $day = DayBuilder::fromArray(Day::WEEK_DAY_MONDAY, [['14:30', '18:30']]);
        self::assertSame('Monday', $day->getDayOfWeekName());
    }

    public function testJsonSerialize(): void
    {
        $day = DayBuilder::fromArray(
            DayInterface::WEEK_DAY_MONDAY,
            [['12:00', '2 pm'], ['14:30', '18:30'], ['09:00', '10 AM']],
        );

        self::assertJsonStringEqualsJsonFile(
            __DIR__ . '/Expected/Day/testJsonSerialize.json',
            Json\encode($day),
        );
    }

    public function testCloning(): void
    {
        $original = $day = DayBuilder::fromArray(DayInterface::WEEK_DAY_MONDAY, [['12:00', '2 pm']]);
        $clone    = clone $original;

        self::assertEquals($original, $clone);
        self::assertNotSame($original, $clone);

        $originalOpeningHours = $original->getOpeningHoursIntervals();
        $cloneOpeningHours    = $clone->getOpeningHoursIntervals();
        self::assertEquals($originalOpeningHours, $cloneOpeningHours);

        self::assertNotSame($originalOpeningHours[0], $cloneOpeningHours[0]);
    }
}
