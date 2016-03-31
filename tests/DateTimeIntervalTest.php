<?php

namespace Speicher210\BusinessHours\Test;

use Speicher210\BusinessHours\DateTimeInterval;

class DateTimeIntervalTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The opening date and time "2016-03-07 13:00:00" must be before the closing date and time "2016-03-07 13:00:00".
     */
    public function testConstructorOpeningEqualClosing()
    {
        new DateTimeInterval(new \DateTime('2016-03-07 13:00:00'), new \DateTime('2016-03-07 13:00:00'));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The opening date and time "2016-03-07 15:00:00" must be before the closing date and time "2016-03-07 13:00:00".
     */
    public function testConstructorOpeningAfterClosing()
    {
        new DateTimeInterval(new \DateTime('2016-03-07 15:00:00'), new \DateTime('2016-03-07 13:00:00'));
    }

    public function testJsonSerialize()
    {
        $interval = new DateTimeInterval(new \DateTime('2016-03-07 11:20:50'), new \DateTime('2016-03-07 13:33:50'));

        $this->assertJsonStringEqualsJsonFile(
            __DIR__.'/Expected/DateTimeInterval/testJsonSerialize.json',
            json_encode($interval)
        );
    }
}
