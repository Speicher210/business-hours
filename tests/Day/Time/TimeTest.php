<?php

namespace Speicher210\BusinessHours\Test\Day\Time;

use Speicher210\BusinessHours\Day\Time\Time;

/**
 * Test class for Time.
 */
class TimeTest extends \PHPUnit_Framework_TestCase
{
    public static function dataProviderTestCreateTimeWithInvalidData()
    {
        return array(
            array(-1, 0, 0),
            array(0, -1, 0),
            array(0, 0, -1),
            array(24, 0, 1),
            array(24, 1, 0),
            array(0, 65, 0),
            array(0, 0, 75),
        );
    }

    /**
     * @dataProvider dataProviderTestCreateTimeWithInvalidData
     *
     * @param integer $hours The hours.
     * @param integer $minutes The minutes.
     * @param integer $seconds The seconds.
     */
    public function testCreateTimeWithInvalidData($hours, $minutes, $seconds)
    {
        $this->setExpectedException('\InvalidArgumentException', 'Invalid time');

        new Time($hours, $minutes, $seconds);
    }

    public static function dataProviderTestIsAfterOrEqual()
    {
        $time = new Time(20, 00);

        return array(
            array($time, 18, 00, true),
            array($time, 22, 15, false),
            array($time, 20, 00, true),
        );
    }

    /**
     * @dataProvider dataProviderTestIsAfterOrEqual
     *
     * @param Time $time The date and time to test.
     * @param integer $hours The hours to test.
     * @param integer $minutes The minutes to test.
     * @param boolean $expected The expected value.
     */
    public function testIsAfterOrEqual(Time $time, $hours, $minutes, $expected)
    {
        $this->assertEquals($time->isAfterOrEqual(new Time($hours, $minutes)), $expected);
    }

    public static function dataProviderTestIsBeforeOrEqual()
    {
        $time = new Time(20, 00);

        return array(
            array($time, 18, 00, false),
            array($time, 22, 15, true),
            array($time, 20, 00, true),
        );
    }

    /**
     * @dataProvider dataProviderTestIsBeforeOrEqual
     *
     * @param Time $time The date and time to test.
     * @param integer $hours The hours to test.
     * @param integer $minutes The minutes to test.
     * @param boolean $expected The expected value.
     */
    public function testIsBeforeOrEqual(Time $time, $hours, $minutes, $expected)
    {
        $this->assertEquals($time->isBeforeOrEqual(new Time($hours, $minutes)), $expected);
    }

    public static function dataProviderTestIsEqual()
    {
        $time = new Time(20, 00);

        return array(
            array($time, 18, 00, false),
            array($time, 22, 15, false),
            array($time, 20, 00, true),
        );
    }

    /**
     * @dataProvider dataProviderTestIsEqual
     *
     * @param Time $time The date and time to test.
     * @param integer $hours The hours to test.
     * @param integer $minutes The minutes to test.
     * @param boolean $expected The expected value.
     */
    public function testIsEqual(Time $time, $hours, $minutes, $expected)
    {
        $this->assertEquals($time->isEqual(new Time($hours, $minutes)), $expected);
    }


    public static function dataProviderTestToInteger()
    {
        return array(
            array(200000, 20, 0, 0),
            array(93000, 9, 30, 0),
            array(123456, 12, 34, 56),
        );
    }

    /**
     * @dataProvider dataProviderTestToInteger
     *
     * @param integer $expectedIntegerRepresentation The expected integer representation of time.
     * @param integer $hours The hours to test.
     * @param integer $minutes The minutes to test.
     * @param integer $seconds The seconds to test.
     */
    public function testToInteger($expectedIntegerRepresentation, $hours, $minutes, $seconds)
    {
        $time = new Time($hours, $minutes, $seconds);
        $this->assertEquals($expectedIntegerRepresentation, $time->toInteger());
    }

    public function testJsonSerialize()
    {
        $time = new Time('20', '30', '15');

        $this->assertJsonStringEqualsJsonFile(
            __DIR__ . '/Expected/Time/testJsonSerialize.json',
            json_encode($time)
        );
    }
}
