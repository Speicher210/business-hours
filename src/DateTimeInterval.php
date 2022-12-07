<?php

declare(strict_types=1);

namespace Speicher210\BusinessHours;

use DateTimeInterface;
use InvalidArgumentException;
use JsonSerializable;
use Psl\Str;

class DateTimeInterval implements JsonSerializable
{
    private DateTimeInterface $start;

    private DateTimeInterface $end;

    /**
     * @param DateTimeInterface $start The starting date and time.
     * @param DateTimeInterface $end   The ending date and time.
     *
     * @throws InvalidArgumentException If the opening date and time is not earlier than closing date and time.
     */
    public function __construct(DateTimeInterface $start, DateTimeInterface $end)
    {
        $this->start = $start;
        $this->end   = $end;

        if ($end <= $start) {
            throw new InvalidArgumentException(
                Str\format(
                    'The opening date and time "%s" must be before the closing date and time "%s".',
                    $start->format('Y-m-d H:i:s'),
                    $end->format('Y-m-d H:i:s'),
                ),
            );
        }
    }

    public function getStart(): DateTimeInterface
    {
        return $this->start;
    }

    public function getEnd(): DateTimeInterface
    {
        return $this->end;
    }

    /**
     * @return array<string,DateTimeInterface>
     */
    public function jsonSerialize(): array
    {
        return [
            'start' => $this->start,
            'end' => $this->end,
        ];
    }
}
