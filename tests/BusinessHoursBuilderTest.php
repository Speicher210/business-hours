<?php

declare(strict_types=1);

namespace Speicher210\BusinessHours\Test;

use DateTimeImmutable;
use DateTimeZone;
use Generator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Speicher210\BusinessHours\BusinessHours;
use Speicher210\BusinessHours\BusinessHoursBuilder;
use Speicher210\BusinessHours\Day\AllDay;
use Speicher210\BusinessHours\Day\Day;
use Speicher210\BusinessHours\Day\Time\TimeInterval;

final class BusinessHoursBuilderTest extends TestCase
{
    /**
     * @return array<mixed>
     */
    public static function dataProviderTestFromAssociativeArrayThrowsExceptionIfArrayStructureIsNotValid(): array
    {
        return [
            [[]],
            [['days' => []]],
            [['timezone' => 1]],
        ];
    }

    /**
     * @param array<mixed> $data
     *
     * @dataProvider dataProviderTestFromAssociativeArrayThrowsExceptionIfArrayStructureIsNotValid
     */
    public function testFromAssociativeArrayThrowsExceptionIfArrayStructureIsNotValid(array $data): void
    {
        $this->expectExceptionMessage('Array is not valid.');
        $this->expectException(InvalidArgumentException::class);

        BusinessHoursBuilder::fromAssociativeArray($data);
    }

    public function testFromArrayReturnsDay(): void
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

    /**
     * @return Generator<string,array<DateTimeImmutable>>
     */
    public static function dataProviderTestShiftToTimezoneWhenNewTimezoneIsTheSame(): Generator
    {
        yield 'summer time' => [new DateTimeImmutable('2020-07-05', new DateTimeZone('Europe/Bucharest'))];
        yield 'winter time' => [new DateTimeImmutable('2020-01-05', new DateTimeZone('Europe/Bucharest'))];
    }

    /**
     * @dataProvider dataProviderTestShiftToTimezoneWhenNewTimezoneIsTheSame
     */
    public function testShiftToTimezoneWhenNewTimezoneIsTheSame(DateTimeImmutable $shiftDateTime): void
    {
        $original = new BusinessHours(
            [
                new Day(
                    Day::WEEK_DAY_MONDAY,
                    [
                        TimeInterval::fromString('10:10', '18:00:30'),
                        TimeInterval::fromString('18:30', '19:00'),
                    ]
                ),
                new AllDay(Day::WEEK_DAY_FRIDAY),
            ],
            new DateTimeZone('Europe/Bucharest')
        );

        $actual = BusinessHoursBuilder::shiftToTimezone($original, $shiftDateTime);
        self::assertEquals($original, $actual);
        self::assertNotSame($original, $actual);
    }

    /**
     * @return Generator<string,array<DateTimeZone|DateTimeImmutable|BusinessHours>>
     */
    public static function dataProviderTestShiftToTimezoneWhenTimezoneShiftIsBackwards(): Generator
    {
        yield 'timezones sharing DST summer time' => [
            new DateTimeZone('Europe/Bucharest'),
            new DateTimeImmutable('2020-07-05', new DateTimeZone('Europe/Berlin')),
            new BusinessHours(
                [
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
                ],
                new DateTimeZone('Europe/Berlin')
            ),
        ];

        yield 'timezones sharing DST winter time' => [
            new DateTimeZone('Europe/Bucharest'),
            new DateTimeImmutable('2020-01-05', new DateTimeZone('Europe/Berlin')),
            new BusinessHours(
                [
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
                ],
                new DateTimeZone('Europe/Berlin')
            ),
        ];

        yield 'timezone with summer time to UTC' => [
            new DateTimeZone('Europe/Bucharest'),
            new DateTimeImmutable('2020-07-05', new DateTimeZone('UTC')),
            new BusinessHours(
                [
                    new Day(
                        Day::WEEK_DAY_MONDAY,
                        [
                            TimeInterval::fromString('00:00', '07:00:30'),
                            TimeInterval::fromString('15:30', '21:00'),
                            TimeInterval::fromString('22:00', '24:00'),
                        ]
                    ),
                    new AllDay(
                        Day::WEEK_DAY_TUESDAY
                    ),
                    new Day(
                        Day::WEEK_DAY_THURSDAY,
                        [TimeInterval::fromString('21:00', '24:00')]
                    ),
                    new Day(
                        Day::WEEK_DAY_FRIDAY,
                        [
                            TimeInterval::fromString('00:00', '21:00'),
                            TimeInterval::fromString('21:30', '21:45'),
                            TimeInterval::fromString('21:50', '21:55'),
                        ]
                    ),
                    new Day(
                        Day::WEEK_DAY_SUNDAY,
                        [TimeInterval::fromString('21:00', '24:00')]
                    ),
                ],
                new DateTimeZone('UTC')
            ),
        ];

        yield 'timezone with winter time to UTC' => [
            new DateTimeZone('Europe/Bucharest'),
            new DateTimeImmutable('2020-01-05', new DateTimeZone('UTC')),
            new BusinessHours(
                [
                    new Day(
                        Day::WEEK_DAY_MONDAY,
                        [
                            TimeInterval::fromString('00:00', '08:00:30'),
                            TimeInterval::fromString('16:30', '22:00'),
                            TimeInterval::fromString('23:00', '24:00'),
                        ]
                    ),
                    new AllDay(
                        Day::WEEK_DAY_TUESDAY
                    ),
                    new Day(
                        Day::WEEK_DAY_WEDNESDAY,
                        [TimeInterval::fromString('00:00', '01:00')]
                    ),
                    new Day(
                        Day::WEEK_DAY_THURSDAY,
                        [TimeInterval::fromString('22:00', '24:00')]
                    ),
                    new Day(
                        Day::WEEK_DAY_FRIDAY,
                        [
                            TimeInterval::fromString('00:00', '22:00'),
                            TimeInterval::fromString('22:30', '22:45'),
                            TimeInterval::fromString('22:50', '22:55'),
                        ]
                    ),
                    new Day(
                        Day::WEEK_DAY_SUNDAY,
                        [TimeInterval::fromString('22:00', '24:00')]
                    ),
                ],
                new DateTimeZone('UTC')
            ),
        ];
    }

    /**
     * @dataProvider dataProviderTestShiftToTimezoneWhenTimezoneShiftIsBackwards
     */
    public function testShiftToTimezoneWhenTimezoneShiftIsBackwards(
        DateTimeZone $originalTimezone,
        DateTimeImmutable $shiftDateTime,
        BusinessHours $expected
    ): void {
        $original = new BusinessHours(
            [
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
            ],
            $originalTimezone
        );

        $actual = BusinessHoursBuilder::shiftToTimezone(
            $original,
            $shiftDateTime
        );
        self::assertEquals($expected, $actual);
    }

    /**
     * @return Generator<string,array<DateTimeZone|DateTimeImmutable|BusinessHours>>
     */
    public static function dataProviderTestShiftToTimezoneWhenTimezoneShiftIsForward(): Generator
    {
        yield 'timezones sharing DST summer time' => [
            new DateTimeZone('Europe/Berlin'),
            new DateTimeImmutable('2020-07-05', new DateTimeZone('Europe/Bucharest')),
            new BusinessHours(
                [
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
                ],
                new DateTimeZone('Europe/Bucharest')
            ),
        ];

        yield 'timezones sharing DST winter time' => [
            new DateTimeZone('Europe/Berlin'),
            new DateTimeImmutable('2020-01-05', new DateTimeZone('Europe/Bucharest')),
            new BusinessHours(
                [
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
                ],
                new DateTimeZone('Europe/Bucharest')
            ),
        ];

        yield 'from UTC to timezone with summer time' => [
            new DateTimeZone('UTC'),
            new DateTimeImmutable('2020-07-05', new DateTimeZone('Europe/Berlin')),
            new BusinessHours(
                [
                    new Day(
                        Day::WEEK_DAY_MONDAY,
                        [
                            TimeInterval::fromString('01:00', '11:00:30'),
                            TimeInterval::fromString('19:30', '24:00'),
                        ]
                    ),
                    new Day(
                        Day::WEEK_DAY_TUESDAY,
                        [
                            TimeInterval::fromString('00:00', '01:00'),
                            TimeInterval::fromString('02:00', '24:00'),
                        ]
                    ),
                    new Day(
                        Day::WEEK_DAY_WEDNESDAY,
                        [TimeInterval::fromString('00:00', '04:00')]
                    ),
                    new Day(
                        Day::WEEK_DAY_FRIDAY,
                        [
                            TimeInterval::fromString('01:00', '24:00'),
                        ]
                    ),
                    new Day(
                        Day::WEEK_DAY_SATURDAY,
                        [
                            TimeInterval::fromString('00:00', '01:00'),
                            TimeInterval::fromString('01:30', '01:45'),
                            TimeInterval::fromString('01:50', '01:55'),
                        ]
                    ),
                ],
                new DateTimeZone('Europe/Berlin')
            ),
        ];

        yield 'from UTC to timezone with winter time' => [
            new DateTimeZone('UTC'),
            new DateTimeImmutable('2020-01-05', new DateTimeZone('Europe/Berlin')),
            new BusinessHours(
                [
                    new Day(
                        Day::WEEK_DAY_MONDAY,
                        [
                            TimeInterval::fromString('00:00', '10:00:30'),
                            TimeInterval::fromString('18:30', '24:00'),
                        ]
                    ),
                    new Day(
                        Day::WEEK_DAY_TUESDAY,
                        [
                            TimeInterval::fromString('01:00', '24:00'),
                        ]
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
                ],
                new DateTimeZone('Europe/Berlin')
            ),
        ];
    }

    /**
     * @dataProvider dataProviderTestShiftToTimezoneWhenTimezoneShiftIsForward
     */
    public function testShiftToTimezoneWhenTimezoneShiftIsForward(
        DateTimeZone $originalTimezone,
        DateTimeImmutable $shiftDateTime,
        BusinessHours $expected
    ): void {
        $actual = BusinessHoursBuilder::shiftToTimezone(
            new BusinessHours(
                [
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
                ],
                $originalTimezone
            ),
            $shiftDateTime
        );

        self::assertEquals($expected, $actual);
    }
}
