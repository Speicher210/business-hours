<?php

declare(strict_types=1);

namespace Speicher210\BusinessHours\Test;

use DateTimeZone;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Speicher210\BusinessHours\BusinessHours;
use Speicher210\BusinessHours\BusinessHoursBuilder;
use Speicher210\BusinessHours\Day\AllDay;
use Speicher210\BusinessHours\Day\Day;
use Speicher210\BusinessHours\Day\Time\TimeInterval;

class BusinessHoursBuilderTest extends TestCase
{
    /**
     * @return mixed[]
     */
    public static function dataProviderTestFromAssociativeArrayThrowsExceptionIfArrayStructureIsNotValid() : array
    {
        return [
            [[]],
            [['days' => []]],
            [['timezone' => 1]],
        ];
    }

    /**
     * @param mixed[] $data
     *
     * @dataProvider dataProviderTestFromAssociativeArrayThrowsExceptionIfArrayStructureIsNotValid
     */
    public function testFromAssociativeArrayThrowsExceptionIfArrayStructureIsNotValid(array $data) : void
    {
        $this->expectExceptionMessage('Array is not valid.');
        $this->expectException(InvalidArgumentException::class);

        BusinessHoursBuilder::fromAssociativeArray($data);
    }

    public function testFromArrayReturnsDay() : void
    {
        $data = [
            'days' => [
                [
                    'dayOfWeek' => Day::WEEK_DAY_MONDAY,
                    'openingIntervals' => [
                        [
                            'start' => ['hours' => 10, 'minutes' => 10],
                            'end' => ['hours' => 18, 'minutes' => 0, 'seconds' => 30],
                        ],
                        [
                            'start' => ['hours' => 18, 'minutes' => 30],
                            'end' => ['hours' => 19],
                        ],
                    ],
                ],
                [
                    'dayOfWeek' => Day::WEEK_DAY_FRIDAY,
                    'openingIntervals' => [
                        [
                            'start' => ['hours' => 0],
                            'end' => ['hours' => 24],
                        ],
                    ],
                ],
            ],
            'timezone' => 'Europe/Berlin',
        ];

        $actual = BusinessHoursBuilder::fromAssociativeArray($data);

        $days     = [
            new Day(
                Day::WEEK_DAY_MONDAY,
                [
                    TimeInterval::fromString('10:10', '18:00:30'),
                    TimeInterval::fromString('18:30', '19:00'),
                ]
            ),
            new AllDay(Day::WEEK_DAY_FRIDAY),
        ];
        $timezone = new DateTimeZone('Europe/Berlin');
        $expected = new BusinessHours($days, $timezone);

        self::assertEquals($expected, $actual);
    }

    public function testShiftToTimezoneWhenNewTimezoneIsTheSame() : void
    {
        $days     = [
            new Day(
                Day::WEEK_DAY_MONDAY,
                [
                    TimeInterval::fromString('10:10', '18:00:30'),
                    TimeInterval::fromString('18:30', '19:00'),
                ]
            ),
            new AllDay(Day::WEEK_DAY_FRIDAY),
        ];
        $timezone = new DateTimeZone('Europe/Bucharest');
        $original = new BusinessHours($days, $timezone);

        $actual = BusinessHoursBuilder::shiftToTimezone($original, new DateTimeZone('Europe/Bucharest'));

        self::assertEquals($original, $actual);
        self::assertNotSame($original, $actual);
    }

    public function testShiftToTimezoneWhenTimezoneShiftIsBackwards() : void
    {
        $originalDays     = [
            new Day(
                Day::WEEK_DAY_MONDAY,
                [
                    TimeInterval::fromString('00:00', '10:00:30'),
                    TimeInterval::fromString('18:30', '24:00'),
                ]
            ),
            new Day(
                Day::WEEK_DAY_TUESDAY,
                [TimeInterval::fromString('01:00', '24:00')]
            ),
            new Day(
                Day::WEEK_DAY_WEDNESDAY,
                [TimeInterval::fromString('00:00', '03:00')]
            ),
            new AllDay(Day::WEEK_DAY_FRIDAY),
            new Day(
                Day::WEEK_DAY_SATURDAY,
                [
                    TimeInterval::fromString('00:30', '00:45'),
                    TimeInterval::fromString('00:50', '00:55'),
                ]
            ),
        ];
        $originalTimezone = new DateTimeZone('Europe/Bucharest');
        $original         = new BusinessHours($originalDays, $originalTimezone);

        $expectedDays     = [
            new Day(
                Day::WEEK_DAY_MONDAY,
                [
                    TimeInterval::fromString('00:00', '09:00:30'),
                    TimeInterval::fromString('17:30', '23:00'),
                ]
            ),
            new AllDay(
                Day::WEEK_DAY_TUESDAY
            ),
            new Day(
                Day::WEEK_DAY_WEDNESDAY,
                [TimeInterval::fromString('00:00', '02:00')]
            ),
            new Day(
                Day::WEEK_DAY_THURSDAY,
                [TimeInterval::fromString('23:00', '24:00')]
            ),
            new Day(
                Day::WEEK_DAY_FRIDAY,
                [
                    TimeInterval::fromString('00:00', '23:00'),
                    TimeInterval::fromString('23:30', '23:45'),
                    TimeInterval::fromString('23:50', '23:55'),
                ]
            ),
            new Day(
                Day::WEEK_DAY_SUNDAY,
                [TimeInterval::fromString('23:00', '24:00')]
            ),
        ];
        $expectedTimezone = new DateTimeZone('Europe/Berlin');
        $expected         = new BusinessHours($expectedDays, $expectedTimezone);

        $actual = BusinessHoursBuilder::shiftToTimezone($original, new DateTimeZone('Europe/Berlin'));

        self::assertEquals($expected, $actual);
    }

    public function testShiftToTimezoneWhenTimezoneShiftIsForward() : void
    {
        $originalDays     = [
            new Day(
                Day::WEEK_DAY_MONDAY,
                [
                    TimeInterval::fromString('00:00', '09:00:30'),
                    TimeInterval::fromString('17:30', '23:00'),
                ]
            ),
            new AllDay(
                Day::WEEK_DAY_TUESDAY
            ),
            new Day(
                Day::WEEK_DAY_WEDNESDAY,
                [TimeInterval::fromString('00:00', '02:00')]
            ),
            new Day(
                Day::WEEK_DAY_THURSDAY,
                [TimeInterval::fromString('23:00', '24:00')]
            ),
            new Day(
                Day::WEEK_DAY_FRIDAY,
                [
                    TimeInterval::fromString('00:00', '23:00'),
                    TimeInterval::fromString('23:30', '23:45'),
                    TimeInterval::fromString('23:50', '23:55'),
                ]
            ),
            new Day(
                Day::WEEK_DAY_SUNDAY,
                [TimeInterval::fromString('23:00', '24:00')]
            ),
        ];
        $originalTimezone = new DateTimeZone('Europe/Berlin');
        $original         = new BusinessHours($originalDays, $originalTimezone);

        $expectedDays     = [
            new Day(
                Day::WEEK_DAY_MONDAY,
                [
                    TimeInterval::fromString('00:00', '10:00:30'),
                    TimeInterval::fromString('18:30', '24:00'),
                ]
            ),
            new Day(
                Day::WEEK_DAY_TUESDAY,
                [TimeInterval::fromString('01:00', '24:00')]
            ),
            new Day(
                Day::WEEK_DAY_WEDNESDAY,
                [TimeInterval::fromString('00:00', '03:00')]
            ),
            new AllDay(Day::WEEK_DAY_FRIDAY),
            new Day(
                Day::WEEK_DAY_SATURDAY,
                [
                    TimeInterval::fromString('00:30', '00:45'),
                    TimeInterval::fromString('00:50', '00:55'),
                ]
            ),
        ];
        $expectedTimezone = new DateTimeZone('Europe/Bucharest');
        $expected         = new BusinessHours($expectedDays, $expectedTimezone);

        $actual = BusinessHoursBuilder::shiftToTimezone($original, new DateTimeZone('Europe/Bucharest'));

        self::assertEquals($expected, $actual);
    }
}
