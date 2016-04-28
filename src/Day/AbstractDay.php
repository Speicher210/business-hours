<?php
/* 

* This file is part of Business-hours. 
* Copyright (c) 2015 - 2016 original code: Florian Voutzinos <florian@voutzinos.com
* Copyright (c) 2015 - 2016 additions and changes: Speicher 210 GmbH
* For the full copyright and license information, please view the LICENSE * file that was distributed with this source code. 

*/

namespace Speicher210\BusinessHours\Day;

use Speicher210\BusinessHours\Day\Time\Time;
use Speicher210\BusinessHours\Day\Time\TimeInterval;
use Speicher210\BusinessHours\Day\Time\TimeIntervalInterface;

/**
 * Abstract day class.
 */
abstract class AbstractDay implements DayInterface
{
    /**
     * The days of the week.
     *
     * @var array
     */
    private $daysOfWeek = array(
        DayInterface::WEEK_DAY_MONDAY => 'Monday',
        DayInterface::WEEK_DAY_TUESDAY => 'Tuesday',
        DayInterface::WEEK_DAY_WEDNESDAY => 'Wednesday',
        DayInterface::WEEK_DAY_THURSDAY => 'Thursday',
        DayInterface::WEEK_DAY_FRIDAY => 'Friday',
        DayInterface::WEEK_DAY_SATURDAY => 'Saturday',
        DayInterface::WEEK_DAY_SUNDAY => 'Sunday',
    );

    /**
     * The day of week.
     *
     * @var integer
     */
    protected $dayOfWeek;

    /**
     * The time intervals.
     *
     * @var TimeIntervalInterface[]
     */
    protected $openingHoursIntervals;

    /**
     * Constructor.
     *
     * @param integer $dayOfWeek The day of week.
     * @param TimeIntervalInterface[] $openingHoursIntervals The opening hours intervals.
     */
    public function __construct($dayOfWeek, array $openingHoursIntervals)
    {
        $this->setDayOfWeek($dayOfWeek);
        $this->setOpeningHoursIntervals($openingHoursIntervals);
    }

    /**
     * {@inheritdoc}
     */
    public function getDayOfWeek()
    {
        return $this->dayOfWeek;
    }

    /**
     * {@inheritdoc}
     */
    public function getDayOfWeekName()
    {
        return $this->daysOfWeek[$this->dayOfWeek];
    }

    /**
     * {@inheritdoc}
     */
    public function getOpeningHoursIntervals()
    {
        return $this->openingHoursIntervals;
    }

    /**
     * {@inheritdoc}
     */
    public function getClosestPreviousOpeningHoursInterval(Time $time)
    {
        foreach ($this->openingHoursIntervals as $openingHoursInterval) {
            if ($openingHoursInterval->contains($time)) {
                return $openingHoursInterval;
            }
        }

        return $this->getPreviousOpeningHoursInterval($time);
    }

    /**
     * {@inheritdoc}
     */
    public function getClosestNextOpeningHoursInterval(Time $time)
    {
        foreach ($this->openingHoursIntervals as $openingHoursInterval) {
            if ($openingHoursInterval->contains($time)) {
                return $openingHoursInterval;
            }
        }

        return $this->getNextOpeningHoursInterval($time);
    }

    /**
     * {@inheritdoc}
     */
    public function getPreviousOpeningHoursInterval(Time $time)
    {
        $closestTime = null;
        $closestInterval = null;

        /** @var TimeIntervalInterface $interval */
        foreach (array_reverse($this->openingHoursIntervals) as $interval) {
            $distance = $time->toSeconds() - $interval->getEnd()->toSeconds();

            if ($distance < 0) {
                continue;
            }

            if (null === $closestTime) {
                $closestTime = $interval->getEnd();
                $closestInterval = $interval;
            }

            if ($distance < ($time->toSeconds() - $closestTime->toSeconds())) {
                $closestTime = $interval->getEnd();
                $closestInterval = $interval;
            }
        }

        return $closestInterval;
    }

    /**
     * {@inheritdoc}
     */
    public function getNextOpeningHoursInterval(Time $time)
    {
        $closestTime = null;
        $closestInterval = null;

        foreach ($this->openingHoursIntervals as $interval) {
            $distance = $interval->getStart()->toSeconds() - $time->toSeconds();

            if ($distance < 0) {
                continue;
            }

            if (null === $closestTime) {
                $closestTime = $interval->getStart();
                $closestInterval = $interval;
            }

            if ($distance < ($closestTime->toSeconds() - $time->toSeconds())) {
                $closestTime = $interval->getStart();
                $closestInterval = $interval;
            }
        }

        return $closestInterval;
    }

    /**
     * {@inheritdoc}
     */
    public function getOpeningTime()
    {
        return $this->openingHoursIntervals[0]->getStart();
    }

    /**
     * {@inheritdoc}
     */
    public function getClosingTime()
    {
        /** @var TimeIntervalInterface $interval */
        $interval = end($this->openingHoursIntervals);

        return $interval->getEnd();
    }

    /**
     * {@inheritdoc}
     */
    public function isWithinOpeningHours(Time $time)
    {
        foreach ($this->openingHoursIntervals as $interval) {
            if ($interval->contains($time)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Set the day of week.
     *
     * @param integer $dayOfWeek
     * @throws \OutOfBoundsException If the given day is invalid.
     */
    protected function setDayOfWeek($dayOfWeek)
    {
        if (!isset($this->daysOfWeek[$dayOfWeek])) {
            throw new \OutOfBoundsException(sprintf('Invalid day of week "%s".', $dayOfWeek));
        }

        $this->dayOfWeek = $dayOfWeek;
    }

    /**
     * Set the opening hours intervals.
     *
     * @param TimeIntervalInterface[] $openingHoursIntervals The opening hours intervals.
     * @throws \InvalidArgumentException If no days are passed or invalid interval is passed.
     */
    protected function setOpeningHoursIntervals(array $openingHoursIntervals)
    {
        if (empty($openingHoursIntervals)) {
            throw new \InvalidArgumentException('The day must have at least one opening interval.');
        }

        $intervals = array();

        foreach ($openingHoursIntervals as $interval) {
            if (!$interval instanceof TimeIntervalInterface) {
                throw new \InvalidArgumentException(sprintf('Interval must be a %s', TimeIntervalInterface::class));
            }

            $intervals[] = $interval;
        }

        $this->openingHoursIntervals = $this->flattenOpeningHoursIntervals($intervals);
    }

    /**
     * Flatten the intervals that overlap.
     *
     * @param TimeIntervalInterface[] $openingHoursIntervals
     * @return TimeIntervalInterface[]
     */
    protected function flattenOpeningHoursIntervals(array $openingHoursIntervals)
    {
        usort(
            $openingHoursIntervals,
            function (TimeIntervalInterface $a, TimeIntervalInterface $b) {
                return ($a->getStart() > $b->getStart()) ? 1 : -1;
            }
        );

        $intervals = array();
        $tmpInterval = reset($openingHoursIntervals);
        foreach ($openingHoursIntervals as $interval) {
            /** @var TimeInterval $tmpInterval */
            if ($interval->getStart() <= $tmpInterval->getEnd()) {
                $tmpInterval = new TimeInterval(
                    $tmpInterval->getStart(),
                    max($tmpInterval->getEnd(), $interval->getEnd())
                );
            } else {
                $intervals[] = $tmpInterval;
                $tmpInterval = $interval;
            }
        }

        $intervals[] = $tmpInterval;

        return $intervals;
    }

    /**
     * Handle cloning.
     */
    public function __clone()
    {
        $openingHoursIntervals = array();

        foreach ($this->openingHoursIntervals as $openingHoursInterval) {
            $openingHoursIntervals[] = clone $openingHoursInterval;
        }

        $this->openingHoursIntervals = $openingHoursIntervals;
    }
}
