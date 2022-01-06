<?php

declare(strict_types=1);

namespace Speicher210\BusinessHours\Test\Day\Time;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Psl\Json;
use Speicher210\BusinessHours\Day\Time\Time;
use Speicher210\BusinessHours\Day\Time\TimeInterval;

class TimeIntervalTest extends TestCase
{
    public function testConstructorOpeningEqualClosing(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The opening time "08:00:00" must be before the closing time "08:00:00".');

        new TimeInterval(new Time(8, 0), new Time(8, 0));
    }

    public function testConstructorOpeningAfterClosing(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The opening time "18:00:00" must be before the closing time "08:00:00".');

        new TimeInterval(new Time(18, 0), new Time(8, 0));
    }

    public function testFromString(): void
    {
        $interval = TimeInterval::fromString('08:00', '18:30');

        self::assertEquals(8, $interval->getStart()->hours());
        self::assertEquals(0, $interval->getStart()->minutes());

        self::assertEquals(18, $interval->getEnd()->hours());
        self::assertEquals(30, $interval->getEnd()->minutes());
    }

    /**
     * @return mixed[]
     */
    public static function dataProviderTestContains(): array
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
    public function testContains(TimeInterval $interval, int $hours, int $minutes, bool $expected): void
    {
        self::assertEquals($interval->contains(new Time($hours, $minutes)), $expected);
    }

    public function testJsonSerialize(): void
    {
        $interval = TimeInterval::fromString('08:00:01', '18:30:02');

        self::assertJsonStringEqualsJsonFile(
            __DIR__ . '/Expected/TimeInterval/testJsonSerialize.json',
            Json\encode($interval)
        );
    }

    public function testCloning(): void
    {
        $original = TimeInterval::fromString('08:00', '18:30');
        $clone    = clone $original;

        self::assertEquals($original, $clone);
        self::assertNotSame($original, $clone);

        self::assertNotSame($original->getStart(), $clone->getStart());
        self::assertNotSame($original->getEnd(), $clone->getEnd());
    }
}
