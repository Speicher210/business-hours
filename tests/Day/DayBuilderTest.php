<?php

declare(strict_types = 1);

namespace Speicher210\BusinessHours\Test\Day;

use PHPUnit\Framework\TestCase;
use Speicher210\BusinessHours\Day\AllDay;
use Speicher210\BusinessHours\Day\Day;
use Speicher210\BusinessHours\Day\DayBuilder;
use Speicher210\BusinessHours\Day\Time\TimeInterval;

class DayBuilderTest extends TestCase
{
    public static function dataProviderTestFromAssociativeArrayThrowsExceptionIfArrayStructureIsNotValid()
    {
        return [
            [[]],
            [['openingIntervals' => []]],
            [['dayOfWeek' => 1]],
            [[
                'openingIntervals' => [
                    []
                ],
                'dayOfWeek' => 1
            ]],
            [[
                'openingIntervals' => [
                    [
                        'start' => ['hours' => 0],
                    ],
                ],
                'dayOfWeek' => 1
            ]],
            [[
                'openingIntervals' => [
                    [
                        'end' => ['hours' => 0],
                    ],
                ],
                'dayOfWeek' => 1
            ]],
        ];
    }

    /**
     * @dataProvider dataProviderTestFromAssociativeArrayThrowsExceptionIfArrayStructureIsNotValid
     *
     * @param array $data The data to test.
     */
    public function testFromAssociativeArrayThrowsExceptionIfArrayStructureIsNotValid(array $data)
    {
        $this->expectExceptionMessage("Array is not valid.");
        $this->expectException(\InvalidArgumentException::class);

        DayBuilder::fromAssociativeArray($data);
    }

    public function testFromAssociativeArrayReturnsAllDay()
    {
        $data = [
            'openingIntervals' => [
                [
                    'start' => ['hours' => 0],
                    'end' => ['hours' => 24]
                ],
            ],
            'dayOfWeek' => 1
        ];

        $actual = DayBuilder::fromAssociativeArray($data);

        $expected = new AllDay(Day::WEEK_DAY_MONDAY);

        $this->assertEquals($expected, $actual);
    }

    public function testFromAssociativeArrayReturnsDay()
    {
        $data = [
            'openingIntervals' => [
                [
                    'start' => ['hours' => 10, 'minutes' => 10],
                    'end' => ['hours' => 18, 'minutes' => 0, 'seconds' => 30]
                ],
                [
                    'start' => ['hours' => 18, 'minutes' => 30],
                    'end' => ['hours' => 19]
                ],
            ],
            'dayOfWeek' => 1
        ];

        $actual = DayBuilder::fromAssociativeArray($data);

        $openingHoursIntervals = [
            TimeInterval::fromString('10:10', '18:00:30'),
            TimeInterval::fromString('18:30', '19:00')
        ];
        $expected = new Day(Day::WEEK_DAY_MONDAY, $openingHoursIntervals);

        $this->assertEquals($expected, $actual);
    }
}
