<?php

namespace Speicher210\BusinessHours\Test;

use Speicher210\BusinessHours\BusinessHours;
use Speicher210\BusinessHours\BusinessHoursBuilder;
use Speicher210\BusinessHours\Day\AllDay;
use Speicher210\BusinessHours\Day\Day;
use Speicher210\BusinessHours\Day\Time\TimeInterval;

class BusinessHoursBuilderTest extends \PHPUnit_Framework_TestCase
{
    public static function dataProviderTestFromArrayThrowsExceptionIfArrayStructureIsNotValid()
    {
        return array(
            array(array()),
            array(array('days' => array())),
            array(array('timezone' => 1))
        );
    }

    /**
     * @dataProvider dataProviderTestFromArrayThrowsExceptionIfArrayStructureIsNotValid
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Array is not valid.
     */
    public function testFromArrayThrowsExceptionIfArrayStructureIsNotValid($data)
    {
        BusinessHoursBuilder::fromAssociativeArray($data);
    }

    public function testFromArrayReturnsDay()
    {
        $data = array(
            'days' => array(
                array(
                    'dayOfWeek' => Day::WEEK_DAY_MONDAY,
                    'openingIntervals' => array(
                        array(
                            'start' => array('hours' => 10, 'minutes' => 10),
                            'end' => array('hours' => 18, 'minutes' => 0, 'seconds' => 30)
                        ),
                        array(
                            'start' => array('hours' => 18, 'minutes' => 30),
                            'end' => array('hours' => 19)
                        ),
                    )
                ),
                array(
                    'dayOfWeek' => Day::WEEK_DAY_FRIDAY,
                    'openingIntervals' => array(
                        array(
                            'start' => array('hours' => 0),
                            'end' => array('hours' => 24)
                        )
                    )
                )
            ),
            'timezone' => 'Europe/Berlin',
        );

        $actual = BusinessHoursBuilder::fromAssociativeArray($data);

        $days = array(
            new Day(
                Day::WEEK_DAY_MONDAY,
                array(
                    TimeInterval::fromString('10:10', '18:00:30'),
                    TimeInterval::fromString('18:30', '19:00')
                )
            ),
            new AllDay(Day::WEEK_DAY_FRIDAY)
        );
        $timezone = new \DateTimeZone('Europe/Berlin');
        $expected = new BusinessHours($days, $timezone);

        $this->assertEquals($expected, $actual);
    }

    public function testShiftToTimezoneWhenNewTimezoneIsTheSame()
    {
        $days = array(
            new Day(
                Day::WEEK_DAY_MONDAY,
                array(
                    TimeInterval::fromString('10:10', '18:00:30'),
                    TimeInterval::fromString('18:30', '19:00')
                )
            ),
            new AllDay(Day::WEEK_DAY_FRIDAY)
        );
        $timezone = new \DateTimeZone('Europe/Bucharest');
        $original = new BusinessHours($days, $timezone);

        $actual = BusinessHoursBuilder::shiftToTimezone($original, new \DateTimeZone('Europe/Bucharest'));

        $this->assertEquals($original, $actual);
        $this->assertNotSame($original, $actual);
    }

    public function testShiftToTimezoneWhenTimezoneShiftIsBackwards()
    {
        $this->markTestSkipped();

        $originalDays = array(
            new Day(
                Day::WEEK_DAY_MONDAY,
                array(
                    TimeInterval::fromString('00:00', '10:00:30'),
                    TimeInterval::fromString('18:30', '24:00')
                )
            ),
            new Day(
                Day::WEEK_DAY_TUESDAY,
                array(
                    TimeInterval::fromString('01:00', '24:00')
                )
            ),
            new Day(
                Day::WEEK_DAY_WEDNESDAY,
                array(
                    TimeInterval::fromString('00:00', '03:00')
                )
            ),
            new AllDay(Day::WEEK_DAY_FRIDAY)
        );
        $originalTimezone = new \DateTimeZone('Europe/Bucharest');
        $original = new BusinessHours($originalDays, $originalTimezone);

        $expectedDays = array(
            new Day(
                Day::WEEK_DAY_MONDAY,
                array(
                    TimeInterval::fromString('00:00', '09:00:30'),
                    TimeInterval::fromString('17:30', '23:00')
                )
            ),
            new AllDay(
                Day::WEEK_DAY_TUESDAY
            ),
            new Day(
                Day::WEEK_DAY_WEDNESDAY,
                array(
                    TimeInterval::fromString('00:00', '02:00')
                )
            ),
            new Day(
                Day::WEEK_DAY_THURSDAY,
                array(
                    TimeInterval::fromString('23:00', '24:00')
                )
            ),
            new Day(
                Day::WEEK_DAY_FRIDAY,
                array(
                    TimeInterval::fromString('00:00', '23:00')
                )
            ),
            new Day(
                Day::WEEK_DAY_SUNDAY,
                array(
                    TimeInterval::fromString('23:00', '24:00')
                )
            )
        );
        $expectedTimezone = new \DateTimeZone('Europe/Berlin');
        $expected = new BusinessHours($expectedDays, $expectedTimezone);

        $actual = BusinessHoursBuilder::shiftToTimezone($original, new \DateTimeZone('Europe/Berlin'));

        $this->assertEquals($expected, $actual);
    }

    public function testShiftToTimezoneWhenTimezoneShiftIsForward()
    {
        $this->markTestSkipped();

        $originalDays = array(
            new Day(
                Day::WEEK_DAY_MONDAY,
                array(
                    TimeInterval::fromString('00:00', '09:00:30'),
                    TimeInterval::fromString('17:30', '23:00')
                )
            ),
            new AllDay(
                Day::WEEK_DAY_TUESDAY
            ),
            new Day(
                Day::WEEK_DAY_WEDNESDAY,
                array(
                    TimeInterval::fromString('00:00', '02:00')
                )
            ),
            new Day(
                Day::WEEK_DAY_THURSDAY,
                array(
                    TimeInterval::fromString('23:00', '24:00')
                )
            ),
            new Day(
                Day::WEEK_DAY_FRIDAY,
                array(
                    TimeInterval::fromString('00:00', '23:00')
                )
            ),
            new Day(
                Day::WEEK_DAY_SUNDAY,
                array(
                    TimeInterval::fromString('23:00', '24:00')
                )
            )
        );
        $originalTimezone = new \DateTimeZone('Europe/Berlin');
        $original = new BusinessHours($originalDays, $originalTimezone);

        $expectedDays = array(
            new Day(
                Day::WEEK_DAY_MONDAY,
                array(
                    TimeInterval::fromString('00:00', '10:00:30'),
                    TimeInterval::fromString('18:30', '24:00')
                )
            ),
            new Day(
                Day::WEEK_DAY_TUESDAY,
                array(
                    TimeInterval::fromString('01:00', '24:00')
                )
            ),
            new Day(
                Day::WEEK_DAY_WEDNESDAY,
                array(
                    TimeInterval::fromString('00:00', '03:00')
                )
            ),
            new AllDay(Day::WEEK_DAY_FRIDAY)
        );
        $expectedTimezone = new \DateTimeZone('Europe/Bucharest');
        $expected = new BusinessHours($expectedDays, $expectedTimezone);

        $actual = BusinessHoursBuilder::shiftToTimezone($original, new \DateTimeZone('Europe/Berlin'));

        $this->assertEquals($expected, $actual);
    }
}
