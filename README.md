# Business Hours

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]
[![Coverage Status][ico-scrutinizer]][link-scrutinizer]
[![Quality Score][ico-code-quality]][link-code-quality]
[![Total Downloads][ico-downloads]][link-downloads]

## Install

Via Composer

``` bash
$ composer require speicher210/business-hours
```

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Usage

```php
<?php

declare(strict_types = 1);

use Speicher210\BusinessHours\BusinessHours;
use Speicher210\BusinessHours\BusinessHoursBuilder;
use Speicher210\BusinessHours\Day\AllDay;
use Speicher210\BusinessHours\Day\Day;
use Speicher210\BusinessHours\Day\DayBuilder;
use Speicher210\BusinessHours\Day\DayInterface;
use Speicher210\BusinessHours\Day\Time\Time;
use Speicher210\BusinessHours\Day\Time\TimeBuilder;
use Speicher210\BusinessHours\Day\Time\TimeInterval;

require_once __DIR__ . '/vendor/autoload.php';

// Define the business hours.
$businessHours = new BusinessHours(
    [
        new Day(
            DayInterface::WEEK_DAY_MONDAY, // Day of the week.
            // Opening hours.
            [
                // Create a time interval
                new TimeInterval(
                    new Time(8, 0, 0),
                    new Time(18, 0, 0)
                )
            ]
        ),
        // Tuesday is opened all day (from 00:00 to 24:00).
        new AllDay(DayInterface::WEEK_DAY_TUESDAY),
        // For Wednesday we use the TimeBuilder
        new Day(
            DayInterface::WEEK_DAY_WEDNESDAY,
            [
                // First part of the day.
                new TimeInterval(
                    TimeBuilder::fromString('10:00'),
                    TimeBuilder::fromString('14:00')
                ),
                // Second part of the day.
                new TimeInterval(
                    TimeBuilder::fromString('15:00'),
                    TimeBuilder::fromString('20:00')
                ),
            ]
        ),
        // Thursday
        DayBuilder::fromArray(
            DayInterface::WEEK_DAY_THURSDAY,
            [
                // Overlapping time intervals will be merged.
                ['08:00', '11:00'],
                ['10:45', '12:15'],
                ['15:45', '22:00'],
                ['20:00', '24:00']
            ]
        )
    ],
    // The timezone for the opening hours.
    new \DateTimeZone('UTC')
);

// Check if you are within the opening hours.
$date = new \DateTime('2016-04-27 14:20', new \DateTimeZone('UTC'));
$businessHours->within($date); // false

// Various example to get the next or previous change (opening or closing).

$date = new \DateTime('2016-04-26 14:20', new \DateTimeZone('UTC'));
$businessHours->getPreviousChangeDateTime($date); // 2016-04-26 00:00:00
$businessHours->getNextChangeDateTime($date); // 2016-04-27 00:00:00

$date = new \DateTime('2016-04-28 10:55', new \DateTimeZone('UTC'));
$businessHours->getPreviousChangeDateTime($date); // 2016-04-28 08:00:00
$businessHours->getNextChangeDateTime($date); // 2016-04-28 12:15:00

$date = new \DateTime('2016-04-27 11:20', new \DateTimeZone('UTC'));
$businessHours->getPreviousChangeDateTime($date); // 2016-04-27 10:00:00
$businessHours->getNextChangeDateTime($date); // 2016-04-27 14:00:00

$date = new \DateTime('2016-04-28 01:00', new \DateTimeZone('UTC'));
$businessHours->getPreviousChangeDateTime($date); // 2016-04-27 20:00:00
$businessHours->getNextChangeDateTime($date); // 2016-04-28 08:00:00


$dateUTC = new \DateTime('2016-04-28 08:01', new \DateTimeZone('UTC'));
$var = $businessHours->within($dateUTC); // true

$businessHoursBerlin = BusinessHoursBuilder::shiftToTimezone($businessHours, new \DateTimeZone('Europe/Berlin'));
$dateBerlin = new \DateTime('2016-04-28 08:00', new \DateTimeZone('Europe/Berlin'));
$businessHoursBerlin->within($dateBerlin); // false
```

## Testing

``` bash
$ vendor/bin/phpunit
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CONDUCT](CONDUCT.md) for details.

## Security

If you discover any security related issues, please email instead of using the issue tracker.

## Credits

- [All Contributors][link-contributors]

Original idea from https://github.com/florianv/business

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/Speicher210/business-hours.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/Speicher210/business-hours/master.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/Speicher210/business-hours.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/Speicher210/business-hours.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/Speicher210/business-hours.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/Speicher210/business-hours
[link-travis]: https://travis-ci.org/Speicher210/business-hours
[link-scrutinizer]: https://scrutinizer-ci.com/g/Speicher210/business-hours/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/Speicher210/business-hours
[link-downloads]: https://packagist.org/packages/Speicher210/business-hours
[link-author]: https://github.com/dragosprotung
[link-contributors]: ../../contributors
