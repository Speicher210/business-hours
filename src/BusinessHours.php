<?php

namespace Speicher210\BusinessHours;

use Speicher210\BusinessHours\Day\DayInterface;
use Speicher210\BusinessHours\Day\Time\TimeBuilder;

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
    public function setDays(array $days)
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
    public function getNextChangeDateTime(\DateTime $date = null)
    {
        if ($date === null) {
            $date = new \DateTime('now', $this->timezone);
        }

        $dateInterval = $this->closestDateInterval($date);

        if ($this->within($date)) {
            return ($date == $dateInterval->getStart()) ? $dateInterval->getStart() : $dateInterval->getEnd();
        } else {
            return $dateInterval->getStart();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function closestDateInterval(\DateTime $date)
    {
        $tmpDate = clone $date;
        $tmpDate->setTimezone($this->timezone);

        return $this->getClosestInterval($tmpDate);
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
     * Get the closest business hours date interval after the given date.
     *
     * @param \DateTime $date
     * @return DateTimeInterval
     */
    private function getClosestDateIntervalAfter(\DateTime $date)
    {
        $tmpDate = clone $date;
        $dayOfWeek = (int)$tmpDate->format('N');
        $time = TimeBuilder::fromDate($tmpDate);

        if (null !== $day = $this->getDay($dayOfWeek)) {
            if (null !== $closestTime = $day->getClosestOpeningHoursInterval($time)) {
                $intervalStart = clone $tmpDate;
                $intervalEnd = clone $tmpDate;

                $intervalStart->setTime(
                    $closestTime->getStart()->getHours(),
                    $closestTime->getStart()->getMinutes(),
                    $closestTime->getStart()->getSeconds()
                );
                $intervalEnd->setTime(
                    $closestTime->getEnd()->getHours(),
                    $closestTime->getEnd()->getMinutes(),
                    $closestTime->getEnd()->getSeconds()
                );

                return new DateTimeInterval($intervalStart, $intervalEnd);
            }
        }

        $tmpDate = $this->getDateAfter($tmpDate);

        $closestDay = $this->getClosestDayBefore((int)$tmpDate->format('N'));

        $openingTime = $closestDay->getOpeningTime();
        $closestTime = $closestDay->getClosestOpeningHoursInterval($openingTime);

        $intervalStart = clone $tmpDate;
        $intervalEnd = clone $tmpDate;

        $intervalStart->setTime(
            $closestTime->getStart()->getHours(),
            $closestTime->getStart()->getMinutes(),
            $closestTime->getStart()->getSeconds()
        );
        $intervalEnd->setTime(
            $closestTime->getEnd()->getHours(),
            $closestTime->getEnd()->getMinutes(),
            $closestTime->getEnd()->getSeconds()
        );

        return new DateTimeInterval($intervalStart, $intervalEnd);
    }

    /**
     * Get the business hours date after the given date (excluding holidays).
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
    private function getClosestInterval(\DateTime $date)
    {
        $tmpDate = clone $date;
        $dayOfWeek = (int)$tmpDate->format('N');
        $time = TimeBuilder::fromDate($tmpDate);

        if (null !== $day = $this->getDay($dayOfWeek)) {
            if (null !== $closestTime = $day->getClosestOpeningHoursInterval($time)) {
                $intervalStart = clone $tmpDate;
                $intervalEnd = clone $tmpDate;

                $intervalStart->setTime(
                    $closestTime->getStart()->getHours(),
                    $closestTime->getStart()->getMinutes(),
                    $closestTime->getStart()->getSeconds()
                );
                $intervalEnd->setTime(
                    $closestTime->getEnd()->getHours(),
                    $closestTime->getEnd()->getMinutes(),
                    $closestTime->getEnd()->getSeconds()
                );

                return new DateTimeInterval($intervalStart, $intervalEnd);
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
     * Get the day corresponding to the day number.
     *
     * @param integer $dayNumber
     * @return DayInterface|null
     */
    private function getDay($dayNumber)
    {
        return isset($this->days[$dayNumber]) ? $this->days[$dayNumber] : null;
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
}
