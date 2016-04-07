<?php

namespace Speicher210\BusinessHours;

use Speicher210\BusinessHours\Day\DayInterface;
use Speicher210\BusinessHours\Day\Time\TimeBuilder;
use Speicher210\BusinessHours\Day\Time\TimeIntervalInterface;

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
    protected $days;

    /**
     * The time zone.
     *
     * @var \DateTimeZone
     */
    protected $timezone;

    /**
     * Constructor.
     *
     * @param DayInterface[] $days
     * @param \DateTimeZone|null $timezone
     */
    public function __construct(array $days, \DateTimeZone $timezone = null)
    {
        $this->setDays($days);
        $this->timezone = $timezone ?: new \DateTimeZone(date_default_timezone_get());
    }

    /**
     * {@inheritdoc}
     */
    public function getTimezone()
    {
        return $this->timezone;
    }

    /**
     * Get the days.
     *
     * @return DayInterface[]
     */
    public function getDays()
    {
        return array_values($this->days);
    }

    /**
     * Add a set of days.
     *
     * @param DayInterface[] $days The days.
     * @throws \InvalidArgumentException If no days are passed.
     */
    protected function setDays(array $days)
    {
        if (empty($days)) {
            throw new \InvalidArgumentException('At least one day must be added.');
        }

        $this->days = [];

        foreach ($days as $day) {
            $this->addDay($day);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function within(\DateTime $date)
    {
        $tmpDate = clone $date;
        $tmpDate->setTimezone($this->timezone);

        if (null !== $day = $this->getDay((int)$tmpDate->format('N'))) {
            return $day->isWithinOpeningHours(TimeBuilder::fromDate($tmpDate));
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getNextChangeDateTime(\DateTime $date)
    {
        $tmpDate = clone $date;
        $tmpDate->setTimezone($this->timezone);
        $dateInterval = $this->getNextClosestInterval($tmpDate);

        if ($this->within($date)) {
            return ($date == $dateInterval->getStart()) ? $dateInterval->getStart() : $dateInterval->getEnd();
        } else {
            return $dateInterval->getStart();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getPreviousChangeDateTime(\DateTime $date)
    {
        $tmpDate = clone $date;
        $tmpDate->setTimezone($this->timezone);
        $dateInterval = $this->getPreviousClosestInterval($tmpDate);

        if ($this->within($date)) {
            return ($date == $dateInterval->getEnd()) ? $dateInterval->getEnd() : $dateInterval->getStart();
        } else {
            return $dateInterval->getEnd();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return array(
            'days' => $this->days,
            'timezone' => $this->timezone->getName(),
        );
    }

    /**
     * Get the closest business hours date interval before the given date.
     *
     * @param \DateTime $date The given date.
     * @return DateTimeInterval
     */
    private function getClosestDateIntervalBefore(\DateTime $date)
    {
        $tmpDate = clone $date;
        $dayOfWeek = (int)$tmpDate->format('N');
        $time = TimeBuilder::fromDate($tmpDate);

        if (null !== $day = $this->getDay($dayOfWeek)) {
            if (null !== $closestTime = $day->getClosestPreviousOpeningHoursInterval($time)) {
                return $this->buildDateTimeInterval($tmpDate, $closestTime);
            }
        }

        $tmpDate = $this->getDateBefore($tmpDate);

        $closestDay = $this->getClosestDayBefore((int)$tmpDate->format('N'));

        $closingTime = $closestDay->getClosingTime();
        $closestTime = $closestDay->getClosestPreviousOpeningHoursInterval($closingTime);

        return $this->buildDateTimeInterval($tmpDate, $closestTime);
    }

    /**
     * Get the closest business hours date interval after the given date.
     *
     * @param \DateTime $date The given date.
     * @return DateTimeInterval
     */
    private function getClosestDateIntervalAfter(\DateTime $date)
    {
        $tmpDate = clone $date;
        $dayOfWeek = (int)$tmpDate->format('N');
        $time = TimeBuilder::fromDate($tmpDate);

        if (null !== $day = $this->getDay($dayOfWeek)) {
            if (null !== $closestTime = $day->getClosestNextOpeningHoursInterval($time)) {
                return $this->buildDateTimeInterval($tmpDate, $closestTime);
            }
        }

        $tmpDate = $this->getDateAfter($tmpDate);

        $closestDay = $this->getClosestDayBefore((int)$tmpDate->format('N'));

        $openingTime = $closestDay->getOpeningTime();
        $closestTime = $closestDay->getClosestNextOpeningHoursInterval($openingTime);

        return $this->buildDateTimeInterval($tmpDate, $closestTime);
    }

    /**
     * Build a new date time interval for a date.
     *
     * @param \DateTime $date The date.
     * @param TimeIntervalInterface $timeInterval
     * @return DateTimeInterval
     */
    private function buildDateTimeInterval(\DateTime $date, TimeIntervalInterface $timeInterval)
    {
        $intervalStart = clone $date;
        $intervalEnd = clone $date;

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
     *
     * @param \DateTime $date
     * @return \DateTime
     */
    private function getDateBefore(\DateTime $date)
    {
        $tmpDate = clone $date;
        $tmpDate->modify('-1 day');

        $dayOfWeek = (int)$tmpDate->format('N');
        $closestDay = $this->getClosestDayBefore($dayOfWeek);
        if ($closestDay->getDayOfWeek() !== $dayOfWeek) {
            $tmpDate->modify(sprintf('last %s', $closestDay->getDayOfWeekName()));
        }
        return $tmpDate;
    }

    /**
     * Get the business hours date after the given date.
     *
     * @param \DateTime $date
     * @return \DateTime
     */
    private function getDateAfter(\DateTime $date)
    {
        $tmpDate = clone $date;
        $tmpDate->modify('+1 day');

        $dayOfWeek = (int)$tmpDate->format('N');
        $closestDay = $this->getClosestDayAfter($dayOfWeek);

        if ($closestDay->getDayOfWeek() !== $dayOfWeek) {
            $tmpDate->modify(sprintf('next %s', $closestDay->getDayOfWeekName()));
        }

        return $tmpDate;
    }

    /**
     * Get the closest interval endpoint after the given date.
     *
     * @param \DateTime $date
     * @return DateTimeInterval
     */
    private function getPreviousClosestInterval(\DateTime $date)
    {
        $tmpDate = clone $date;
        $dayOfWeek = (int)$tmpDate->format('N');
        $time = TimeBuilder::fromDate($tmpDate);

        if (null !== $day = $this->getDay($dayOfWeek)) {
            if (null !== $closestTime = $day->getClosestPreviousOpeningHoursInterval($time)) {
                return $this->buildDateTimeInterval($tmpDate, $closestTime);
            }
        }

        return $this->getClosestDateIntervalBefore($date);
    }

    /**
     * Get the closest interval endpoint after the given date.
     *
     * @param \DateTime $date
     * @return DateTimeInterval
     */
    private function getNextClosestInterval(\DateTime $date)
    {
        $tmpDate = clone $date;
        $dayOfWeek = (int)$tmpDate->format('N');
        $time = TimeBuilder::fromDate($tmpDate);

        if (null !== $day = $this->getDay($dayOfWeek)) {
            if (null !== $closestTime = $day->getClosestNextOpeningHoursInterval($time)) {
                return $this->buildDateTimeInterval($tmpDate, $closestTime);
            }
        }

        return $this->getClosestDateIntervalAfter($date);
    }

    /**
     * Get the closest business hours day before a given day number (including it).
     *
     * @param integer $dayNumber
     * @return DayInterface|null
     */
    private function getClosestDayBefore($dayNumber)
    {
        if (null !== $day = $this->getDay($dayNumber)) {
            return $day;
        }

        return $this->getDayBefore($dayNumber);
    }

    /**
     * Get the closest business hours day after a given day number (including it).
     *
     * @param integer $dayNumber
     * @return DayInterface|null
     */
    private function getClosestDayAfter($dayNumber)
    {
        if (null !== $day = $this->getDay($dayNumber)) {
            return $day;
        }

        return $this->getDayAfter($dayNumber);
    }

    /**
     * Get the business hours day before the day number.
     *
     * @param integer $dayNumber
     * @return DayInterface|null
     */
    private function getDayBefore($dayNumber)
    {
        $tmpDayNumber = $dayNumber;

        for ($i = 0; $i < 6; $i++) {
            $tmpDayNumber = (DayInterface::WEEK_DAY_MONDAY === $tmpDayNumber) ? DayInterface::WEEK_DAY_SUNDAY : --$tmpDayNumber;

            if (null !== $day = $this->getDay($tmpDayNumber)) {
                return $day;
            }
        }

        return $this->getDay($dayNumber);
    }

    /**
     * Get the business hours day after the day number.
     *
     * @param integer $dayNumber
     * @return DayInterface|null
     */
    private function getDayAfter($dayNumber)
    {
        $tmpDayNumber = $dayNumber;

        for ($i = 0; $i < 6; $i++) {
            $tmpDayNumber = (DayInterface::WEEK_DAY_SUNDAY === $tmpDayNumber) ? DayInterface::WEEK_DAY_MONDAY : ++$tmpDayNumber;

            if (null !== $day = $this->getDay($tmpDayNumber)) {
                return $day;
            }
        }

        return $this->getDay($dayNumber);
    }

    /**
     * Get the day corresponding to the day of the week.
     *
     * @param integer $dayOfWeek The day of the week.
     * @return DayInterface|null
     */
    private function getDay($dayOfWeek)
    {
        return isset($this->days[$dayOfWeek]) ? $this->days[$dayOfWeek] : null;
    }

    /**
     * Add a day.
     *
     * @param DayInterface $day The day.
     */
    private function addDay(DayInterface $day)
    {
        $this->days[$day->getDayOfWeek()] = $day;
    }

    /**
     * Clone.
     */
    public function __clone()
    {
        $days = array();
        foreach ($this->days as $key => $day) {
            $days[$key] = clone $day;
        }

        $this->days = $days;
    }
}
