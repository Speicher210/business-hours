<?php

/**
 * This file is part of Business-hours.
 * Copyright (c) 2015 - 2016 original code: Florian Voutzinos <florian@voutzinos.com
 * Copyright (c) 2015 - 2017 additions and changes: Speicher 210 GmbH
 * For the full copyright and license information, please view the LICENSE * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Speicher210\BusinessHours;

use DateTimeInterface;
use DateTimeZone;
use InvalidArgumentException;
use Speicher210\BusinessHours\Day\DayInterface;
use Speicher210\BusinessHours\Day\Time\Time;
use Speicher210\BusinessHours\Day\Time\TimeIntervalInterface;

use function array_values;
use function count;
use function date_default_timezone_get;
use function Safe\sprintf;

/**
 * Default implementation of BusinessHoursInterface.
 */
class BusinessHours implements BusinessHoursInterface
{
    /** @var DayInterface[] */
    protected array $days;

    protected DateTimeZone $timezone;

    /**
     * @param DayInterface[] $days
     */
    public function __construct(array $days, ?DateTimeZone $timezone = null)
    {
        $this->setDays($days);
        $this->timezone = $timezone ?? new DateTimeZone(date_default_timezone_get());
    }

    public function getTimezone(): DateTimeZone
    {
        return $this->timezone;
    }

    /**
     * @return DayInterface[]
     */
    public function getDays(): array
    {
        return array_values($this->days);
    }

    /**
     * @param DayInterface[] $days The days.
     *
     * @throws InvalidArgumentException If no days are passed.
     */
    protected function setDays(array $days): void
    {
        if (count($days) === 0) {
            throw new InvalidArgumentException('At least one day must be added.');
        }

        $this->days = [];

        foreach ($days as $day) {
            $this->addDay($day);
        }
    }

    public function within(DateTimeInterface $date): bool
    {
        $tmpDate = clone $date;
        $tmpDate = $tmpDate->setTimezone($this->timezone);

        $day = $this->getDay((int) $tmpDate->format('N'));
        if ($day !== null) {
            return $day->isWithinOpeningHours(Time::fromDate($tmpDate));
        }

        return false;
    }

    public function getNextChangeDateTime(DateTimeInterface $date): DateTimeInterface
    {
        $tmpDate      = clone $date;
        $tmpDate      = $tmpDate->setTimezone($this->timezone);
        $dateInterval = $this->getNextClosestInterval($tmpDate);

        if ($this->within($date)) {
            // phpcs:ignore SlevomatCodingStandard.Operators.DisallowEqualOperators.DisallowedEqualOperator
            return $date == $dateInterval->getStart() ? $dateInterval->getStart() : $dateInterval->getEnd();
        }

        return $dateInterval->getStart();
    }

    public function getPreviousChangeDateTime(DateTimeInterface $date): DateTimeInterface
    {
        $tmpDate      = clone $date;
        $tmpDate      = $tmpDate->setTimezone($this->timezone);
        $dateInterval = $this->getPreviousClosestInterval($tmpDate);

        if ($this->within($date)) {
            // phpcs:ignore SlevomatCodingStandard.Operators.DisallowEqualOperators.DisallowedEqualOperator
            return $date == $dateInterval->getEnd() ? $dateInterval->getEnd() : $dateInterval->getStart();
        }

        return $dateInterval->getEnd();
    }

    /**
     * @return array<string,mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'days' => $this->days,
            'timezone' => $this->timezone->getName(),
        ];
    }

    /**
     * Get the closest business hours date interval before the given date.
     *
     * @param DateTimeInterface $date The given date.
     */
    private function getClosestDateIntervalBefore(DateTimeInterface $date): DateTimeInterval
    {
        $tmpDate = clone $date;
        $tmpDate = $this->getDateBefore($tmpDate);

        $closestDay = $this->getClosestDayBefore((int) $tmpDate->format('N'));

        $closingTime = $closestDay->getClosingTime();
        $closestTime = $closestDay->getClosestPreviousOpeningHoursInterval($closingTime);

        return $this->buildDateTimeInterval($tmpDate, $closestTime);
    }

    /**
     * Get the closest business hours date interval after the given date.
     */
    private function getClosestDateIntervalAfter(DateTimeInterface $date): DateTimeInterval
    {
        $tmpDate = clone $date;
        $tmpDate = $this->getDateAfter($tmpDate);

        $closestDay = $this->getClosestDayBefore((int) $tmpDate->format('N'));

        $openingTime = $closestDay->getOpeningTime();
        $closestTime = $closestDay->getClosestNextOpeningHoursInterval($openingTime);

        return $this->buildDateTimeInterval($tmpDate, $closestTime);
    }

    /**
     * Build a new date time interval for a date.
     */
    private function buildDateTimeInterval(DateTimeInterface $date, TimeIntervalInterface $timeInterval): DateTimeInterval
    {
        $intervalStart = clone $date;
        $intervalEnd   = clone $date;

        $intervalStart = $intervalStart->setTime(
            $timeInterval->getStart()->hours(),
            $timeInterval->getStart()->minutes(),
            $timeInterval->getStart()->seconds()
        );
        $intervalEnd   = $intervalEnd->setTime(
            $timeInterval->getEnd()->hours(),
            $timeInterval->getEnd()->minutes(),
            $timeInterval->getEnd()->seconds()
        );

        return new DateTimeInterval($intervalStart, $intervalEnd);
    }

    /**
     * Get the business hours date before the given date.
     */
    private function getDateBefore(DateTimeInterface $date): DateTimeInterface
    {
        $tmpDate = clone $date;
        $tmpDate = $tmpDate->modify('-1 day');

        $dayOfWeek  = (int) $tmpDate->format('N');
        $closestDay = $this->getClosestDayBefore($dayOfWeek);
        if ($closestDay->getDayOfWeek() !== $dayOfWeek) {
            $tmpDate = $tmpDate->modify(sprintf('last %s', $closestDay->getDayOfWeekName()));
        }

        return $tmpDate;
    }

    /**
     * Get the business hours date after the given date.
     */
    private function getDateAfter(DateTimeInterface $date): DateTimeInterface
    {
        $tmpDate = clone $date;
        $tmpDate = $tmpDate->modify('+1 day');

        $dayOfWeek  = (int) $tmpDate->format('N');
        $closestDay = $this->getClosestDayAfter($dayOfWeek);

        if ($closestDay->getDayOfWeek() !== $dayOfWeek) {
            $tmpDate = $tmpDate->modify(sprintf('next %s', $closestDay->getDayOfWeekName()));
        }

        return $tmpDate;
    }

    /**
     * Get the closest interval endpoint after the given date.
     */
    private function getPreviousClosestInterval(DateTimeInterface $date): DateTimeInterval
    {
        $tmpDate   = clone $date;
        $dayOfWeek = (int) $tmpDate->format('N');
        $time      = Time::fromDate($tmpDate);

        $day = $this->getDay($dayOfWeek);
        if ($day !== null) {
            $closestTime = $day->getClosestPreviousOpeningHoursInterval($time);
            if ($closestTime !== null) {
                return $this->buildDateTimeInterval($tmpDate, $closestTime);
            }
        }

        return $this->getClosestDateIntervalBefore($date);
    }

    /**
     * Get the closest interval endpoint after the given date.
     */
    private function getNextClosestInterval(DateTimeInterface $date): DateTimeInterval
    {
        $tmpDate   = clone $date;
        $dayOfWeek = (int) $tmpDate->format('N');
        $time      = Time::fromDate($tmpDate);

        $day = $this->getDay($dayOfWeek);
        if ($day !== null) {
            $closestTime = $day->getClosestNextOpeningHoursInterval($time);
            if ($closestTime !== null) {
                return $this->buildDateTimeInterval($tmpDate, $closestTime);
            }
        }

        return $this->getClosestDateIntervalAfter($date);
    }

    /**
     * Get the closest business hours day before a given day number (including it).
     */
    private function getClosestDayBefore(int $dayNumber): ?DayInterface
    {
        $day = $this->getDay($dayNumber);

        return $day ?? $this->getDayBefore($dayNumber);
    }

    /**
     * Get the closest business hours day after a given day number (including it).
     */
    private function getClosestDayAfter(int $dayNumber): ?DayInterface
    {
        $day = $this->getDay($dayNumber);

        return $day ?? $this->getDayAfter($dayNumber);
    }

    /**
     * Get the business hours day before the day number.
     */
    private function getDayBefore(int $dayNumber): ?DayInterface
    {
        $tmpDayNumber = $dayNumber;

        for ($i = 0; $i < 6; $i++) {
            $tmpDayNumber = $tmpDayNumber === DayInterface::WEEK_DAY_MONDAY ? DayInterface::WEEK_DAY_SUNDAY : --$tmpDayNumber;

            $day = $this->getDay($tmpDayNumber);
            if ($day !== null) {
                return $day;
            }
        }

        return $this->getDay($dayNumber);
    }

    /**
     * Get the business hours day after the day number.
     */
    private function getDayAfter(int $dayNumber): ?DayInterface
    {
        $tmpDayNumber = $dayNumber;

        for ($i = 0; $i < 6; $i++) {
            $tmpDayNumber = $tmpDayNumber === DayInterface::WEEK_DAY_SUNDAY ? DayInterface::WEEK_DAY_MONDAY : ++$tmpDayNumber;

            $day = $this->getDay($tmpDayNumber);
            if ($day !== null) {
                return $day;
            }
        }

        return $this->getDay($dayNumber);
    }

    /**
     * Get the day corresponding to the day of the week.
     *
     * @param int $dayOfWeek The day of the week.
     */
    private function getDay(int $dayOfWeek): ?DayInterface
    {
        return $this->days[$dayOfWeek] ?? null;
    }

    /**
     * @param DayInterface $day The day.
     */
    private function addDay(DayInterface $day): void
    {
        $this->days[$day->getDayOfWeek()] = $day;
    }

    public function __clone()
    {
        $days = [];
        foreach ($this->days as $key => $day) {
            $days[$key] = clone $day;
        }

        $this->days = $days;
    }
}
