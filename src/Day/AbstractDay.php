<?php

/**
 * This file is part of Business-hours.
 * Copyright (c) 2015 - 2016 original code: Florian Voutzinos <florian@voutzinos.com
 * Copyright (c) 2015 - 2017 additions and changes: Speicher 210 GmbH
 * For the full copyright and license information, please view the LICENSE * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Speicher210\BusinessHours\Day;

use InvalidArgumentException;
use OutOfBoundsException;
use Psl\Iter;
use Psl\Str;
use Psl\Type;
use Psl\Vec;
use Speicher210\BusinessHours\Day\Time\Time;
use Speicher210\BusinessHours\Day\Time\TimeInterval;
use Speicher210\BusinessHours\Day\Time\TimeIntervalInterface;

use function array_reverse;
use function assert;
use function count;
use function end;
use function max;

abstract class AbstractDay implements DayInterface
{
    private const DAYS_OF_WEEK = [
        DayInterface::WEEK_DAY_MONDAY => 'Monday',
        DayInterface::WEEK_DAY_TUESDAY => 'Tuesday',
        DayInterface::WEEK_DAY_WEDNESDAY => 'Wednesday',
        DayInterface::WEEK_DAY_THURSDAY => 'Thursday',
        DayInterface::WEEK_DAY_FRIDAY => 'Friday',
        DayInterface::WEEK_DAY_SATURDAY => 'Saturday',
        DayInterface::WEEK_DAY_SUNDAY => 'Sunday',
    ];

    protected int $dayOfWeek;

    /** @var TimeIntervalInterface[] */
    protected array $openingHoursIntervals;

    /**
     * @param TimeIntervalInterface[] $openingHoursIntervals The opening hours intervals.
     */
    public function __construct(int $dayOfWeek, array $openingHoursIntervals)
    {
        $this->setDayOfWeek($dayOfWeek);
        $this->setOpeningHoursIntervals($openingHoursIntervals);
    }

    public function getDayOfWeek(): int
    {
        return $this->dayOfWeek;
    }

    public function getDayOfWeekName(): string
    {
        return self::DAYS_OF_WEEK[$this->dayOfWeek];
    }

    /**
     * {@inheritdoc}
     */
    public function getOpeningHoursIntervals(): array
    {
        return $this->openingHoursIntervals;
    }

    public function getClosestPreviousOpeningHoursInterval(Time $time): TimeIntervalInterface|null
    {
        foreach ($this->openingHoursIntervals as $openingHoursInterval) {
            if ($openingHoursInterval->contains($time)) {
                return $openingHoursInterval;
            }
        }

        return $this->getPreviousOpeningHoursInterval($time);
    }

    public function getClosestNextOpeningHoursInterval(Time $time): TimeIntervalInterface|null
    {
        foreach ($this->openingHoursIntervals as $openingHoursInterval) {
            if ($openingHoursInterval->contains($time)) {
                return $openingHoursInterval;
            }
        }

        return $this->getNextOpeningHoursInterval($time);
    }

    public function getPreviousOpeningHoursInterval(Time $time): TimeIntervalInterface|null
    {
        $closestTime     = null;
        $closestInterval = null;

        foreach (array_reverse($this->openingHoursIntervals) as $interval) {
            $distance = $time->toSeconds() - $interval->getEnd()->toSeconds();

            if ($distance < 0) {
                continue;
            }

            if ($closestTime === null) {
                $closestTime     = $interval->getEnd();
                $closestInterval = $interval;
            }

            if ($distance >= $time->toSeconds() - $closestTime->toSeconds()) {
                continue;
            }

            $closestTime     = $interval->getEnd();
            $closestInterval = $interval;
        }

        return $closestInterval;
    }

    public function getNextOpeningHoursInterval(Time $time): TimeIntervalInterface|null
    {
        $closestTime     = null;
        $closestInterval = null;

        foreach ($this->openingHoursIntervals as $interval) {
            $distance = $interval->getStart()->toSeconds() - $time->toSeconds();

            if ($distance < 0) {
                continue;
            }

            if ($closestTime === null) {
                $closestTime     = $interval->getStart();
                $closestInterval = $interval;
            }

            if ($distance >= $closestTime->toSeconds() - $time->toSeconds()) {
                continue;
            }

            $closestTime     = $interval->getStart();
            $closestInterval = $interval;
        }

        return $closestInterval;
    }

    public function getOpeningTime(): Time
    {
        return $this->openingHoursIntervals[0]->getStart();
    }

    public function getClosingTime(): Time
    {
        $interval = end($this->openingHoursIntervals);
        assert($interval instanceof TimeIntervalInterface);

        return $interval->getEnd();
    }

    public function isWithinOpeningHours(Time $time): bool
    {
        foreach ($this->openingHoursIntervals as $interval) {
            if ($interval->contains($time)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @throws OutOfBoundsException If the given day is invalid.
     */
    protected function setDayOfWeek(int $dayOfWeek): void
    {
        if (! isset(self::DAYS_OF_WEEK[$dayOfWeek])) {
            throw new OutOfBoundsException(Str\format('Invalid day of week "%s".', $dayOfWeek));
        }

        $this->dayOfWeek = $dayOfWeek;
    }

    /**
     * @param TimeIntervalInterface[] $openingHoursIntervals The opening hours intervals.
     *
     * @throws InvalidArgumentException If no days are passed or invalid interval is passed.
     */
    protected function setOpeningHoursIntervals(array $openingHoursIntervals): void
    {
        if (count($openingHoursIntervals) === 0) {
            throw new InvalidArgumentException('The day must have at least one opening interval.');
        }

        $intervals = [];

        foreach ($openingHoursIntervals as $interval) {
            if (! $interval instanceof TimeIntervalInterface) {
                throw new InvalidArgumentException(Str\format('Interval must be a %s', TimeIntervalInterface::class));
            }

            $intervals[] = $interval;
        }

        $this->openingHoursIntervals = $this->flattenOpeningHoursIntervals($intervals);
    }

    /**
     * @param TimeIntervalInterface[] $openingHoursIntervals
     *
     * @return TimeIntervalInterface[]
     */
    protected function flattenOpeningHoursIntervals(array $openingHoursIntervals): array
    {
        $sortedOpeningHoursIntervals = Vec\sort(
            $openingHoursIntervals,
            static fn (TimeIntervalInterface $a, TimeIntervalInterface $b): int => $a->getStart()->compareTo($b->getStart()),
        );

        $intervals = [];

        $tmpInterval = Type\instance_of(TimeIntervalInterface::class)->coerce(Iter\first($sortedOpeningHoursIntervals));
        foreach ($sortedOpeningHoursIntervals as $interval) {
            if ($interval->getStart()->lessThanOrEqual($tmpInterval->getEnd())) {
                $tmpInterval = new TimeInterval(
                    $tmpInterval->getStart(),
                    max($tmpInterval->getEnd(), $interval->getEnd()),
                );
            } else {
                $intervals[] = $tmpInterval;
                $tmpInterval = $interval;
            }
        }

        $intervals[] = $tmpInterval;

        return $intervals;
    }

    public function __clone()
    {
        $openingHoursIntervals = [];

        foreach ($this->openingHoursIntervals as $openingHoursInterval) {
            $openingHoursIntervals[] = clone $openingHoursInterval;
        }

        $this->openingHoursIntervals = $openingHoursIntervals;
    }
}
