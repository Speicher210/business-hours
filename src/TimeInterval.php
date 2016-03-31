<?php

namespace Speicher210\BusinessHours;

/**
 * Represents a time interval.
 */
class TimeInterval implements \JsonSerializable
{
    /**
     * The start time.
     *
     * @var Time
     */
    private $start;

    /**
     * The end time.
     *
     * @var Time
     */
    private $end;

    /**
     * Creates a time interval.
     *
     * @param Time $start
     * @param Time $end
     * @throws \InvalidArgumentException If the opening time is not earlier than closing time
     */
    public function __construct(Time $start, Time $end)
    {
        $this->start = $start;
        $this->end = $end;

        if ($start->isAfterOrEqual($end)) {
            throw new \InvalidArgumentException(
                sprintf('The opening time "%s" must be before the closing time "%s".', $start, $end)
            );
        }
    }

    /**
     * Creates a new interval from time strings.
     *
     * @param string $startTime The start time
     * @param string $endTime The end time
     * @return TimeInterval
     * @throws \InvalidArgumentException
     */
    public static function fromString($startTime, $endTime)
    {
        return new static(Time::fromString($startTime), Time::fromString($endTime));
    }

    /**
     * Checks if the interval contains the given time.
     *
     * @param Time $time
     *
     * @return bool
     */
    public function contains(Time $time)
    {
        return $this->start->isBeforeOrEqual($time) && $this->end->isAfterOrEqual($time);
    }

    /**
     * Get the start time.
     *
     * @return Time
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * Get the end time.
     *
     * @return Time
     */
    public function getEnd()
    {
        return $this->end;
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return array(
            'start' => $this->start,
            'end' => $this->end,
        );
    }
}
