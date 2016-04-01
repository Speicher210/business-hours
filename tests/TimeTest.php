<?php

namespace Speicher210\BusinessHours\Test;

use Speicher210\BusinessHours\Time;

/**
 * Test class for Time.
 */
class TimeTest extends \PHPUnit_Framework_TestCase
{
    public static function dataProviderTestFromStringInvalid()
    {
        return array(
            array('invalid'),
            array('25:00'),
            array(20),
            array(''),
            array(null),
        );
    }

    /**
     * @dataProvider dataProviderTestFromStringInvalid
     *
     * @param mixed $string The string to test.
     */
    public function testFromStringInvalid($string)
    {
        $this->setExpectedException('\InvalidArgumentException', sprintf('Invalid time "%s".', $string));

        Time::fromString($string);
    }

    public static function dataProviderTestFromString()
    {
        $now = new \DateTime('now');

        return array(
            array('2pm', 14, 0),
            array('11:00', 11, 0),
            array('11:00:00', 11, 0),
            array('23:15', 23, 15),
            array('24:00', 0, 0),
            array('+20 hours', (20 + (int)$now->format('H')) % 24, (int)$now->format('i')),
        );
    }

    /**
     * @dataProvider dataProviderTestFromString
     *
     * @param string $string The time string to test.
     * @param integer $expectedHours The expected hours.
     * @param integer $expectedSeconds The expected seconds.
     */
    public function testFromString($string, $expectedHours, $expectedSeconds)
    {
        $time = Time::fromString($string);
        $this->assertEquals($expectedHours, $time->getHours());
        $this->assertEquals($expectedSeconds, $time->getMinutes());
    }

    public static function dataProviderTestFromDate()
    {
        return array(
            array(new \DateTime('2 AM'), 2, 0),
            array(new \DateTime('3:20 PM'), 15, 20),
        );
    }

    /**
     * @dataProvider dataProviderTestFromDate
     *
     * @param \DateTime $date The date and time to test.
     * @param integer $expectedHours The expected hours.
     * @param integer $expectedSeconds The expected seconds.
     */
    public function testFromDate(\DateTime $date, $expectedHours, $expectedSeconds)
    {
        $time = Time::fromDate($date);
        $this->assertEquals($expectedHours, $time->getHours());
        $this->assertEquals($expectedSeconds, $time->getMinutes());
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
            array(200000, 20, 00),
            array(93000, 9, 30),
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
    public function testToInteger($expectedIntegerRepresentation, $hours, $minutes, $seconds = 0)
    {
        $time = new Time($hours, $minutes, $seconds);
        $this->assertEquals($expectedIntegerRepresentation, $time->toInteger());
    }

    public function testJsonSerialize()
    {
        $time = new Time('20', '30', '15');

        $this->assertJsonStringEqualsJsonFile(
            __DIR__.'/Expected/Time/testJsonSerialize.json',
            json_encode($time)
        );
    }
}
