<?php

namespace Speicher210\BusinessHours\Test;

use Speicher210\BusinessHours\Time;
use Speicher210\BusinessHours\TimeInterval;

class TimeIntervalTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The opening time "08:00:00" must be before the closing time "08:00:00".
     */
    public function testConstructorOpeningEqualClosing()
    {
        new TimeInterval(new Time('08', '00'), new Time('08', '00'));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The opening time "18:00:00" must be before the closing time "08:00:00".
     */
    public function testConstructorOpeningAfterClosing()
    {
        new TimeInterval(new Time('18', '00'), new Time('08', '00'));
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

        return array(
            array($interval, '08', '00', true),
            array($interval, '18', '30', true),
            array($interval, '09', '00', true),
            array($interval, '07', '59', false),
            array($interval, '18', '31', false),
        );
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
            __DIR__.'/Expected/TimeInterval/testJsonSerialize.json',
            json_encode($interval)
        );
    }
}
