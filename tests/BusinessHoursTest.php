<?php

declare(strict_types=1);

namespace Speicher210\BusinessHours\Test;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Psl\Json;
use Speicher210\BusinessHours\BusinessHours;
use Speicher210\BusinessHours\Day\DayBuilder;
use Speicher210\BusinessHours\Day\DayInterface;

use function date_default_timezone_get;
use function date_default_timezone_set;

class BusinessHoursTest extends TestCase
{
    public function testExceptionIsThrownIfNoDaysAreSet(): void
    {
        $this->expectExceptionMessage('At least one day must be added.');
        $this->expectException(InvalidArgumentException::class);
        new BusinessHours([]);
    }

    public function testWithin(): void
    {
        $business = new BusinessHours(
            [
                DayBuilder::fromArray(DayInterface::WEEK_DAY_MONDAY, [['09:00', '13:00'], ['14:00', '17:00']]),
            ]
        );

        self::assertTrue($business->within(new DateTime('2015-05-11 10:00'))); // Monday
        self::assertTrue($business->within(new DateTime('2015-05-11 17:00')));

        self::assertFalse($business->within(new DateTime('2015-05-11 18:00'))); // Monday
        self::assertFalse($business->within(new DateTime('2015-05-12 10:00'))); // Tuesday
        self::assertFalse(
            $business->within(new DateTime('2015-05-11 13:00:25'))
        ); // Monday, seconds outside business hours
    }

    /**
     * @return mixed[]
     */
    public static function dataProviderTestWithinCustomTimezone(): array
    {
        return [
            // "2015-05-25 22:00:00" in Europe/Paris
            [new DateTime('2015-05-25 10:00:00', new DateTimeZone('Pacific/Tahiti'))],
            [new DateTimeImmutable('2015-05-25 10:00:00', new DateTimeZone('Pacific/Tahiti'))],
        ];
    }

    /**
     * @dataProvider dataProviderTestWithinCustomTimezone
     */
    public function testWithinCustomTimezone(DateTimeInterface $date): void
    {
        $tz = date_default_timezone_get();
        date_default_timezone_set('Europe/Paris');

        $business = new BusinessHours(
            [
                DayBuilder::fromArray(DayInterface::WEEK_DAY_MONDAY, [['10:00', '13:00'], ['14:00', '17:00']]),
            ]
        );

        self::assertFalse($business->within($date));

        date_default_timezone_set($tz);
    }

    /**
     * @return mixed[]
     */
    public static function dataProviderTestGetNextChangeDateTime(): array
    {
        $utcTimeZone = new DateTimeZone('UTC');
        $business    = new BusinessHours(
            [
                DayBuilder::fromArray(DayInterface::WEEK_DAY_MONDAY, [['09:00', '13:00'], ['14:00', '17:00']]),
                DayBuilder::fromArray(DayInterface::WEEK_DAY_WEDNESDAY, [['09:00', '12:00'], ['12:30', '13:30'], ['14:00', '17:00']]),
                DayBuilder::fromArray(DayInterface::WEEK_DAY_FRIDAY, [['10:00', '13:00'], ['14:00', '17:00']]),
            ],
            $utcTimeZone
        );

        return [
            // Monday
            [$business, new DateTime('2016-03-07 13:00:00'), new DateTime('2016-03-07 10:00:00', $utcTimeZone)],
            [$business, new DateTimeImmutable('2016-03-07 13:00:00'), new DateTimeImmutable('2016-03-07 10:00:00', $utcTimeZone)],
            // Friday / Tuesday
            [$business, new DateTime('2016-03-11 10:00:00'), new DateTime('2016-03-10 17:30:00', $utcTimeZone)],
            [$business, new DateTimeImmutable('2016-03-11 10:00:00'), new DateTimeImmutable('2016-03-10 17:30:00', $utcTimeZone)],
            // Monday / Friday
            [$business, new DateTime('2016-03-28 09:00:00'), new DateTime('2016-03-25 17:30:00', $utcTimeZone)],
            [$business, new DateTimeImmutable('2016-03-28 09:00:00'), new DateTimeImmutable('2016-03-25 17:30:00', $utcTimeZone)],
            // Wednesday
            [$business, new DateTime('2016-03-30 12:30:00'), new DateTime('2016-03-30 12:15:00', $utcTimeZone)],
            [$business, new DateTimeImmutable('2016-03-30 12:30:00'), new DateTimeImmutable('2016-03-30 12:15:00', $utcTimeZone)],
            // Monday
            [
                $business,
                new DateTime('2016-03-28 09:00:00'),
                new DateTime('2016-03-28 10:00:00', new DateTimeZone('Europe/Bucharest')),
            ],
            [
                $business,
                new DateTimeImmutable('2016-03-28 09:00:00'),
                new DateTimeImmutable('2016-03-28 10:00:00', new DateTimeZone('Europe/Bucharest')),
            ],
            [$business, new DateTime('2016-03-28 09:00:00'), new DateTime('2016-03-28 09:00:00', $utcTimeZone)],
            [$business, new DateTimeImmutable('2016-03-28 09:00:00'), new DateTimeImmutable('2016-03-28 09:00:00', $utcTimeZone)],
            [$business, new DateTime('2016-03-28 17:00:00'), new DateTime('2016-03-28 17:00:00', $utcTimeZone)],
            [$business, new DateTimeImmutable('2016-03-28 17:00:00'), new DateTimeImmutable('2016-03-28 17:00:00', $utcTimeZone)],
        ];
    }

    /**
     * @dataProvider dataProviderTestGetNextChangeDateTime
     */
    public function testGetNextChangeDateTime(BusinessHours $business, DateTimeInterface $expectedDateTime, DateTimeInterface $context): void
    {
        $date = $business->getNextChangeDateTime($context);
        self::assertEquals($expectedDateTime, $date);
    }

    /**
     * @return mixed[]
     */
    public static function dataProviderTestGetPreviousChangeDateTime(): array
    {
        $utcTimeZone = new DateTimeZone('UTC');
        $business    = new BusinessHours(
            [
                DayBuilder::fromArray(DayInterface::WEEK_DAY_MONDAY, [['09:00', '13:00'], ['14:00', '17:00']]),
                DayBuilder::fromArray(DayInterface::WEEK_DAY_WEDNESDAY, [['09:00', '12:00'], ['12:30', '13:30'], ['14:00', '17:00']]),
                DayBuilder::fromArray(DayInterface::WEEK_DAY_FRIDAY, [['10:00', '13:00'], ['14:00', '17:00']]),
            ],
            $utcTimeZone
        );

        return [
            // Monday
            [$business, new DateTime('2016-03-07 09:00:00'), new DateTime('2016-03-07 10:00:00', $utcTimeZone)],
            [$business, new DateTimeImmutable('2016-03-07 09:00:00'), new DateTimeImmutable('2016-03-07 10:00:00', $utcTimeZone)],
            // Friday / Thursday
            [$business, new DateTime('2016-03-09 17:00:00'), new DateTime('2016-03-10 17:30:00', $utcTimeZone)],
            [$business, new DateTimeImmutable('2016-03-09 17:00:00'), new DateTimeImmutable('2016-03-10 17:30:00', $utcTimeZone)],
            // Monday / Friday
            [$business, new DateTime('2016-03-25 17:00:00'), new DateTime('2016-03-25 17:30:00', $utcTimeZone)],
            [$business, new DateTimeImmutable('2016-03-25 17:00:00'), new DateTimeImmutable('2016-03-25 17:30:00', $utcTimeZone)],
            // Wednesday
            [$business, new DateTime('2016-03-30 12:00:00'), new DateTime('2016-03-30 12:15:00', $utcTimeZone)],
            [$business, new DateTimeImmutable('2016-03-30 12:00:00'), new DateTimeImmutable('2016-03-30 12:15:00', $utcTimeZone)],
            // Monday
            [
                $business,
                new DateTime('2016-03-25 17:00:00'),
                new DateTime('2016-03-28 10:00:00', new DateTimeZone('Europe/Bucharest')),
            ],
            [
                $business,
                new DateTimeImmutable('2016-03-25 17:00:00'),
                new DateTimeImmutable('2016-03-28 10:00:00', new DateTimeZone('Europe/Bucharest')),
            ],
            [$business, new DateTime('2016-03-28 09:00:00'), new DateTime('2016-03-28 09:00:00', $utcTimeZone)],
            [$business, new DateTimeImmutable('2016-03-28 09:00:00'), new DateTimeImmutable('2016-03-28 09:00:00', $utcTimeZone)],
            [$business, new DateTime('2016-03-28 17:00:00'), new DateTime('2016-03-28 17:00:00', $utcTimeZone)],
            [$business, new DateTimeImmutable('2016-03-28 17:00:00'), new DateTimeImmutable('2016-03-28 17:00:00', $utcTimeZone)],
        ];
    }

    /**
     * @dataProvider dataProviderTestGetPreviousChangeDateTime
     */
    public function testGetPreviousChangeDateTime(BusinessHours $business, DateTimeInterface $expectedDateTime, DateTimeInterface $context): void
    {
        $date = $business->getPreviousChangeDateTime($context);
        self::assertEquals($expectedDateTime, $date);
    }

    public function testJsonSerialize(): void
    {
        $business = new BusinessHours(
            [
                DayBuilder::fromArray(DayInterface::WEEK_DAY_MONDAY, [['09:00', '13:00'], ['14:00', '17:00']]),
                DayBuilder::fromArray(DayInterface::WEEK_DAY_FRIDAY, [['10:00', '13:00'], ['14:00', '17:00']]),
            ],
            new DateTimeZone('Europe/London')
        );

        self::assertJsonStringEqualsJsonFile(
            __DIR__ . '/Expected/Business/testJsonSerialize.json',
            Json\encode($business)
        );
    }

    public function testCloning(): void
    {
        $original = new BusinessHours(
            [
                DayBuilder::fromArray(DayInterface::WEEK_DAY_MONDAY, [['09:00', '13:00'], ['14:00', '17:00']]),
                DayBuilder::fromArray(DayInterface::WEEK_DAY_FRIDAY, [['10:00', '13:00'], ['14:00', '17:00']]),
            ],
            new DateTimeZone('Europe/London')
        );

        $clone = clone $original;

        self::assertEquals($original, $clone);
        self::assertNotSame($original, $clone);

        self::assertNotSame($original->getDays()[0], $clone->getDays()[0]);
    }
}
