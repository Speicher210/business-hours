<?php

declare(strict_types = 1);

namespace Speicher210\BusinessHours\Test\Day\Time;

use PHPUnit\Framework\TestCase;
use Speicher210\BusinessHours\Day\Time\TimeBuilder;

class TimeBuilderTest extends TestCase
{
    public static function dataProviderTestFromStringInvalid()
    {
        return [
            ['invalid'],
            ['24:00:01'],
            ['25:00'],
            [''],
            [null],
        ];
    }

    /**
     * @dataProvider dataProviderTestFromStringInvalid
     *
     * @param mixed $string The string to test.
     */
    public function testFromStringInvalid($string)
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(\sprintf('Invalid time "%s".', $string));

        TimeBuilder::fromString($string);
    }

    public static function dataProviderTestFromString(): array
    {
        return [
            ['2pm', 14, 0, 0],
            ['11:00', 11, 0, 0],
            ['11:00:11', 11, 0, 11],
            ['23:15', 23, 15, 0],
            ['24:00', 24, 0, 0]
        ];
    }

    /**
     * @dataProvider dataProviderTestFromString
     *
     * @param string $string The time string to test.
     * @param integer $expectedHours The expected hours.
     * @param integer $expectedMinutes The expected minutes.
     * @param integer $expectedSeconds The expected seconds.
     */
    public function testFromString($string, $expectedHours, $expectedMinutes, $expectedSeconds)
    {
        $time = TimeBuilder::fromString($string);
        $this->assertEquals($expectedHours, $time->getHours());
        $this->assertEquals($expectedMinutes, $time->getMinutes());
        $this->assertEquals($expectedSeconds, $time->getSeconds());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Array is not valid.
     */
    public function testFromArrayThrowsExceptionIfArrayStructureIsNotValid()
    {
        TimeBuilder::fromArray([[]]);
    }

    public static function dataProviderTestFromDate(): array
    {
        return [
            [new \DateTime('2 AM'), 2, 0, 0],
            [new \DateTime('3:20:15 PM'), 15, 20, 15],
        ];
    }

    /**
     * @dataProvider dataProviderTestFromDate
     *
     * @param \DateTime $date The date and time to test.
     * @param integer $expectedHours The expected hours.
     * @param integer $expectedMinutes The expected minutes.
     * @param integer $expectedSeconds The expected seconds.
     */
    public function testFromDate(\DateTime $date, $expectedHours, $expectedMinutes, $expectedSeconds)
    {
        $time = TimeBuilder::fromDate($date);
        $this->assertEquals($expectedHours, $time->getHours());
        $this->assertEquals($expectedMinutes, $time->getMinutes());
        $this->assertEquals($expectedSeconds, $time->getSeconds());
    }

    public static function dataProviderTestFromSecondsInvalid()
    {
        return [
            [-1],
            [86401]
        ];
    }

    /**
     * @dataProvider dataProviderTestFromSecondsInvalid
     *
     * @param mixed $seconds The seconds to test.
     */
    public function testFromSecondsInvalid($seconds)
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(\sprintf('Invalid time "%s".', $seconds));

        TimeBuilder::fromSeconds($seconds);
    }

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
     *
     * @param integer $seconds The seconds integer to test.
     * @param integer $expectedHours The expected hours.
     * @param integer $expectedMinutes The expected minutes.
     * @param integer $expectedSeconds The expected seconds.
     */
    public function testFromSeconds(int $seconds, int $expectedHours, int $expectedMinutes, int $expectedSeconds)
    {
        $time = TimeBuilder::fromSeconds($seconds);
        $this->assertEquals($expectedHours, $time->getHours());
        $this->assertEquals($expectedMinutes, $time->getMinutes());
        $this->assertEquals($expectedSeconds, $time->getSeconds());
    }
}
