<?php

namespace Speicher210\BusinessHours\Test\Day\Time;

use Speicher210\BusinessHours\Day\Time\TimeBuilder;

class TimeBuilderTest extends \PHPUnit_Framework_TestCase
{
    public static function dataProviderTestFromStringInvalid()
    {
        return array(
            array('invalid'),
            array('24:00:01'),
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

        TimeBuilder::fromString($string);
    }

    public static function dataProviderTestFromString()
    {
        return array(
            array('2pm', 14, 0, 0),
            array('11:00', 11, 0, 0),
            array('11:00:11', 11, 0, 11),
            array('23:15', 23, 15, 0),
            array('24:00', 24, 0, 0)
        );
    }

    /**
     * @dataProvider dataProviderTestFromString
     *
     * @param string $string The time string to test.
     * @param integer $expectedHours The expected hours.
     * @param integer $expectedMinutes The expected minutes.
     * @param integer $expectedSeconds The expected seconds.
     */
    public function testFromString($string, $expectedHours, $expectedMinutes, $expectedSeconds)
    {
        $time = TimeBuilder::fromString($string);
        $this->assertEquals($expectedHours, $time->getHours());
        $this->assertEquals($expectedMinutes, $time->getMinutes());
        $this->assertEquals($expectedSeconds, $time->getSeconds());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Array is not valid.
     */
    public function testFromArrayThrowsExceptionIfArrayStructureIsNotValid()
    {
        TimeBuilder::fromArray(array(array()));
    }

    public static function dataProviderTestFromDate()
    {
        return array(
            array(new \DateTime('2 AM'), 2, 0, 0),
            array(new \DateTime('3:20:15 PM'), 15, 20, 15),
        );
    }

    /**
     * @dataProvider dataProviderTestFromDate
     *
     * @param \DateTime $date The date and time to test.
     * @param integer $expectedHours The expected hours.
     * @param integer $expectedMinutes The expected minutes.
     * @param integer $expectedSeconds The expected seconds.
     */
    public function testFromDate(\DateTime $date, $expectedHours, $expectedMinutes, $expectedSeconds)
    {
        $time = TimeBuilder::fromDate($date);
        $this->assertEquals($expectedHours, $time->getHours());
        $this->assertEquals($expectedMinutes, $time->getMinutes());
        $this->assertEquals($expectedSeconds, $time->getSeconds());
    }
}
