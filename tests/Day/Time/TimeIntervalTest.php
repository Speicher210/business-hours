<?php

declare(strict_types = 1);

namespace Speicher210\BusinessHours\Test\Day\Time;

use PHPUnit\Framework\TestCase;
use Speicher210\BusinessHours\Day\Time\Time;
use Speicher210\BusinessHours\Day\Time\TimeInterval;

class TimeIntervalTest extends TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The opening time "08:00:00" must be before the closing time "08:00:00".
     */
    public function testConstructorOpeningEqualClosing()
    {
        new TimeInterval(new Time(8, 0), new Time(8, 0));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The opening time "18:00:00" must be before the closing time "08:00:00".
     */
    public function testConstructorOpeningAfterClosing()
    {
        new TimeInterval(new Time(18, 0), new Time(8, 0));
    }

    public function testFromString()
    {
        $interval = TimeInterval::fromString('08:00', '18:30');

        $this->assertEquals(8, $interval->getStart()->getHours());
        $this->assertEquals(0, $interval->getStart()->getMinutes());

        $this->assertEquals(18, $interval->getEnd()->getHours());
        $this->assertEquals(30, $interval->getEnd()->getMinutes());
    }

    public static function dataProviderTestContains()
    {
        $interval = TimeInterval::fromString('08:00', '18:30');

        return [
            [$interval, 8, 0, true],
            [$interval, 18, 30, true],
            [$interval, 9, 0, true],
            [$interval, 7, 59, false],
            [$interval, 18, 31, false],
        ];
    }

    /**
     * @dataProvider dataProviderTestContains
     *
     * @param TimeInterval $interval The interval to test.
     * @param integer $hours The hours to test.
     * @param integer $minutes The minutes to test.
     * @param boolean $expected The expected value.
     */
    public function testContains(TimeInterval $interval, $hours, $minutes, $expected)
    {
        $this->assertEquals($interval->contains(new Time($hours, $minutes)), $expected);
    }

    public function testJsonSerialize()
    {
        $interval = TimeInterval::fromString('08:00:01', '18:30:02');

        $this->assertJsonStringEqualsJsonFile(
            __DIR__ . '/Expected/TimeInterval/testJsonSerialize.json',
            \json_encode($interval)
        );
    }

    public function testCloning()
    {
        $original = TimeInterval::fromString('08:00', '18:30');
        $clone = clone $original;

        $this->assertEquals($original, $clone);
        $this->assertNotSame($original, $clone);

        $this->assertNotSame($original->getStart(), $clone->getStart());
        $this->assertNotSame($original->getEnd(), $clone->getEnd());
    }
}
