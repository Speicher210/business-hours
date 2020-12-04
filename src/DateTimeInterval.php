<?php

declare(strict_types=1);

namespace Speicher210\BusinessHours;

use DateTime;
use InvalidArgumentException;
use JsonSerializable;

use function Safe\sprintf;

class DateTimeInterval implements JsonSerializable
{
    private DateTime $start;

    private DateTime $end;

    /**
     * @param DateTime $start The starting date and time.
     * @param DateTime $end   The ending date and time.
     *
     * @throws InvalidArgumentException If the opening date and time is not earlier than closing date and time.
     */
    public function __construct(DateTime $start, DateTime $end)
    {
        $this->start = $start;
        $this->end   = $end;

        if ($end <= $start) {
            throw new InvalidArgumentException(
                sprintf(
                    'The opening date and time "%s" must be before the closing date and time "%s".',
                    $start->format('Y-m-d H:i:s'),
                    $end->format('Y-m-d H:i:s')
                )
            );
        }
    }

    public function getStart(): DateTime
    {
        return $this->start;
    }

    public function getEnd(): DateTime
    {
        return $this->end;
    }

    /**
     * @return array<string,DateTime>
     */
    public function jsonSerialize(): array
    {
        return [
            'start' => $this->start,
            'end' => $this->end,
        ];
    }
}
