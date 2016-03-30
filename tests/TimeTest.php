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
            array('+20 hours', (20 + (int)$now->format('H')) % 24, (int)$now->format('i'))
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
}
