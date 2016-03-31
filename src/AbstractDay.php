<?php

namespace Speicher210\BusinessHours;

/**
 * Base day class.
 *
 */
abstract class AbstractDay implements DayInterface
{
    /**
     * The time intervals.
     *
     * @var TimeInterval[]
     */
    protected $openingHoursIntervals;

    /**
     *  The day of week.
     *
     * @var integer
     */
    protected $dayOfWeek;

    /**
     * Constructor.
     *
     * @param integer $dayOfWeek The day of week.
     * @param array $openingHoursIntervals The opening hours intervals.
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
    public function getClosestOpeningHoursInterval(Time $time)
    {
        foreach ($this->openingHoursIntervals as $openingHoursInterval) {
            if ($openingHoursInterval->contains($time)) {
                return $openingHoursInterval;
            }
        }

        $closestTime = null;
        $closestInterval = null;

        foreach ($this->openingHoursIntervals as $interval) {
            $distance = $interval->getStart()->toInteger() - $time->toInteger();

            if ($distance < 0) {
                continue;
            }

            if (null === $closestTime) {
                $closestTime = $interval->getStart();
                $closestInterval = $interval;
            }

            if ($distance < $closestTime->toInteger() - $time->toInteger()) {
                $closestTime = $interval->getStart();
                $closestInterval = $interval;
            }
        }

        return $closestInterval;
    }

    /**
     * @todo
     * @return TimeInterval
     */
    public function getNextOpeningHoursInterval()
    {

    }

    /**
     * @todo
     */
    public function getNextOpeningTime()
    {
        $this->getNextOpeningHoursInterval()->getStart();
    }

    /**
     * @todo
     */
    public function getNextClosingTime()
    {
        $this->getNextOpeningHoursInterval()->getEnd();
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
        return end($this->openingHoursIntervals)->getEnd();
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
     * @param int $dayOfWeek
     *
     * @throws \InvalidArgumentException If the given day is invalid.
     */
    protected function setDayOfWeek($dayOfWeek)
    {
        if (!in_array($dayOfWeek, Days::toArray())) {
            throw new \InvalidArgumentException(sprintf('Invalid day of week "%s".', $dayOfWeek));
        }

        $this->dayOfWeek = $dayOfWeek;
    }

    /**
     * Set the opening hours intervals.
     *
     * @param array $openingHoursIntervals The opening hours intervals.
     *
     * @throws \InvalidArgumentException If no days are passed or invalid interval is passed.
     */
    protected function setOpeningHoursIntervals(array $openingHoursIntervals)
    {
        if (empty($openingHoursIntervals)) {
            throw new \InvalidArgumentException('The day must have at least one opening interval.');
        }

        $this->openingHoursIntervals = [];

        foreach ($openingHoursIntervals as $interval) {
            if (!is_array($interval) || !isset($interval[0]) || !isset($interval[1])) {
                throw new \InvalidArgumentException(
                    'Each interval must be an array containing opening and closing times.'
                );
            }

            $this->openingHoursIntervals[] = TimeInterval::fromString($interval[0], $interval[1]);
        }

        usort($this->openingHoursIntervals, function (TimeInterval $a, TimeInterval $b) {
            return ($a->getStart() > $b->getStart()) ? 1 : -1;
        });
    }
}
