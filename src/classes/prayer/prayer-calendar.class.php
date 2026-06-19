<?php

namespace Obadiah\Prayer;

use DateTimeImmutable;
use Obadiah\App;
use Obadiah\AddressBook\Person;
use Obadiah\Cache\Cache;
use Obadiah\Config\Config as C;
use Obadiah\Helpers\Arr;

App::check();

class Prayer_Calendar
{
    /**
     * Tells get_day() to return the name of a person.
     *
     */
    public const RETURN_FULL_NAME = 0;

    /**
     * Tells get_day() to return the full Person object.
     *
     */
    public const RETURN_OBJECT = 1;

    /**
     * Get matching people objects from an array of hashes.
     *
     * @param string[] $hashes          Array of hashes.
     * @return Person[]                 Array of matching Person objects.
     */
    public static function get_people(?array $hashes = null): array
    {
        // get people from cache and return if there are no hashes to match
        $all_people = Cache::get_people();
        if ($hashes === null) {
            return $all_people;
        }

        // get matching people
        $matching_people = Arr::map($hashes, fn(string $hash) => Arr::get($all_people, $hash));

        // sort and return
        self::sort_people($matching_people);
        return $matching_people;
    }

    /**
     * Get the people on a particular day from the prayer calendar.
     *
     * @param DateTimeImmutable $dt     Date.
     * @return Person[]|string[]        Array of people.
     */
    public static function get_day(DateTimeImmutable $dt, int $return_as = self::RETURN_FULL_NAME): array
    {
        // get month
        $id = $dt->format(C::$formats->prayer_month_id);
        $month = Month::load($id);

        // if we are at the end of the month return the configured additional people
        $day = (int)$dt->format("j");
        if (in_array($day, array(29, 30, 31))) {
            $day = sprintf("day_%s", $day);
            return C::$prayer->$day;
        }

        // get the people hashes for the day
        $hashes = Arr::get($month->days, $dt->format(C::$formats->sortable_date), []);

        // return matching people
        return Arr::map(self::get_people($hashes), function (Person $p) use ($return_as) {
            if ($return_as == self::RETURN_FULL_NAME) {
                return $p->get_full_name(C::$prayer->show_last_name);
            } else {
                return $p;
            }
        });
    }

    /**
     * Sort an array of Person objects by last name and then first name.
     *
     * @param Person[] $people          Array of Person objects.
     * @return void
     */
    public static function sort_people(array &$people): void
    {
        // use custom sort - maintaining array keys
        uasort($people, function (Person $a, Person $b) {
            // if last names match, compare first names
            if ($a->last_name == $b->last_name) {
                return $a->first_name <=> $b->first_name;
            }

            // compare last names
            return $a->last_name <=> $b->last_name;
        });
    }

    /**
     * Get filenames of months that have been created for the prayer calendar.
     *
     * @param $all                      If true, all months will be returned - otherwise just the most recent.
     * @return string[]                 Array of months.
     */
    public static function get_months(bool $all = false): array
    {
        // get saved month files
        $files = glob(sprintf("%s/*.month", C::$dir->prayer));
        if ($files === false) {
            return [];
        }
        sort($files);

        // return each month without the '.month' extension
        $months = str_replace(array(C::$dir->prayer, "/", ".month"), "", $files);
        if ($all) {
            return $months;
        } else {
            return array_slice($months, C::$prayer->show_recent_months * -1);
        }
    }
}
