<?php

declare(strict_types=1);

namespace Speicher210\BusinessHours\Test\Day\Time;

use DateTime;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Speicher210\BusinessHours\Day\Time\TimeBuilder;
use function Safe\sprintf;

class TimeBuilderTest extends TestCase
{
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

        TimeBuilder::fromString($string);
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
     * @param string $string          The time string to test.
     * @param int    $expectedHours   The expected hours.
     * @param int    $expectedMinutes The expected minutes.
     * @param int    $expectedSeconds The expected seconds.
     *
     * @dataProvider dataProviderTestFromString
     */
    public function testFromString(string $string, int $expectedHours, int $expectedMinutes, int $expectedSeconds) : void
    {
        $time = TimeBuilder::fromString($string);
        self::assertEquals($expectedHours, $time->getHours());
        self::assertEquals($expectedMinutes, $time->getMinutes());
        self::assertEquals($expectedSeconds, $time->getSeconds());
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
     * @param DateTime $date            The date and time to test.
     * @param int      $expectedHours   The expected hours.
     * @param int      $expectedMinutes The expected minutes.
     * @param int      $expectedSeconds The expected seconds.
     *
     * @dataProvider dataProviderTestFromDate
     */
    public function testFromDate(DateTime $date, int $expectedHours, int $expectedMinutes, int $expectedSeconds) : void
    {
        $time = TimeBuilder::fromDate($date);
        self::assertEquals($expectedHours, $time->getHours());
        self::assertEquals($expectedMinutes, $time->getMinutes());
        self::assertEquals($expectedSeconds, $time->getSeconds());
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
     * @param mixed $seconds The seconds to test.
     *
     * @dataProvider dataProviderTestFromSecondsInvalid
     */
    public function testFromSecondsInvalid($seconds) : void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('Invalid time "%s".', $seconds));

        TimeBuilder::fromSeconds($seconds);
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
     * @param int $seconds         The seconds integer to test.
     * @param int $expectedHours   The expected hours.
     * @param int $expectedMinutes The expected minutes.
     * @param int $expectedSeconds The expected seconds.
     *
     * @dataProvider dataProviderTestFromSeconds
     */
    public function testFromSeconds(int $seconds, int $expectedHours, int $expectedMinutes, int $expectedSeconds) : void
    {
        $time = TimeBuilder::fromSeconds($seconds);
        self::assertEquals($expectedHours, $time->getHours());
        self::assertEquals($expectedMinutes, $time->getMinutes());
        self::assertEquals($expectedSeconds, $time->getSeconds());
    }
}
