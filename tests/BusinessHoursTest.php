<?php

declare(strict_types = 1);

namespace Speicher210\BusinessHours\Test;

use PHPUnit\Framework\TestCase;
use Speicher210\BusinessHours\BusinessHours;
use Speicher210\BusinessHours\Day\DayBuilder;
use Speicher210\BusinessHours\Day\DayInterface;

class BusinessHoursTest extends TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage At least one day must be added.
     */
    public function testExceptionIsThrownIfNoDaysAreSet()
    {
        new BusinessHours([]);
    }

    public function testWithin()
    {
        $business = new BusinessHours(
            [
                DayBuilder::fromArray(DayInterface::WEEK_DAY_MONDAY, [['09:00', '13:00'], ['14:00', '17:00']]),
            ]
        );

        $this->assertTrue($business->within(new \DateTime('2015-05-11 10:00'))); // Monday
        $this->assertTrue($business->within(new \DateTime('2015-05-11 17:00')));

        $this->assertFalse($business->within(new \DateTime('2015-05-11 18:00'))); // Monday
        $this->assertFalse($business->within(new \DateTime('2015-05-12 10:00'))); // Tuesday
        $this->assertFalse(
            $business->within(new \DateTime('2015-05-11 13:00:25'))
        ); // Monday, seconds outside business hours
    }

    public function testWithinCustomTimezone()
    {
        $tz = \date_default_timezone_get();
        \date_default_timezone_set('Europe/Paris');

        $business = new BusinessHours(
            [
                DayBuilder::fromArray(DayInterface::WEEK_DAY_FRIDAY, [['10:00', '13:00'], ['14:00', '17:00']]),
            ]
        );

        // "2015-05-25 22:00:00" in Europe/Paris
        $date = new \DateTime('2015-05-25 10:00:00', new \DateTimeZone('Pacific/Tahiti'));

        $this->assertFalse($business->within($date));

        \date_default_timezone_set($tz);
    }

    public static function dataProviderTestGetNextChangeDateTime()
    {
        $utcTimeZone = new \DateTimeZone('UTC');
        $business = new BusinessHours(
            [
                DayBuilder::fromArray(DayInterface::WEEK_DAY_MONDAY, [['09:00', '13:00'], ['14:00', '17:00']]),
                DayBuilder::fromArray(DayInterface::WEEK_DAY_WEDNESDAY, [['09:00', '12:00'], ['12:30', '13:30'], ['14:00', '17:00']]),
                DayBuilder::fromArray(DayInterface::WEEK_DAY_FRIDAY, [['10:00', '13:00'], ['14:00', '17:00']]),
            ],
            $utcTimeZone
        );

        return [
            // Monday
            [$business, new \DateTime('2016-03-07 13:00:00'), new \DateTime('2016-03-07 10:00:00', $utcTimeZone)],
            // Friday / Tuesday
            [$business, new \DateTime('2016-03-11 10:00:00'), new \DateTime('2016-03-10 17:30:00', $utcTimeZone)],
            // Monday / Friday
            [$business, new \DateTime('2016-03-28 09:00:00'), new \DateTime('2016-03-25 17:30:00', $utcTimeZone)],
            // Wednesday
            [$business, new \DateTime('2016-03-30 12:30:00'), new \DateTime('2016-03-30 12:15:00', $utcTimeZone)],
            // Monday
            [
                $business,
                new \DateTime('2016-03-28 09:00:00'),
                new \DateTime('2016-03-28 10:00:00', new \DateTimeZone('Europe/Bucharest')),
            ],
            [$business, new \DateTime('2016-03-28 09:00:00'), new \DateTime('2016-03-28 09:00:00', $utcTimeZone)],
            [$business, new \DateTime('2016-03-28 17:00:00'), new \DateTime('2016-03-28 17:00:00', $utcTimeZone)],
        ];
    }

    /**
     * @dataProvider dataProviderTestGetNextChangeDateTime
     *
     * @param BusinessHours $business
     * @param \DateTime $expectedDateTime
     * @param \DateTime $context
     */
    public function testGetNextChangeDateTime(BusinessHours $business, \DateTime $expectedDateTime, \DateTime $context)
    {
        $date = $business->getNextChangeDateTime($context);
        $this->assertEquals($expectedDateTime, $date);
    }

    public static function dataProviderTestGetPreviousChangeDateTime()
    {
        $utcTimeZone = new \DateTimeZone('UTC');
        $business = new BusinessHours(
            [
                DayBuilder::fromArray(DayInterface::WEEK_DAY_MONDAY, [['09:00', '13:00'], ['14:00', '17:00']]),
                DayBuilder::fromArray(DayInterface::WEEK_DAY_WEDNESDAY, [['09:00', '12:00'], ['12:30', '13:30'], ['14:00', '17:00']]),
                DayBuilder::fromArray(DayInterface::WEEK_DAY_FRIDAY, [['10:00', '13:00'], ['14:00', '17:00']]),
            ],
            $utcTimeZone
        );

        return [
            // Monday
            [$business, new \DateTime('2016-03-07 09:00:00'), new \DateTime('2016-03-07 10:00:00', $utcTimeZone)],
            // Friday / Thursday
            [$business, new \DateTime('2016-03-09 17:00:00'), new \DateTime('2016-03-10 17:30:00', $utcTimeZone)],
            // Monday / Friday
            [$business, new \DateTime('2016-03-25 17:00:00'), new \DateTime('2016-03-25 17:30:00', $utcTimeZone)],
            // Wednesday
            [$business, new \DateTime('2016-03-30 12:00:00'), new \DateTime('2016-03-30 12:15:00', $utcTimeZone)],
            // Monday
            [
                $business,
                new \DateTime('2016-03-25 17:00:00'),
                new \DateTime('2016-03-28 10:00:00', new \DateTimeZone('Europe/Bucharest')),
            ],
            [$business, new \DateTime('2016-03-28 09:00:00'), new \DateTime('2016-03-28 09:00:00', $utcTimeZone)],
            [$business, new \DateTime('2016-03-28 17:00:00'), new \DateTime('2016-03-28 17:00:00', $utcTimeZone)],
        ];
    }

    /**
     * @dataProvider dataProviderTestGetPreviousChangeDateTime
     *
     * @param BusinessHours $business
     * @param \DateTime $expectedDateTime
     * @param \DateTime $context
     */
    public function testGetPreviousChangeDateTime(BusinessHours $business, \DateTime $expectedDateTime, \DateTime $context)
    {
        $date = $business->getPreviousChangeDateTime($context);
        $this->assertEquals($expectedDateTime, $date);
    }

    public function testJsonSerialize()
    {
        $business = new BusinessHours(
            [
                DayBuilder::fromArray(DayInterface::WEEK_DAY_MONDAY, [['09:00', '13:00'], ['14:00', '17:00']]),
                DayBuilder::fromArray(DayInterface::WEEK_DAY_FRIDAY, [['10:00', '13:00'], ['14:00', '17:00']])
            ],
            new \DateTimeZone('Europe/London')
        );

        $this->assertJsonStringEqualsJsonFile(
            __DIR__ . '/Expected/Business/testJsonSerialize.json',
            \json_encode($business)
        );
    }

    public function testCloning()
    {
        $original = new BusinessHours(
            [
                DayBuilder::fromArray(DayInterface::WEEK_DAY_MONDAY, [['09:00', '13:00'], ['14:00', '17:00']]),
                DayBuilder::fromArray(DayInterface::WEEK_DAY_FRIDAY, [['10:00', '13:00'], ['14:00', '17:00']])
            ],
            new \DateTimeZone('Europe/London')
        );

        $clone = clone $original;

        $this->assertEquals($original, $clone);
        $this->assertNotSame($original, $clone);

        $this->assertNotSame($original->getDays()[0], $clone->getDays()[0]);
    }
}
