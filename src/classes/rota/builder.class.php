<?php

namespace Feeds\Rota;

use DateTimeImmutable;
use Feeds\App;
use Feeds\Config\Config as C;
use Feeds\Helpers\Arr;
use Feeds\Lectionary\Lectionary;
use Feeds\Rota\Service;

App::check();

class Builder
{
    /**
     * Character used to join ministries in service summary descriptions.
     */
    private const MINISTRY_JOIN = "/";

    /**
     * Array of days of the week, starting with Sunday, numbered to match DateTimeImmutable format 'N'
     *
     * @var array
     */
    public static array $days_of_the_week = array(
        7 => "Sunday",
        1 => "Monday",
        2 => "Tuesday",
        3 => "Wednesday",
        4 => "Thursday",
        5 => "Friday",
        6 => "Saturday",
    );

    /**
     * Build a rota of matching services, combining rota and lectionary information.
     *
     * @param Lectionary $lectionary    Lectionary object.
     * @param Service[] $services       Services matching the current filters.
     * @return Combined_Day[]           Array of objects combining rota and lectionary service info.
     */
    public static function build_combined_rota(Lectionary $lectionary, array $services): array
    {
        // create an empty array to hold the combined rota
        $rota = array();
        $sunday_collect = "";

        foreach ($lectionary->days as $lectionary_day) {
            // update the Sunday collect
            $date = new DateTimeImmutable($lectionary_day->date);
            if ($date->format("N") == "7") {
                $sunday_collect = $lectionary_day->collect;
            }

            // look for any services on this day
            $rota_services = array_filter($services, function (Service $service) use ($lectionary_day) {
                return $service->start->format(C::$formats->sortable_date) == $lectionary_day->date;
            });

            // if there are no services, continue
            if (!$rota_services) {
                continue;
            }

            // add all the services
            $c_services = array();
            foreach ($rota_services as $rota_service) {
                // get lectionary information and create combined service
                $lectionary_service = $lectionary_day->get_service($rota_service->start);
                if ($lectionary_service === null) {
                    continue;
                }

                // create Combined Service object
                $c_services[] = new Combined_Service(
                    start: $rota_service->start,
                    end: $rota_service->start->add($lectionary_service->length),
                    time: $rota_service->start->format(C::$formats->display_time),
                    name: $lectionary_service->name,
                    series_title: $lectionary_service?->series,
                    sermon_num: $lectionary_service?->num,
                    sermon_title: $lectionary_service?->title,
                    main_reading: $lectionary_service?->main_reading,
                    additional_reading: $lectionary_service?->additional_reading,
                    psalms: $lectionary_service?->psalms ?: array(),
                    ministries: $rota_service->ministries
                );
            }

            // add the day to the rota
            $rota[$lectionary_day->date] = new Combined_Day(
                date: DateTimeImmutable::createFromFormat(C::$formats->sortable_date, $lectionary_day->date, C::$events->timezone)->setTime(0, 0),
                name: $lectionary_day->name,
                colour: $lectionary_day->colour,
                collect: $lectionary_day->collect ?: $sunday_collect,
                services: $c_services
            );
        }

        // return built rota
        return $rota;
    }

    /**
     * Get the name of the specified day of the week.
     *
     * @param int $num                  Day number.
     * @return null|string              Day name.
     */
    public static function get_day(int $num): null|string
    {
        return Arr::get(self::$days_of_the_week, $num);
    }

    /**
     * Generate an event summary for a service, including ministry indicators for the specified person.
     *
     * @param Combined_Service $service     Service object.
     * @param null|string $person           Selected person.
     * @return string                       Service name with ministry indicators.
     */
    public static function get_summary(Combined_Service $service, ?string $person = null): string
    {
        // use the name as the basic summary
        $summary = $service->name;

        // if no person is set, return the summary
        if (!$person) {
            return $summary;
        }

        // look for certain ministries
        $ministries = array();
        foreach ($service->ministries as $service_ministry) {
            foreach ($service_ministry->people as $p) {
                if (str_starts_with($p, $person)) {
                    $ministries[] = $service_ministry->abbreviation;
                }
            }
        }

        // filter out blank ministries
        $filtered = array_filter($ministries);

        // if there are no ministries, return the summary
        // if there are ministries, but filtered is empty, that means there are ministries
        // but they don't have abbreviations defined so add an asterisk instead
        if (count($ministries) == 0) {
            return $summary;
        } elseif (count($filtered) == 0) {
            return sprintf("%s (*)", $summary);
        }

        // sort filtered ministries and add to summary
        sort($filtered);
        return sprintf("%s (%s)", $summary, join(self::MINISTRY_JOIN, $filtered));
    }

    /**
     * Generate an event description for a service, including lectionary / teaching info and ministries.
     *
     * @param Combined_Day $day         Lectionary day information.
     * @param Combined_Service $service Service object.
     * @param bool $include_people      If true, people and ministries will be added to the description.
     * @param string $separator         Line separator.
     * @return string                   Event description.
     */
    public static function get_description(
        Combined_Day $day,
        Combined_Service $service,
        bool $include_people = true,
        string $separator = "\\n"
    ): string {
        // create empty array for description lines
        $description = array();

        // add lectionary info
        if ($day->name && $day->colour) {
            $description[] = sprintf("%s (%s)", $day->name, $day->colour);
            $description[] = "";
        }

        // add teaching
        if ($service->series_title || $service->sermon_title || $service->psalms || $service->main_reading) {
            $description[] = "= Teaching =";

            // series title
            if ($service->series_title) {
                $title = $service->series_title;
                if ($service->sermon_num) {
                    $title = sprintf("%s (%d)", $title, $service->sermon_num);
                }
                $description[] = $title;
            }

            // sermon title
            if ($service->sermon_title) {
                $description[] = sprintf("Title: %s", $service->sermon_title);
            }

            // readings
            if (count($service->psalms)) {
                $description[] = sprintf("Psalm%s: %s", count($service->psalms) > 1 ? "s" : "", join("; ", $service->psalms));
            }

            if ($service->main_reading) {
                $description[] = $service->additional_reading
                    ? sprintf("Readings: %s and %s", $service->main_reading, $service->additional_reading)
                    : sprintf("Reading: %s", $service->main_reading);
            }

            $description[] = "";
        }

        // add ministries
        if ($include_people && $service->ministries) {
            $description[] = "= Ministries =";
            foreach ($service->ministries as $name => $service_ministry) {
                $description[] = sprintf("%s: %s", $name, join(", ", $service_ministry->people));
            }
            $description[] = "";
        }

        // add Collect
        if ($day->collect) {
            $description[] = "= Collect =";
            array_push($description, ...explode("\n", $day->collect));
            $description[] = "";
        }

        // return description
        return join($separator, $description);
    }
}
