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
}
