<?php

namespace Speicher210\BusinessHours\Test;

use Speicher210\BusinessHours\Business;
use Speicher210\BusinessHours\Day;
use Speicher210\BusinessHours\DayInterface;

/**
 * Test class for Business.
 */
class BusinessTest extends \PHPUnit_Framework_TestCase
{
    public function testWithin()
    {
        $business = new Business(
            [
                new Day(DayInterface::WEEK_DAY_MONDAY, [['09:00', '13:00'], ['14:00', '17:00']]),
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
        $tz = date_default_timezone_get();
        date_default_timezone_set('Europe/Paris');

        $business = new Business(
            [
                new Day(DayInterface::WEEK_DAY_FRIDAY, [['10:00', '13:00'], ['14:00', '17:00']]),
            ]
        );

        // "2015-05-25 22:00:00" in Europe/Paris
        $date = new \DateTime('2015-05-25 10:00', new \DateTimeZone('Pacific/Tahiti'));

        $this->assertFalse($business->within($date));

        date_default_timezone_set($tz);
    }

    public function testClosestDateInterval()
    {
        $business = new Business(
            [
                new Day(DayInterface::WEEK_DAY_MONDAY, [['09:00', '13:00'], ['14:00', '17:00']]),
                new Day(DayInterface::WEEK_DAY_FRIDAY, [['10:00', '13:00'], ['14:00', '17:00']]),
            ]
        );

        // Withing working hours
        $target = new \DateTime('2015-05-11 10:00'); // Monday
        $dateInterval = $business->closestDateInterval($target);
        $this->assertEquals('2015-05-11 09:00:00', $dateInterval[0]->format('Y-m-d H:i:s')); // Monday
        $this->assertEquals('2015-05-11 13:00:00', $dateInterval[1]->format('Y-m-d H:i:s')); // Monday

        // The next day
        $target = new \DateTime('2015-05-12 17:30'); // Tuesday
        $dateInterval = $business->closestDateInterval($target);
        $this->assertEquals('2015-05-15 10:00:00', $dateInterval[0]->format('Y-m-d H:i:s')); // Friday
        $this->assertEquals('2015-05-15 13:00:00', $dateInterval[1]->format('Y-m-d H:i:s')); // Friday

        // Next week
        $target = new \DateTime('2015-05-15 17:30'); // Friday
        $dateInterval = $business->closestDateInterval($target);
        $this->assertEquals('2015-05-18 09:00:00', $dateInterval[0]->format('Y-m-d H:i:s')); // Next Monday
        $this->assertEquals('2015-05-18 13:00:00', $dateInterval[1]->format('Y-m-d H:i:s')); // Next Monday
    }

    public function testGetNextChangeDateTime()
    {
        $business = new Business(
            [
                new Day(DayInterface::WEEK_DAY_MONDAY, [['09:00', '13:00'], ['14:00', '17:00']]),
                new Day(DayInterface::WEEK_DAY_FRIDAY, [['10:00', '13:00'], ['14:00', '17:00']]),
            ]
        );

        // Withing working hours
        $target = new \DateTime('2015-05-11 10:00'); // Monday
        $date = $business->getNextChangeDateTime($target);
        $this->assertEquals('2015-05-11 13:00:00', $date->format('Y-m-d H:i:s')); // Monday

        // The next day
        $target = new \DateTime('2015-05-12 17:30'); // Tuesday
        $date = $business->getNextChangeDateTime($target);
        $this->assertEquals('2015-05-15 10:00:00', $date->format('Y-m-d H:i:s')); // Friday

        // Next week
        $target = new \DateTime('2015-05-15 17:30'); // Friday
        $date = $business->getNextChangeDateTime($target);
        $this->assertEquals('2015-05-18 09:00:00', $date->format('Y-m-d H:i:s')); // Next Monday
    }
}
