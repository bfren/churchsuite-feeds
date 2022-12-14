<?php

namespace Feeds\Rota\Filters;

use DateTimeImmutable;
use Feeds\App;
use Feeds\Config\Config as C;
use Feeds\Lectionary\Lectionary;
use Feeds\Rota\Service;

App::check();

class Before_Filter implements Filter
{
    /**
     * Returns true if the service is before the specified date.
     *
     * @param Lectionary $lectionary    Lectionary object
     * @param Service $service          Service object.
     * @param string $value             A date formatted YYYY-MM-DD.
     * @return bool                     True if the service is before the specified date.
     */
    public function apply(Lectionary $lectionary, Service $service, string $value): bool
    {
        // if no date value is set, include the service
        if (!$value) {
            return true;
        }

        // convert the date to a timestamp
        $dt = new DateTimeImmutable($value, C::$events->timezone);
        if ($dt) {
            return $service->start->format(C::$formats->sortable_date) <= $dt->format(C::$formats->sortable_date);
        }

        // if we get here $value was an invalid date format so return false
        return false;
    }
}
