<?php

declare(strict_types = 1);

namespace Speicher210\BusinessHours\Day\Time;

/**
 * Represents a time interval.
 */
class TimeInterval implements TimeIntervalInterface
{
    /**
     * The start time.
     *
     * @var Time
     */
    protected $start;

    /**
     * The end time.
     *
     * @var Time
     */
    protected $end;

    /**
     * @param Time $start The start time.
     * @param Time $end The end time.
     * @throws \InvalidArgumentException If the opening time is not earlier than closing time.
     */
    public function __construct(Time $start, Time $end)
    {
        $this->start = $start;
        $this->end = $end;

        if ($start->isAfterOrEqual($end)) {
            throw new \InvalidArgumentException(
                \sprintf('The opening time "%s" must be before the closing time "%s".', $start, $end)
            );
        }
    }

    /**
     * Create a new interval from time strings.
     *
     * @param string $startTime The start time
     * @param string $endTime The end time
     * @return TimeInterval
     * @throws \InvalidArgumentException
     */
    public static function fromString($startTime, $endTime): self
    {
        return new static(TimeBuilder::fromString($startTime), TimeBuilder::fromString($endTime));
    }

    /**
     * {@inheritdoc}
     */
    public function contains(Time $time): bool
    {
        return $this->start->isBeforeOrEqual($time) && $this->end->isAfterOrEqual($time);
    }

    /**
     * {@inheritdoc}
     */
    public function getStart(): Time
    {
        return $this->start;
    }

    /**
     * {@inheritdoc}
     */
    public function getEnd(): Time
    {
        return $this->end;
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return [
            'start' => $this->start,
            'end' => $this->end,
        ];
    }

    public function __clone()
    {
        $this->start = clone $this->start;
        $this->end = clone $this->end;
    }
}
