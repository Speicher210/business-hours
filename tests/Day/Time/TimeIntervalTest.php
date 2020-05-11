<?php

declare(strict_types=1);

namespace Speicher210\BusinessHours\Test\Day\Time;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Speicher210\BusinessHours\Day\Time\Time;
use Speicher210\BusinessHours\Day\Time\TimeInterval;
use function json_encode;

class TimeIntervalTest extends TestCase
{
    public function testConstructorOpeningEqualClosing() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The opening time "08:00:00" must be before the closing time "08:00:00".');

        new TimeInterval(new Time(8, 0), new Time(8, 0));
    }

    public function testConstructorOpeningAfterClosing() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The opening time "18:00:00" must be before the closing time "08:00:00".');

        new TimeInterval(new Time(18, 0), new Time(8, 0));
    }

    public function testFromString() : void
    {
        $interval = TimeInterval::fromString('08:00', '18:30');

        $this->assertEquals(8, $interval->getStart()->getHours());
        $this->assertEquals(0, $interval->getStart()->getMinutes());

        $this->assertEquals(18, $interval->getEnd()->getHours());
        $this->assertEquals(30, $interval->getEnd()->getMinutes());
    }

    /**
     * @return mixed[]
     */
    public static function dataProviderTestContains() : array
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
     * @param TimeInterval $interval The interval to test.
     * @param int          $hours    The hours to test.
     * @param int          $minutes  The minutes to test.
     * @param bool         $expected The expected value.
     *
     * @dataProvider dataProviderTestContains
     */
    public function testContains(TimeInterval $interval, int $hours, int $minutes, bool $expected) : void
    {
        $this->assertEquals($interval->contains(new Time($hours, $minutes)), $expected);
    }

    public function testJsonSerialize() : void
    {
        $interval = TimeInterval::fromString('08:00:01', '18:30:02');

        $this->assertJsonStringEqualsJsonFile(
            __DIR__ . '/Expected/TimeInterval/testJsonSerialize.json',
            json_encode($interval)
        );
    }

    public function testCloning() : void
    {
        $original = TimeInterval::fromString('08:00', '18:30');
        $clone    = clone $original;

        $this->assertEquals($original, $clone);
        $this->assertNotSame($original, $clone);

        $this->assertNotSame($original->getStart(), $clone->getStart());
        $this->assertNotSame($original->getEnd(), $clone->getEnd());
    }
}
