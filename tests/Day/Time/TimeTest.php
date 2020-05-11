<?php

declare(strict_types=1);

namespace Speicher210\BusinessHours\Test\Day\Time;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Speicher210\BusinessHours\Day\Time\Time;
use function json_encode;

class TimeTest extends TestCase
{
    /**
     * @return mixed[]
     */
    public static function dataProviderTestCreateTimeWithInvalidData() : array
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
     * @param int $hours   The hours.
     * @param int $minutes The minutes.
     * @param int $seconds The seconds.
     *
     * @dataProvider dataProviderTestCreateTimeWithInvalidData
     */
    public function testCreateTimeWithInvalidData(int $hours, int $minutes, int $seconds) : void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid time');

        new Time($hours, $minutes, $seconds);
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
     * @param Time $time     The date and time to test.
     * @param int  $hours    The hours to test.
     * @param int  $minutes  The minutes to test.
     * @param bool $expected The expected value.
     *
     * @dataProvider dataProviderTestIsAfterOrEqual
     */
    public function testIsAfterOrEqual(Time $time, int $hours, int $minutes, bool $expected) : void
    {
        $this->assertEquals($time->isAfterOrEqual(new Time($hours, $minutes)), $expected);
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
     * @param Time $time     The date and time to test.
     * @param int  $hours    The hours to test.
     * @param int  $minutes  The minutes to test.
     * @param bool $expected The expected value.
     *
     * @dataProvider dataProviderTestIsBeforeOrEqual
     */
    public function testIsBeforeOrEqual(Time $time, int $hours, int $minutes, bool $expected) : void
    {
        $this->assertEquals($time->isBeforeOrEqual(new Time($hours, $minutes)), $expected);
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
     * @param Time $time     The date and time to test.
     * @param int  $hours    The hours to test.
     * @param int  $minutes  The minutes to test.
     * @param bool $expected The expected value.
     *
     * @dataProvider dataProviderTestIsEqual
     */
    public function testIsEqual(Time $time, int $hours, int $minutes, bool $expected) : void
    {
        $this->assertEquals($time->isEqual(new Time($hours, $minutes)), $expected);
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
     * @param int $expectedTimeRepresentationInSeconds The expected representation of time in seconds.
     * @param int $hours                               The hours to test.
     * @param int $minutes                             The minutes to test.
     * @param int $seconds                             The seconds to test.
     *
     * @dataProvider dataProviderTestToSeconds
     */
    public function testToSeconds(int $expectedTimeRepresentationInSeconds, int $hours, int $minutes, int $seconds) : void
    {
        $time = new Time($hours, $minutes, $seconds);
        $this->assertEquals($expectedTimeRepresentationInSeconds, $time->toSeconds());
    }

    public function testJsonSerialize() : void
    {
        $time = new Time(20, 30, 15);

        $this->assertJsonStringEqualsJsonFile(
            __DIR__ . '/Expected/Time/testJsonSerialize.json',
            json_encode($time)
        );
    }
}
