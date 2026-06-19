<?php

namespace Obadiah\Refresh;

use DateTimeImmutable;
use Obadiah\App;
use Obadiah\AddressBook\Person;
use Obadiah\Bible\Day as Readings;
use Obadiah\Cache\Cache;
use Obadiah\Calendar\Event;
use Obadiah\Config\Config as C;
use Obadiah\Helpers\Arr;
use Obadiah\Helpers\Psalms;

App::check();

class Day
{
    /**
     * Create Day object.
     *
     * @param DateTimeImmutable $date       The date.
     * @param Person[]|string[] $people     Array of people.
     * @param Event[] $events               Array of people.
     * @param Readings|null $readings       Bible readings.
     * @return void
     */
    public function __construct(
        public readonly DateTimeImmutable $date,
        public readonly array $people,
        public readonly array $events,
        public readonly ?Readings $readings
    ) {}

    /**
     * Build event summary.
     *
     * @return string                       Event summary.
     */
    public function get_summary(): string
    {
        return sprintf("Refresh %s", $this->date->format(C::$formats->refresh_date));
    }

    /**
     * Build event description.
     *
     * @param string $separator             Line separator.
     * @return string                       Event description.
     */
    public function get_description(string $separator = "\\n"): string
    {
        $description = [];

        // add readings
        if ($this->readings) {
            $description[] = "= Readings =";
            $description[] = sprintf("1: %s %s", Psalms::pluralise($this->readings->ot_psalms), $this->readings->ot_psalms);
            $description[] = sprintf("2: %s", $this->readings->ot_1);
            $description[] = sprintf("3: %s", $this->readings->ot_2);
            $description[] = sprintf("4: %s", $this->readings->nt_gospels);
            $description[] = sprintf("5: %s", $this->readings->nt_epistles);
            $description[] = "";
        }

        // add people
        if (!empty($this->people)) {
            $description[] = "= People =";
            $people = Arr::map($this->people, fn($person) => $person instanceof Person ? $person->get_full_name(C::$prayer->show_last_name) : (string) $person);
            $description[] = join($separator, $people);
            $description[] = "";
        }

        // add events
        if (!empty($this->events)) {
            $description[] = "= Events & Groups =";
            $events = Arr::map($this->events, fn($e) => sprintf("%s - %s", $e->start->format(C::$formats->display_time), $e->title));
            $description[] = join($separator, $events);
            $description[] = "";
        }

        // add Collects
        if (($collect = Cache::get_lectionary()->get_collect($this->date)) !== null) {
            $lines = preg_split("/\n/", $collect);
            if ($lines !== false) {
                $description[] = "= Collect =";
                $description[] = join($separator, $lines);
                $description[] = "";
            }
        }
        if (($additional_collect = Cache::get_lectionary()->get_additional_collect($this->date)) !== null) {
            $lines = preg_split("/\n/", $additional_collect);
            if ($lines !== false) {
                $description[] = "= Additional Collect =";
                $description[] = join($separator, $lines);
                $description[] = "";
            }
        }

        // return description
        return join($separator, $description);
    }
}
