<?php

namespace Feeds\Config;

use DateInterval;
use Feeds\App;
use Feeds\Helpers\Arr;

App::check();

class Config_Rota
{
    /**
     * The Bible version to use for links on rota pages.
     *
     * @var int
     */
    public readonly string $bible_version;

    /**
     * The number of days of the rota to show on the rota home page.
     *
     * @var int
     */
    public readonly int $default_days;

    /**
     * The default length of time for services (override using $services array).
     *
     * @var DateInterval
     */
    public readonly DateInterval $default_length;

    /**
     * Array of roles on this rota, with override descriptions and abbreviations.
     *
     * @var Config_Rota_Role[]
     */
    public readonly array $roles;

    /**
     * Array of services with custom lengths as DateInterval strings.
     *
     * @var DateInterval[]
     */
    public readonly array $services;

    /**
     * Get values from rota configuration array.
     *
     * @param array $config             Rota configuration array.
     * @return void
     */
    public function __construct(array $config)
    {
        $this->bible_version = Arr::get($config, "bible_version", "NIVUK");
        $this->default_days = Arr::get($config, "default_days", 28);
        $this->default_length = new DateInterval(Arr::get($config, "default_length", "PT60M"));

        $roles = array();
        foreach (Arr::get($config, "roles") as $role) {
            $roles[] = new Config_Rota_Role($role);
        }
        $this->roles = $roles;

        $services = array();
        foreach (Arr::get($config, "services") as $service) {
            $name = Arr::get($service, "name");
            $length = new DateInterval(Arr::get($service, "length"));
            $services[$name] = $length;
        }
        $this->services = $services;
    }
}
