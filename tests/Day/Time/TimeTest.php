<?php

declare(strict_types=1);

namespace Speicher210\BusinessHours\Test\Day\Time;

use DateTime;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Psl\Json;
use Psl\Str;
use Speicher210\BusinessHours\Day\Time\Time;

class TimeTest extends TestCase
{
    /**
     * @return mixed[]
     */
    public static function dataProviderTestInstantiateTimeWithInvalidData(): array
    {
        return [
            [-1, 0, 0],
            [0, -1, 0],
            [0, 0, -1],
            [24, 0, 1],
            [24, 1, 0],
            [0, 65, 0],
            [0, 0, 75],
        ];
    }

    /**
     * @dataProvider dataProviderTestInstantiateTimeWithInvalidData
     */
    public function testInstantiateTimeWithInvalidData(int $hours, int $minutes, int $seconds): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid time');

        new Time($hours, $minutes, $seconds);
    }

    /**
     * @return mixed[]
     */
    public static function dataProviderTestFromStringInvalid(): array
    {
        return [
            ['invalid'],
            ['24:00:01'],
            ['25:00'],
            [''],
        ];
    }

    /**
     * @dataProvider dataProviderTestFromStringInvalid
     */
    public function testFromStringInvalid(string $string): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(Str\format('Invalid time "%s".', $string));

        Time::fromString($string);
    }

    /**
     * @return mixed[]
     */
    public static function dataProviderTestFromString(): array
    {
        return [
            ['2pm', 14, 0, 0],
            ['11:00', 11, 0, 0],
            ['11:00:11', 11, 0, 11],
            ['23:15', 23, 15, 0],
            ['24:00', 24, 0, 0],
        ];
    }

    /**
     * @dataProvider dataProviderTestFromString
     */
    public function testFromString(
        string $string,
        int $expectedHours,
        int $expectedMinutes,
        int $expectedSeconds
    ): void {
        $time = Time::fromString($string);
        self::assertEquals($expectedHours, $time->hours());
        self::assertEquals($expectedMinutes, $time->minutes());
        self::assertEquals($expectedSeconds, $time->seconds());
    }

    /**
     * @return mixed[]
     */
    public static function dataProviderTestFromDate(): array
    {
        return [
            [new DateTime('2 AM'), 2, 0, 0],
            [new DateTime('3:20:15 PM'), 15, 20, 15],
        ];
    }

    /**
     * @dataProvider dataProviderTestFromDate
     */
    public function testFromDate(DateTime $date, int $expectedHours, int $expectedMinutes, int $expectedSeconds): void
    {
        $time = Time::fromDate($date);
        self::assertEquals($expectedHours, $time->hours());
        self::assertEquals($expectedMinutes, $time->minutes());
        self::assertEquals($expectedSeconds, $time->seconds());
    }

    /**
     * @return mixed[]
     */
    public static function dataProviderTestFromSecondsInvalid(): array
    {
        return [
            [-1, 'Invalid time "-00:00:01".'],
            [86401, 'Invalid time "24:00:01".'],
        ];
    }

    /**
     * @dataProvider dataProviderTestFromSecondsInvalid
     */
    public function testFromSecondsInvalid(int $seconds, string $expectedMessage): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($expectedMessage);

        Time::fromSeconds($seconds);
    }

    /**
     * @return mixed[]
     */
    public static function dataProviderTestFromSeconds(): array
    {
        return [
            [0, 0, 0, 0],
            [40, 0, 0, 40],
            [60, 0, 1, 0],
            [3600, 1, 0, 0],
            [86400, 24, 0, 0],
            [45296, 12, 34, 56],
        ];
    }

    /**
     * @dataProvider dataProviderTestFromSeconds
     */
    public function testFromSeconds(int $seconds, int $expectedHours, int $expectedMinutes, int $expectedSeconds): void
    {
        $time = Time::fromSeconds($seconds);
        self::assertEquals($expectedHours, $time->hours());
        self::assertEquals($expectedMinutes, $time->minutes());
        self::assertEquals($expectedSeconds, $time->seconds());
    }

    /**
     * @return mixed[]
     */
    public static function dataProviderTestIsAfterOrEqual(): array
    {
        $time = new Time(20, 00);

        return [
            [$time, 18, 00, true],
            [$time, 22, 15, false],
            [$time, 20, 00, true],
        ];
    }

    /**
     * @dataProvider dataProviderTestIsAfterOrEqual
     */
    public function testIsAfterOrEqual(Time $time, int $hours, int $minutes, bool $expected): void
    {
        self::assertEquals($time->isAfterOrEqual(new Time($hours, $minutes)), $expected);
    }

    /**
     * @return mixed[]
     */
    public static function dataProviderTestIsBeforeOrEqual(): array
    {
        $time = new Time(20, 00);

        return [
            [$time, 18, 00, false],
            [$time, 22, 15, true],
            [$time, 20, 00, true],
        ];
    }

    /**
     * @dataProvider dataProviderTestIsBeforeOrEqual
     */
    public function testIsBeforeOrEqual(Time $time, int $hours, int $minutes, bool $expected): void
    {
        self::assertEquals($time->isBeforeOrEqual(new Time($hours, $minutes)), $expected);
    }

    /**
     * @return mixed[]
     */
    public static function dataProviderTestIsEqual(): array
    {
        $time = new Time(20, 00);

        return [
            [$time, 18, 00, false],
            [$time, 22, 15, false],
            [$time, 20, 00, true],
        ];
    }

    /**
     * @dataProvider dataProviderTestIsEqual
     */
    public function testIsEqual(Time $time, int $hours, int $minutes, bool $expected): void
    {
        self::assertEquals($time->isEqual(new Time($hours, $minutes)), $expected);
    }

    /**
     * @return mixed[]
     */
    public static function dataProviderTestToSeconds(): array
    {
        return [
            [72000, 20, 0, 0],
            [34200, 9, 30, 0],
            [45296, 12, 34, 56],
        ];
    }

    /**
     * @dataProvider dataProviderTestToSeconds
     */
    public function testToSeconds(
        int $expectedTimeRepresentationInSeconds,
        int $hours,
        int $minutes,
        int $seconds
    ): void {
        $time = new Time($hours, $minutes, $seconds);
        self::assertEquals($expectedTimeRepresentationInSeconds, $time->toSeconds());
    }

    public function testWithHours(): void
    {
        $time = new Time(1, 23, 45);

        $newTime = $time->withHours(10);

        self::assertEquals('01:23:45', $time->asString());
        self::assertEquals('10:23:45', $newTime->asString());
    }

    public function testAddHours(): void
    {
        $time = new Time(1, 0, 0);

        $newTime = $time->addHours(2);

        self::assertEquals('01:00:00', $time->asString());
        self::assertEquals('03:00:00', $newTime->asString());
    }

    public function testAddHoursThrowsExceptionIfResultIsNotValid(): void
    {
        $time = new Time(10, 0, 1);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid time "24:00:01".');

        $time->addHours(14);
    }

    public function testSubtractHours(): void
    {
        $time = new Time(11, 0, 0);

        $newTime = $time->subtractHours(2);

        self::assertEquals('11:00:00', $time->asString());
        self::assertEquals('09:00:00', $newTime->asString());
    }

    public function testSubtractHoursThrowsExceptionIfResultIsNotValid(): void
    {
        $time = new Time(10, 0, 1);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid time "-03:59:59".');

        $time->subtractHours(14);
    }

    public function testWithMinutes(): void
    {
        $time = new Time(1, 23, 45);

        $newTime = $time->withMinutes(32);

        self::assertEquals('01:23:45', $time->asString());
        self::assertEquals('01:32:45', $newTime->asString());
    }

    public function testAddMinutes(): void
    {
        $time = new Time(0, 0, 0);

        $newTime = $time->addMinutes(100);

        self::assertEquals('00:00:00', $time->asString());
        self::assertEquals('01:40:00', $newTime->asString());
    }

    public function testAddMinutesThrowsExceptionIfResultIsNotValid(): void
    {
        $time = new Time(24, 0, 0);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid time "24:01:00".');

        $time->addMinutes(1);
    }

    public function testSubtractMinutes(): void
    {
        $time = new Time(11, 0, 0);

        $newTime = $time->subtractMinutes(100);

        self::assertEquals('11:00:00', $time->asString());
        self::assertEquals('09:20:00', $newTime->asString());
    }

    public function testSubtractMinutesThrowsExceptionIfResultIsNotValid(): void
    {
        $time = new Time(0, 0, 0);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid time "-00:01:00".');

        $time->subtractMinutes(1);
    }

    public function testWithSeconds(): void
    {
        $time = new Time(1, 23, 45);

        $newTime = $time->withSeconds(54);

        self::assertEquals('01:23:45', $time->asString());
        self::assertEquals('01:23:54', $newTime->asString());
    }

    public function testAddSeconds(): void
    {
        $time = new Time(0, 59, 58);

        $newTime = $time->addSeconds(3);

        self::assertEquals('00:59:58', $time->asString());
        self::assertEquals('01:00:01', $newTime->asString());
    }

    public function testAddSecondsThrowsExceptionIfResultIsNotValid(): void
    {
        $time = new Time(24, 0, 0);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid time "24:00:01".');

        $time->addSeconds(1);
    }

    public function testSubtractSeconds(): void
    {
        $time = new Time(11, 0, 0);

        $newTime = $time->subtractSeconds(100);

        self::assertEquals('11:00:00', $time->asString());
        self::assertEquals('10:58:20', $newTime->asString());
    }

    public function testSubtractSecondsThrowsExceptionIfResultIsNotValid(): void
    {
        $time = new Time(0, 0, 0);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid time "-00:00:01".');

        $time->subtractSeconds(1);
    }

    /**
     * @return array<array<Time>>
     */
    public static function dataProviderTestAddTime(): array
    {
        return [
            [Time::fromString('00:00'), Time::fromString('01:01'), Time::fromString('01:01')],
            [Time::fromString('05:30'), Time::fromString('06:45'), Time::fromString('12:15')],
            [Time::fromString('22:45'), Time::fromString('01:15'), Time::fromString('24:00')],
        ];
    }

    /**
     * @dataProvider dataProviderTestAddTime
     */
    public function testAddTime(Time $startingTime, Time $timeToAdd, Time $expected): void
    {
        self::assertEquals(
            $expected,
            $startingTime->addTime($timeToAdd)
        );
    }

    public function testExceptionIsThrownIfAddingTimeOverflows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid time "24:00:01".');

        Time::fromString('22:45')->addTime(Time::fromString('01:15:01'));
    }

    /**
     * @return array<array<Time>>
     */
    public static function dataProviderTestSubtractTime(): array
    {
        return [
            [Time::fromString('03:40'), Time::fromString('01:15'), Time::fromString('02:25')],
            [Time::fromString('06:30'), Time::fromString('05:45'), Time::fromString('00:45')],
            [Time::fromString('22:45:20'), Time::fromString('22:45:20'), Time::fromString('00:00')],
        ];
    }

    /**
     * @dataProvider dataProviderTestSubtractTime
     */
    public function testSubtractTime(Time $startingTime, Time $timeToSubtract, Time $expected): void
    {
        self::assertEquals(
            $expected,
            $startingTime->subtractTime($timeToSubtract)
        );
    }

    public function testExceptionIsThrownIfSubtractingTimeOverflows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid time "-00:00:01".');

        Time::fromString('01:45')->subtractTime(Time::fromString('01:45:01'));
    }

    /**
     * @return mixed[]
     */
    public static function dataProviderTestRoundToMinutes(): array
    {
        return [
            [Time::fromString('14:20:00'), 10, Time::ROUND_HALF_UP, Time::fromString('14:20')],
            [Time::fromString('14:21:00'), 10, Time::ROUND_HALF_UP, Time::fromString('14:20')],
            [Time::fromString('14:21:20'), 10, Time::ROUND_HALF_UP, Time::fromString('14:20')],
            [Time::fromString('14:25:00'), 10, Time::ROUND_HALF_UP, Time::fromString('14:30')],
            [Time::fromString('14:25:20'), 10, Time::ROUND_HALF_UP, Time::fromString('14:30')],
            [Time::fromString('14:20:00'), 10, Time::ROUND_HALF_DOWN, Time::fromString('14:20')],
            [Time::fromString('14:21:00'), 10, Time::ROUND_HALF_DOWN, Time::fromString('14:20')],
            [Time::fromString('14:21:20'), 10, Time::ROUND_HALF_DOWN, Time::fromString('14:20')],
            [Time::fromString('14:25:00'), 10, Time::ROUND_HALF_DOWN, Time::fromString('14:20')],
            [Time::fromString('14:25:20'), 10, Time::ROUND_HALF_DOWN, Time::fromString('14:30')],
            [Time::fromString('14:20:00'), 10, Time::ROUND_UP, Time::fromString('14:20')],
            [Time::fromString('14:21:00'), 10, Time::ROUND_UP, Time::fromString('14:30')],
            [Time::fromString('14:21:20'), 10, Time::ROUND_UP, Time::fromString('14:30')],
            [Time::fromString('14:25:00'), 10, Time::ROUND_UP, Time::fromString('14:30')],
            [Time::fromString('14:25:20'), 10, Time::ROUND_UP, Time::fromString('14:30')],
            [Time::fromString('14:20:00'), 10, Time::ROUND_DOWN, Time::fromString('14:20')],
            [Time::fromString('14:21:00'), 10, Time::ROUND_DOWN, Time::fromString('14:20')],
            [Time::fromString('14:21:20'), 10, Time::ROUND_DOWN, Time::fromString('14:20')],
            [Time::fromString('14:25:00'), 10, Time::ROUND_DOWN, Time::fromString('14:20')],
            [Time::fromString('14:25:20'), 10, Time::ROUND_DOWN, Time::fromString('14:20')],
            [Time::fromString('14:20:01'), 1, Time::ROUND_UP, Time::fromString('14:21')],
            [Time::fromString('14:20:01'), 1, Time::ROUND_DOWN, Time::fromString('14:20')],
            [Time::fromString('14:24:01'), 5, Time::ROUND_UP, Time::fromString('14:25')],
            [Time::fromString('14:24:59'), 5, Time::ROUND_DOWN, Time::fromString('14:20')],
        ];
    }

    /**
     * @dataProvider dataProviderTestRoundToMinutes
     */
    public function testRoundToMinutes(Time $time, int $precision, int $roundingMode, Time $expected): void
    {
        $actual = $time->roundToMinutes($precision, $roundingMode);

        self::assertEquals($expected, $actual);
    }

    /**
     * @return mixed[]
     */
    public static function dataProviderTestRoundToHour(): array
    {
        return [
            [Time::fromString('14:00:00'), Time::ROUND_HALF_UP, Time::fromString('14:00')],
            [Time::fromString('14:20:00'), Time::ROUND_HALF_UP, Time::fromString('14:00')],
            [Time::fromString('14:30:00'), Time::ROUND_HALF_UP, Time::fromString('15:00')],
            [Time::fromString('14:00:00'), Time::ROUND_HALF_DOWN, Time::fromString('14:00')],
            [Time::fromString('14:20:00'), Time::ROUND_HALF_DOWN, Time::fromString('14:00')],
            [Time::fromString('14:30:00'), Time::ROUND_HALF_DOWN, Time::fromString('14:00')],
            [Time::fromString('14:00:00'), Time::ROUND_UP, Time::fromString('14:00')],
            [Time::fromString('14:00:01'), Time::ROUND_UP, Time::fromString('15:00')],
            [Time::fromString('14:30:00'), Time::ROUND_UP, Time::fromString('15:00')],
            [Time::fromString('14:00:00'), Time::ROUND_DOWN, Time::fromString('14:00')],
            [Time::fromString('14:00:01'), Time::ROUND_DOWN, Time::fromString('14:00')],
            [Time::fromString('14:59:59'), Time::ROUND_DOWN, Time::fromString('14:00')],
        ];
    }

    /**
     * @dataProvider dataProviderTestRoundToHour
     */
    public function testRoundToHour(Time $time, int $roundingMode, Time $expected): void
    {
        $actual = $time->roundToHour($roundingMode);

        self::assertEquals($expected, $actual);
    }

    public function testCompareTo(): void
    {
        self::assertEquals(1, Time::fromString('15:00:01')->compareTo(Time::fromString('15:00:00')));
        self::assertEquals(0, Time::fromString('15:00')->compareTo(Time::fromString('15:00:00')));
        self::assertEquals(-1, Time::fromString('15:00:00')->compareTo(Time::fromString('15:00:01')));
    }

    public function testCompareEqual(): void
    {
        $time1 = Time::fromString('15:00');
        $time2 = Time::fromString('15:00:00');

        self::assertEquals(0, $time1->compareTo($time2));
        self::assertTrue($time1->equals($time2));
        self::assertTrue($time1->greaterThanOrEqual($time2));
        self::assertFalse($time1->greaterThan($time2));
        self::assertTrue($time1->lessThanOrEqual($time2));
        self::assertFalse($time1->lessThan($time2));
    }

    public function testCompareGreaterThan(): void
    {
        $time1 = Time::fromString('15:00:01');
        $time2 = Time::fromString('15:00:00');

        self::assertEquals(1, $time1->compareTo($time2));
        self::assertFalse($time1->equals($time2));
        self::assertTrue($time1->greaterThanOrEqual($time2));
        self::assertTrue($time1->greaterThan($time2));
        self::assertFalse($time1->lessThanOrEqual($time2));
        self::assertFalse($time1->lessThan($time2));
    }

    public function testCompareLessThan(): void
    {
        $time1 = Time::fromString('15:00:00');
        $time2 = Time::fromString('15:00:01');

        self::assertEquals(-1, $time1->compareTo($time2));
        self::assertFalse($time1->equals($time2));
        self::assertFalse($time1->greaterThanOrEqual($time2));
        self::assertFalse($time1->greaterThan($time2));
        self::assertTrue($time1->lessThanOrEqual($time2));
        self::assertTrue($time1->lessThan($time2));
    }

    public function testMin(): void
    {
        $times = [
            Time::fromString('22:00:00'),
            Time::fromString('22:00:00'),
            Time::fromString('05:00:01'),
            Time::fromString('05:00:00'),
            Time::fromString('06:00:00'),
        ];

        $actual = Time::min(...$times);
        self::assertEquals(Time::fromString('5:00'), $actual);
    }

    public function testMax(): void
    {
        $times = [
            Time::fromString('22:00:00'),
            Time::fromString('22:00:01'),
            Time::fromString('05:00:00'),
            Time::fromString('05:00:00'),
            Time::fromString('06:00:00'),
        ];

        $actual = Time::max(...$times);
        self::assertEquals(Time::fromString('22:00:01'), $actual);
    }

    public function testAsString(): void
    {
        $time = new Time(12, 34, 56);

        self::assertSame('12:34:56', $time->asString());
    }

    public function testJsonSerialize(): void
    {
        $time = new Time(20, 30, 15);

        self::assertJsonStringEqualsJsonFile(
            __DIR__ . '/Expected/Time/testJsonSerialize.json',
            Json\encode($time)
        );
    }
}
