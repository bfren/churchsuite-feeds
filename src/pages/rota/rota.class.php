<?php

namespace Feeds\Pages\Rota;

use Feeds\App;
use Feeds\Cache\Cache;
use Feeds\Calendar\Event;
use Feeds\Calendar\VCal;
use Feeds\Config\Config as C;
use Feeds\Helpers\Arr;
use Feeds\Request\Request;
use Feeds\Response\ICalendar;
use Feeds\Response\Json;
use Feeds\Response\View;
use Feeds\Rota\Builder;
use Feeds\Rota\Combined_Day;
use Feeds\Rota\Rota as R;

App::check();

class Rota
{
    /**
     * Get rota filters.
     *
     * @return array
     */
    public static function get_filters(): array
    {
        // create default filters
        $default_filters = array(
            "start" => date(C::$formats->sortable_date)
        );

        // merge with query string
        return array_merge($default_filters, Request::$get->all());
    }

    /**
     * Build combined rota
     *
     * @return Combined_Day[]           Array of objects combining rota and lectionary service info.
     */
    public static function build_combined_rota(): array
    {
        // get lectionary and rota
        $lectionary = Cache::get_lectionary();
        $rota = Cache::get_rota();

        // get and apply filters
        $services = $rota->apply_filters(self::get_filters(), $lectionary);

        // build rota
        return Builder::build_combined_rota($lectionary, $services);
    }

    /**
     * GET: /rota
     *
     * @return View
     */
    public function index_get(): View
    {
        // get lectionary and rota
        $lectionary = Cache::get_lectionary();
        $rota = Cache::get_rota();

        // create and return View
        return new View("rota", model: new Index_Model(
            filters: self::get_filters(),
            ten_thirty: R::upcoming_sundays(ten_thirty_only: true),
            days_of_the_week: Builder::$days_of_the_week,
            people: $rota->people,
            series: $lectionary->series,
            days: self::build_combined_rota()
        ));
    }

    /**
     * GET: /rota/print
     *
     * @param string $name              Variant name (default 'print').
     * @return View
     */
    public function print_get(string $name = "print"): View
    {
        // get filters
        $filters = self::get_filters();

        // create and return View
        return new View("rota", name: $name, model: new Print_Model(
            time: Arr::get($filters, "time"),
            day: Builder::get_day(Arr::get($filters, "day", 0)),
            person: Arr::get($filters, "person"),
            days: self::build_combined_rota()
        ));
    }

    /**
     * GET: /rota/ics
     *
     * @return ICalendar
     */
    public function ics_get():ICalendar
    {
        // get combined rota
        $rota = Rota::build_combined_rota();

        // build events array
        $events = array();
        foreach ($rota as $day) {
            foreach ($day->services as $service) {
                $events[] = new Event(
                    uid: Event::create_uid(Cache::get_rota_last_modified()),
                    start: $service->start,
                    end: $service->end,
                    title: Builder::get_summary($service, Arr::get(self::get_filters(), "person")),
                    location: C::$events->default_location,
                    description: Builder::get_description($day, $service)
                );
            }
        }

        // create calendar
        $vcal = new VCal($events, time());

        // return ICalendar action
        return new ICalendar("services", $vcal);
    }

    /**
     * GET: /rota/json
     *
     * @return Json
     */
    public function json_get(): Json
    {
        // get combined rota
        $rota = Rota::build_combined_rota();

        // build events array
        $events = array();
        foreach ($rota as $day) {
            foreach ($day->services as $service) {
                $events[] = new Event(
                    uid: Event::create_uid(Cache::get_rota_last_modified()),
                    start: $service->start,
                    end: $service->end,
                    title: Builder::get_summary($service, Arr::get(self::get_filters(), "person")),
                    location: C::$events->default_location,
                    description: Builder::get_description($day, $service, "\n")
                );
            }
        }

        // return Json action
        return new Json($events, last_modified: Cache::get_rota()->last_modified_timestamp);
    }

    /**
     * GET: /rota/notices
     *
     * @return View
     */
    public function notices_get(): View
    {
        return $this->print_get("notices");
    }
}
