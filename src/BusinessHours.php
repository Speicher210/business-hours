<?php

/**
 * This file is part of Business-hours.
 * Copyright (c) 2015 - 2016 original code: Florian Voutzinos <florian@voutzinos.com
 * Copyright (c) 2015 - 2017 additions and changes: Speicher 210 GmbH
 * For the full copyright and license information, please view the LICENSE * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Speicher210\BusinessHours;

use DateTime;
use DateTimeZone;
use InvalidArgumentException;
use Speicher210\BusinessHours\Day\DayInterface;
use Speicher210\BusinessHours\Day\Time\TimeBuilder;
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
    /**
     * The days.
     *
     * @var DayInterface[]
     */
    protected array $days;

    /**
     * The time zone.
     */
    protected DateTimeZone $timezone;

    /**
     * @param DayInterface[] $days
     */
    public function __construct(array $days, ?DateTimeZone $timezone = null)
    {
        $this->setDays($days);
        $this->timezone = $timezone ?? new DateTimeZone(date_default_timezone_get());
    }

    public function getTimezone() : DateTimeZone
    {
        return $this->timezone;
    }

    /**
     * Get the days.
     *
     * @return DayInterface[]
     */
    public function getDays() : array
    {
        return array_values($this->days);
    }

    /**
     * Add a set of days.
     *
     * @param DayInterface[] $days The days.
     *
     * @throws InvalidArgumentException If no days are passed.
     */
    protected function setDays(array $days) : void
    {
        if (count($days) === 0) {
            throw new InvalidArgumentException('At least one day must be added.');
        }

        $this->days = [];

        foreach ($days as $day) {
            $this->addDay($day);
        }
    }

    public function within(DateTime $date) : bool
    {
        $tmpDate = clone $date;
        $tmpDate->setTimezone($this->timezone);

        $day = $this->getDay((int) $tmpDate->format('N'));
        if ($day !== null) {
            return $day->isWithinOpeningHours(TimeBuilder::fromDate($tmpDate));
        }

        return false;
    }

    public function getNextChangeDateTime(DateTime $date) : DateTime
    {
        $tmpDate = clone $date;
        $tmpDate->setTimezone($this->timezone);
        $dateInterval = $this->getNextClosestInterval($tmpDate);

        if ($this->within($date)) {
            // phpcs:ignore SlevomatCodingStandard.Operators.DisallowEqualOperators.DisallowedEqualOperator
            return $date == $dateInterval->getStart() ? $dateInterval->getStart() : $dateInterval->getEnd();
        }

        return $dateInterval->getStart();
    }

    public function getPreviousChangeDateTime(DateTime $date) : DateTime
    {
        $tmpDate = clone $date;
        $tmpDate->setTimezone($this->timezone);
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
    public function jsonSerialize() : array
    {
        return [
            'days' => $this->days,
            'timezone' => $this->timezone->getName(),
        ];
    }

    /**
     * Get the closest business hours date interval before the given date.
     *
     * @param DateTime $date The given date.
     */
    private function getClosestDateIntervalBefore(DateTime $date) : DateTimeInterval
    {
        $tmpDate   = clone $date;
        $dayOfWeek = (int) $tmpDate->format('N');
        $time      = TimeBuilder::fromDate($tmpDate);

        $day = $this->getDay($dayOfWeek);
        if ($day !== null) {
            $closestTime = $day->getClosestPreviousOpeningHoursInterval($time);
            if ($closestTime !== null) {
                return $this->buildDateTimeInterval($tmpDate, $closestTime);
            }
        }

        $tmpDate = $this->getDateBefore($tmpDate);

        $closestDay = $this->getClosestDayBefore((int) $tmpDate->format('N'));

        $closingTime = $closestDay->getClosingTime();
        $closestTime = $closestDay->getClosestPreviousOpeningHoursInterval($closingTime);

        return $this->buildDateTimeInterval($tmpDate, $closestTime);
    }

    /**
     * Get the closest business hours date interval after the given date.
     */
    private function getClosestDateIntervalAfter(DateTime $date) : DateTimeInterval
    {
        $tmpDate   = clone $date;
        $dayOfWeek = (int) $tmpDate->format('N');
        $time      = TimeBuilder::fromDate($tmpDate);

        $day = $this->getDay($dayOfWeek);
        if ($day !== null) {
            $closestTime = $day->getClosestNextOpeningHoursInterval($time);
            if ($closestTime !== null) {
                return $this->buildDateTimeInterval($tmpDate, $closestTime);
            }
        }

        $tmpDate = $this->getDateAfter($tmpDate);

        $closestDay = $this->getClosestDayBefore((int) $tmpDate->format('N'));

        $openingTime = $closestDay->getOpeningTime();
        $closestTime = $closestDay->getClosestNextOpeningHoursInterval($openingTime);

        return $this->buildDateTimeInterval($tmpDate, $closestTime);
    }

    /**
     * Build a new date time interval for a date.
     */
    private function buildDateTimeInterval(DateTime $date, TimeIntervalInterface $timeInterval) : DateTimeInterval
    {
        $intervalStart = clone $date;
        $intervalEnd   = clone $date;

        $intervalStart->setTime(
            $timeInterval->getStart()->getHours(),
            $timeInterval->getStart()->getMinutes(),
            $timeInterval->getStart()->getSeconds()
        );
        $intervalEnd->setTime(
            $timeInterval->getEnd()->getHours(),
            $timeInterval->getEnd()->getMinutes(),
            $timeInterval->getEnd()->getSeconds()
        );

        return new DateTimeInterval($intervalStart, $intervalEnd);
    }

    /**
     * Get the business hours date before the given date.
     */
    private function getDateBefore(DateTime $date) : DateTime
    {
        $tmpDate = clone $date;
        $tmpDate->modify('-1 day');

        $dayOfWeek  = (int) $tmpDate->format('N');
        $closestDay = $this->getClosestDayBefore($dayOfWeek);
        if ($closestDay->getDayOfWeek() !== $dayOfWeek) {
            $tmpDate->modify(sprintf('last %s', $closestDay->getDayOfWeekName()));
        }

        return $tmpDate;
    }

    /**
     * Get the business hours date after the given date.
     */
    private function getDateAfter(DateTime $date) : DateTime
    {
        $tmpDate = clone $date;
        $tmpDate->modify('+1 day');

        $dayOfWeek  = (int) $tmpDate->format('N');
        $closestDay = $this->getClosestDayAfter($dayOfWeek);

        if ($closestDay->getDayOfWeek() !== $dayOfWeek) {
            $tmpDate->modify(sprintf('next %s', $closestDay->getDayOfWeekName()));
        }

        return $tmpDate;
    }

    /**
     * Get the closest interval endpoint after the given date.
     */
    private function getPreviousClosestInterval(DateTime $date) : DateTimeInterval
    {
        $tmpDate   = clone $date;
        $dayOfWeek = (int) $tmpDate->format('N');
        $time      = TimeBuilder::fromDate($tmpDate);

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
    private function getNextClosestInterval(DateTime $date) : DateTimeInterval
    {
        $tmpDate   = clone $date;
        $dayOfWeek = (int) $tmpDate->format('N');
        $time      = TimeBuilder::fromDate($tmpDate);

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
    private function getClosestDayBefore(int $dayNumber) : ?DayInterface
    {
        $day = $this->getDay($dayNumber);
        if ($day !== null) {
            return $day;
        }

        return $this->getDayBefore($dayNumber);
    }

    /**
     * Get the closest business hours day after a given day number (including it).
     */
    private function getClosestDayAfter(int $dayNumber) : ?DayInterface
    {
        $day = $this->getDay($dayNumber);
        if ($day !== null) {
            return $day;
        }

        return $this->getDayAfter($dayNumber);
    }

    /**
     * Get the business hours day before the day number.
     */
    private function getDayBefore(int $dayNumber) : ?DayInterface
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
    private function getDayAfter(int $dayNumber) : ?DayInterface
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
    private function getDay(int $dayOfWeek) : ?DayInterface
    {
        return $this->days[$dayOfWeek] ?? null;
    }

    /**
     * Add a day.
     *
     * @param DayInterface $day The day.
     */
    private function addDay(DayInterface $day) : void
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
