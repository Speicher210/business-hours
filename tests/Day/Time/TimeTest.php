<?php

declare(strict_types = 1);

namespace Speicher210\BusinessHours\Test\Day\Time;

use PHPUnit\Framework\TestCase;
use Speicher210\BusinessHours\Day\Time\Time;

class TimeTest extends TestCase
{
    public static function dataProviderTestCreateTimeWithInvalidData()
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
     * @dataProvider dataProviderTestCreateTimeWithInvalidData
     *
     * @param integer $hours The hours.
     * @param integer $minutes The minutes.
     * @param integer $seconds The seconds.
     */
    public function testCreateTimeWithInvalidData($hours, $minutes, $seconds)
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid time');

        new Time($hours, $minutes, $seconds);
    }

    public static function dataProviderTestIsAfterOrEqual()
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
     *
     * @param Time $time The date and time to test.
     * @param integer $hours The hours to test.
     * @param integer $minutes The minutes to test.
     * @param boolean $expected The expected value.
     */
    public function testIsAfterOrEqual(Time $time, $hours, $minutes, $expected)
    {
        $this->assertEquals($time->isAfterOrEqual(new Time($hours, $minutes)), $expected);
    }

    public static function dataProviderTestIsBeforeOrEqual()
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
     *
     * @param Time $time The date and time to test.
     * @param integer $hours The hours to test.
     * @param integer $minutes The minutes to test.
     * @param boolean $expected The expected value.
     */
    public function testIsBeforeOrEqual(Time $time, $hours, $minutes, $expected)
    {
        $this->assertEquals($time->isBeforeOrEqual(new Time($hours, $minutes)), $expected);
    }

    public static function dataProviderTestIsEqual()
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
     *
     * @param Time $time The date and time to test.
     * @param integer $hours The hours to test.
     * @param integer $minutes The minutes to test.
     * @param boolean $expected The expected value.
     */
    public function testIsEqual(Time $time, $hours, $minutes, $expected)
    {
        $this->assertEquals($time->isEqual(new Time($hours, $minutes)), $expected);
    }

    public static function dataProviderTestToSeconds()
    {
        return [
            [72000, 20, 0, 0],
            [34200, 9, 30, 0],
            [45296, 12, 34, 56],
        ];
    }

    /**
     * @dataProvider dataProviderTestToSeconds
     *
     * @param integer $expectedTimeRepresentationInSeconds The expected representation of time in seconds.
     * @param integer $hours The hours to test.
     * @param integer $minutes The minutes to test.
     * @param integer $seconds The seconds to test.
     */
    public function testToSeconds($expectedTimeRepresentationInSeconds, $hours, $minutes, $seconds)
    {
        $time = new Time($hours, $minutes, $seconds);
        $this->assertEquals($expectedTimeRepresentationInSeconds, $time->toSeconds());
    }

    public function testJsonSerialize()
    {
        $time = new Time(20, 30, 15);

        $this->assertJsonStringEqualsJsonFile(
            __DIR__ . '/Expected/Time/testJsonSerialize.json',
            \json_encode($time)
        );
    }
}
