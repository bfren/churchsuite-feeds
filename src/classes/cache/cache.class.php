<?php

namespace Obadiah\Cache;

use Obadiah\App;
use Obadiah\AddressBook\Person;
use Obadiah\Bible\Bible_Plan;
use Obadiah\Calendar\Event;
use Obadiah\ChurchSuite\Api;
use Obadiah\Helpers\Hash;
use Obadiah\Helpers\IO;
use Obadiah\Helpers\Serialise;
use Obadiah\Lectionary\Lectionary;
use Obadiah\Refresh\Refresh;
use Obadiah\Rota\Rota;
use SplFileInfo;

App::check();

class Cache
{
    /**
     * Bible Plan cache name.
     */
    public const BIBLE_PLAN = "bible";

    /**
     * Church Suite events cache.
     */
    public const EVENTS = "events";

    /**
     * Lectionary cache name.
     */
    public const LECTIONARY = "lectionary";

    /**
     * People cache name.
     */
    public const PEOPLE = "people";

    /**
     * Prayer Calendar cache name.
     */
    public const PRAYER = "prayer";

    /**
     * Refresh cache name.
     */
    public const REFRESH = "refresh";

    /**
     * Rota cache name.
     */
    public const ROTA = "rota";

    /**
     * Absolute path to cache data directory.
     *
     * @var string
     */
    private static string $dir_path;

    /**
     * Duration in seconds before cache entries expire.
     *
     * @var int
     */
    private static int $duration_in_seconds;

    /**
     * Store cache values.
     *
     * @param string $dir_path          Absolute path to cache data directory.
     * @param int $duration_in_seconds  Duration in seconds before cache entries expire.
     * @return void
     */
    public static function init(string $dir_path, int $duration_in_seconds)
    {
        self::$dir_path = $dir_path;
        self::$duration_in_seconds = $duration_in_seconds;
    }

    /**
     * Clear the Bible Plan cache.
     *
     * @return void
     */
    public static function clear_bible_plan(): void
    {
        self::clear(self::BIBLE_PLAN);
    }

    /**
     * Clear the Events cache.
     *
     * @return void
     */
    public static function clear_events(): void
    {
        // get all events cache files
        $files = glob(sprintf("%s/%s-*.cache", self::$dir_path, self::EVENTS));
        if ($files === false) {
            return;
        }

        // delete files
        foreach ($files as $file) {
            unlink($file);
        }
    }

    /**
     * Clear the Lectionary cache.
     *
     * @return void
     */
    public static function clear_lectionary(): void
    {
        self::clear(self::LECTIONARY);
    }

    /**
     * Clear the People cache.
     *
     * @return void
     */
    public static function clear_people(): void
    {
        self::clear(self::PEOPLE);
    }

    /**
     * Clear the Prayer Calendar cache.
     *
     * @return void
     */
    public static function clear_prayer_calendar(): void
    {
        self::clear(self::PRAYER);
    }

    /**
     * Clear the Refresh cache.
     *
     * @return void
     */
    public static function clear_refresh(): void
    {
        self::clear(self::REFRESH);
    }

    /**
     * Clear the Rota cache.
     *
     * @return void
     */
    public static function clear_rota(): void
    {
        self::clear(self::ROTA);
    }

    /**
     * Get Bible Plan from the cache (or generate a fresh copy).
     *
     * @param bool $force               If true, $callable will be used whether or not the cache entry has expired.
     * @return Bible_Plan               Bible Plan object.
     */
    public static function get_bible_plan(bool $force = false): Bible_Plan
    {
        return self::get_or_set(self::BIBLE_PLAN, fn() => new Bible_Plan(), force: $force);
    }

    /**
     * Get the Bible Plan last modified timestamp.
     *
     * @return int                      Bible Plan last modified timestamp.
     */
    public static function get_bible_plan_last_modified(): int
    {
        $path = self::get_cache_file_path(self::BIBLE_PLAN);
        return self::get_last_modified($path);
    }

    /**
     * Get Events from the cache (or generate a fresh copy).
     *
     * @param string $query             URL-encoded query (e.g. using http_build_query()).
     * @param callable $callable        Callable function to generate an array of events if not set / expired.
     * @param bool $force               If true, $callable will be used whether or not the cache entry has expired.
     * @return Event[]                  Event objects.
     */
    public static function get_events(string $query, callable $callable, bool $force = false): array
    {
        return self::get_or_set(self::get_events_id($query), $callable, array($query), $force);
    }

    /**
     * Get the last modified timestamp for the Events with the specified query.
     *
     * @param string $query             URL-encoded query (e.g. using http_build_query()).
     * @return int                      Rota last modified timestamp.
     */
    public static function get_events_last_modified(string $query): int
    {
        $id = self::get_events_id($query);
        $path = self::get_cache_file_path($id);
        return self::get_last_modified($path);
    }

    /**
     * Build Events Cache ID by hashing the query.
     *
     * @param string $query             URL-encoded query (e.g. using http_build_query()).
     * @return string                   Events Cache ID.
     */
    private static function get_events_id(string $query): string
    {
        return sprintf("%s-%s", self::EVENTS, Hash::events_query($query));
    }

    /**
     * Get Lectionary from the cache (or generate a fresh copy).
     *
     * @param bool $force               If true, $callable will be used whether or not the cache entry has expired.
     * @return Lectionary               Lectionary object.
     */
    public static function get_lectionary(bool $force = false): Lectionary
    {
        return self::get_or_set(self::LECTIONARY, fn() => new Lectionary(), force: $force);
    }

    /**
     * Get the Lectionary last modified timestamp.
     *
     * @return int                      Lectionary last modified timestamp.
     */
    public static function get_lectionary_last_modified(): int
    {
        $path = self::get_cache_file_path(self::LECTIONARY);
        return self::get_last_modified($path);
    }

    /**
     * Get People from the cache (or retrieve from ChurchSuite).
     *
     * @param bool $force               If true, $callable will be used whether or not the cache entry has expired.
     * @return Person[]                 Array of People.
     */
    public static function get_people(bool $force = false): array
    {
        return self::get_or_set(self::PEOPLE, fn() => Api::get_prayer_calendar_people(), force: $force);
    }

    /**
     * Get the People last modified timestamp.
     *
     * @return int                      People last modified timestamp.
     */
    public static function get_people_last_modified(): int
    {
        $path = self::get_cache_file_path(self::PEOPLE);
        return self::get_last_modified($path);
    }

    /**
     * Get Refresh calendar from the cache (or generate a fresh copy).
     *
     * @param bool $force               If true, $callable will be used whether or not the cache entry has expired.
     * @return Refresh                  Refresh object.
     */
    public static function get_refresh(bool $force = false): Refresh
    {
        return self::get_or_set(self::REFRESH, fn() => new Refresh(), force: $force);
    }

    /**
     * Get the Refresh last modified timestamp.
     *
     * @return int                      Refresh last modified timestamp.
     */
    public static function get_refresh_last_modified(): int
    {
        $path = self::get_cache_file_path(self::REFRESH);
        return self::get_last_modified($path);
    }

    /**
     * Get Rota from the cache (or generate a fresh copy).
     *
     * @param bool $force               If true, $callable will be used whether or not the cache entry has expired.
     * @return Rota                     Rota object.
     */
    public static function get_rota(bool $force = false): Rota
    {
        return self::get_or_set(self::ROTA, fn() => new Rota(), force: $force);
    }

    /**
     * Get the Rota last modified timestamp.
     *
     * @return int                      Rota last modified timestamp.
     */
    public static function get_rota_last_modified(): int
    {
        $path = self::get_cache_file_path(self::ROTA);
        return self::get_last_modified($path);
    }

    /**
     * Get absolute path to cache file.
     *
     * @param string $id                Cache ID.
     * @return string                   Absolute path to cache file.
     */
    private static function get_cache_file_path(string $id): string
    {
        return sprintf("%s/%s.cache", self::$dir_path, $id);
    }

    /**
     * Clear a cache.
     *
     * @param string $id                Cache file name.
     * @return void
     */
    private static function clear(string $id): void
    {
        // get path to cache file
        $path = self::get_cache_file_path($id);

        // delete the file if it exists
        $file = new SplFileInfo($path);
        $file->isFile() && unlink($file->getRealPath());
    }

    /**
     * Get an item from the cache, or generate it if not set or expired.
     *
     * @param string $id                Cache file name.
     * @param callable $callable        Callable function to get cache value if expired or not set.
     * @param mixed[] $args             Optional args to pass to $callable.
     * @param bool $force               If true, $callable will be used whether or not the cache entry has expired.
     * @return mixed                    Value (cached or generated).
     */
    private static function get_or_set(string $id, callable $callable, array $args = [], bool $force = false): mixed
    {
        // clear cache if $force is set
        if ($force) {
            self::clear($id);
        }

        // get path to cache file
        $path = self::get_cache_file_path($id);
        $get_current_value = fn() => Serialise::parse(IO::file_get_contents($path));

        // if the file exists, and the cache file has not expired, read and unserialise the value
        $last_modified = self::get_last_modified($path);
        if (time() - $last_modified < self::$duration_in_seconds) {
            return $get_current_value();
        }

        // get a fresh value and serialise it to the cache
        $value = call_user_func($callable, ...$args);
        if ($value !== false) {
            file_put_contents($path, Serialise::store($value));
            return $value;
        }

        // failed to get fresh value so return current cache value
        return $get_current_value();
    }

    /**
     * Return the last modified time of the specified file, or zero if the file does not exist.
     *
     * @param string $path              Absolute path to file.
     * @return int                      Last modified timestamp (or zero if $file does not exist).
     */
    public static function get_last_modified(string $path): int
    {
        // if the file does not exist return zero
        $file = new SplFileInfo($path);
        if (!$file->isFile()) {
            return 0;
        }

        // return file modified timestamp or zero on failure
        return $file->getMTime() ?: 0;
    }
}
