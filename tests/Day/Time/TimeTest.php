<?php

declare(strict_types=1);

namespace Speicher210\BusinessHours\Test\Day\Time;

use DateTime;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Speicher210\BusinessHours\Day\Time\Time;
use function Safe\json_encode;
use function Safe\sprintf;

class TimeTest extends TestCase
{
    /**
     * @return mixed[]
     */
    public static function dataProviderTestInstantiateTimeWithInvalidData() : array
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
    public function testInstantiateTimeWithInvalidData(int $hours, int $minutes, int $seconds) : void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid time');

        new Time($hours, $minutes, $seconds);
    }

    /**
     * @return mixed[]
     */
    public static function dataProviderTestFromStringInvalid() : array
    {
        return [
            ['invalid'],
            ['24:00:01'],
            ['25:00'],
            [''],
        ];
    }

    /**
     * @param mixed $string The string to test.
     *
     * @dataProvider dataProviderTestFromStringInvalid
     */
    public function testFromStringInvalid($string) : void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('Invalid time "%s".', $string));

        Time::fromString($string);
    }

    /**
     * @return mixed[]
     */
    public static function dataProviderTestFromString() : array
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
    ) : void {
        $time = Time::fromString($string);
        self::assertEquals($expectedHours, $time->hours());
        self::assertEquals($expectedMinutes, $time->minutes());
        self::assertEquals($expectedSeconds, $time->seconds());
    }

    /**
     * @return mixed[]
     */
    public static function dataProviderTestFromDate() : array
    {
        return [
            [new DateTime('2 AM'), 2, 0, 0],
            [new DateTime('3:20:15 PM'), 15, 20, 15],
        ];
    }

    /**
     * @dataProvider dataProviderTestFromDate
     */
    public function testFromDate(DateTime $date, int $expectedHours, int $expectedMinutes, int $expectedSeconds) : void
    {
        $time = Time::fromDate($date);
        self::assertEquals($expectedHours, $time->hours());
        self::assertEquals($expectedMinutes, $time->minutes());
        self::assertEquals($expectedSeconds, $time->seconds());
    }

    /**
     * @return mixed[]
     */
    public static function dataProviderTestFromSecondsInvalid() : array
    {
        return [
            [-1],
            [86401],
        ];
    }

    /**
     * @dataProvider dataProviderTestFromSecondsInvalid
     */
    public function testFromSecondsInvalid(int $seconds) : void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('Invalid time "%s".', $seconds));

        Time::fromSeconds($seconds);
    }

    /**
     * @return mixed[]
     */
    public static function dataProviderTestFromSeconds() : array
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
    public function testFromSeconds(int $seconds, int $expectedHours, int $expectedMinutes, int $expectedSeconds) : void
    {
        $time = Time::fromSeconds($seconds);
        self::assertEquals($expectedHours, $time->hours());
        self::assertEquals($expectedMinutes, $time->minutes());
        self::assertEquals($expectedSeconds, $time->seconds());
    }

    /**
     * @return mixed[]
     */
    public static function dataProviderTestIsAfterOrEqual() : array
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
    public function testIsAfterOrEqual(Time $time, int $hours, int $minutes, bool $expected) : void
    {
        self::assertEquals($time->isAfterOrEqual(new Time($hours, $minutes)), $expected);
    }

    /**
     * @return mixed[]
     */
    public static function dataProviderTestIsBeforeOrEqual() : array
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
    public function testIsBeforeOrEqual(Time $time, int $hours, int $minutes, bool $expected) : void
    {
        self::assertEquals($time->isBeforeOrEqual(new Time($hours, $minutes)), $expected);
    }

    /**
     * @return mixed[]
     */
    public static function dataProviderTestIsEqual() : array
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
    public function testIsEqual(Time $time, int $hours, int $minutes, bool $expected) : void
    {
        self::assertEquals($time->isEqual(new Time($hours, $minutes)), $expected);
    }

    /**
     * @return mixed[]
     */
    public static function dataProviderTestToSeconds() : array
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
    ) : void {
        $time = new Time($hours, $minutes, $seconds);
        self::assertEquals($expectedTimeRepresentationInSeconds, $time->toSeconds());
    }

    public function testWithHours() : void
    {
        $time = new Time(1, 23, 45);

        $newTime = $time->withHours(10);

        self::assertEquals('01:23:45', $time->asString());
        self::assertEquals('10:23:45', $newTime->asString());
    }

    public function testWithMinutes() : void
    {
        $time = new Time(1, 23, 45);

        $newTime = $time->withMinutes(32);

        self::assertEquals('01:23:45', $time->asString());
        self::assertEquals('01:32:45', $newTime->asString());
    }

    public function testWithSeconds() : void
    {
        $time = new Time(1, 23, 45);

        $newTime = $time->withSeconds(54);

        self::assertEquals('01:23:45', $time->asString());
        self::assertEquals('01:23:54', $newTime->asString());
    }

    public function testAsString() : void
    {
        $time = new Time(12, 34, 56);

        self::assertSame('12:34:56', $time->asString());
    }

    public function testJsonSerialize() : void
    {
        $time = new Time(20, 30, 15);

        self::assertJsonStringEqualsJsonFile(
            __DIR__ . '/Expected/Time/testJsonSerialize.json',
            json_encode($time)
        );
    }
}
