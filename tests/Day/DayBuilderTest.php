<?php

declare(strict_types=1);

namespace Speicher210\BusinessHours\Test\Day;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Speicher210\BusinessHours\Day\AllDay;
use Speicher210\BusinessHours\Day\Day;
use Speicher210\BusinessHours\Day\DayBuilder;
use Speicher210\BusinessHours\Day\Time\TimeInterval;

class DayBuilderTest extends TestCase
{
    /**
     * @return mixed[]
     */
    public static function dataProviderTestFromAssociativeArrayThrowsExceptionIfArrayStructureIsNotValid(): array
    {
        return [
            [[]],
            [['openingIntervals' => []]],
            [['dayOfWeek' => 1]],
            [
                [
                    'openingIntervals' => [
                        [],
                    ],
                    'dayOfWeek' => 1,
                ],
            ],
            [
                [
                    'openingIntervals' => [
                        [
                            'start' => ['hours' => 0],
                        ],
                    ],
                    'dayOfWeek' => 1,
                ],
            ],
            [
                [
                    'openingIntervals' => [
                        [
                            'end' => ['hours' => 0],
                        ],
                    ],
                    'dayOfWeek' => 1,
                ],
            ],
        ];
    }

    /**
     * @param mixed[] $data The data to test.
     *
     * @dataProvider dataProviderTestFromAssociativeArrayThrowsExceptionIfArrayStructureIsNotValid
     */
    public function testFromAssociativeArrayThrowsExceptionIfArrayStructureIsNotValid(array $data): void
    {
        $this->expectExceptionMessage('Array is not valid.');
        $this->expectException(InvalidArgumentException::class);

        DayBuilder::fromAssociativeArray($data);
    }

    public function testFromAssociativeArrayReturnsAllDay(): void
    {
        $data = [
            'openingIntervals' => [
                [
                    'start' => ['hours' => 0],
                    'end' => ['hours' => 24],
                ],
            ],
            'dayOfWeek' => 1,
        ];

        $actual = DayBuilder::fromAssociativeArray($data);

        $expected = new AllDay(Day::WEEK_DAY_MONDAY);

        self::assertEquals($expected, $actual);
    }

    public function testFromAssociativeArrayReturnsDay(): void
    {
        $data = [
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
            'dayOfWeek' => 1,
        ];

        $actual = DayBuilder::fromAssociativeArray($data);

        $openingHoursIntervals = [
            TimeInterval::fromString('10:10', '18:00:30'),
            TimeInterval::fromString('18:30', '19:00'),
        ];
        $expected              = new Day(Day::WEEK_DAY_MONDAY, $openingHoursIntervals);

        self::assertEquals($expected, $actual);
    }
}
