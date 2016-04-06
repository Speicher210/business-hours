<?php

namespace Speicher210\BusinessHours\Test\Day;

use Speicher210\BusinessHours\Day\AllDay;
use Speicher210\BusinessHours\Day\Day;
use Speicher210\BusinessHours\Day\DayBuilder;
use Speicher210\BusinessHours\Day\Time\TimeInterval;

class DayBuilderTest extends \PHPUnit_Framework_TestCase
{
    public static function dataProviderTestFromArrayThrowsExceptionIfArrayStructureIsNotValid()
    {
        return array(
            array(array()),
            array(array('openingIntervals' => array())),
            array(array('dayOfWeek' => 1))
        );
    }

    /**
     * @dataProvider dataProviderTestFromArrayThrowsExceptionIfArrayStructureIsNotValid
     *
     * @param array $data The data to test.
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Array is not valid.
     */
    public function testFromArrayThrowsExceptionIfArrayStructureIsNotValid(array $data)
    {
        DayBuilder::fromAssociativeArray($data);
    }

    public function testFromArrayReturnsAllDay()
    {
        $data = array(
            'openingIntervals' => array(
                array(
                    'start' => array('hours' => '0'),
                    'end' => array('hours' => '24')
                ),
            ),
            'dayOfWeek' => 1
        );

        $actual = DayBuilder::fromAssociativeArray($data);

        $expected = new AllDay(Day::WEEK_DAY_MONDAY);

        $this->assertEquals($expected, $actual);
    }

    public function testFromArrayReturnsDay()
    {
        $data = array(
            'openingIntervals' => array(
                array(
                    'start' => array('hours' => 10, 'minutes' => 10),
                    'end' => array('hours' => 18, 'minutes' => 0, 'seconds' => 30)
                ),
                array(
                    'start' => array('hours' => 18, 'minutes' => 30),
                    'end' => array('hours' => 19)
                ),
            ),
            'dayOfWeek' => 1
        );

        $actual = DayBuilder::fromAssociativeArray($data);

        $openingHoursIntervals = array(
            TimeInterval::fromString('10:10', '18:00:30'),
            TimeInterval::fromString('18:30', '19:00')
        );
        $expected = new Day(Day::WEEK_DAY_MONDAY, $openingHoursIntervals);

        $this->assertEquals($expected, $actual);
    }
}
