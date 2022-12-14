<?php

namespace Feeds\Config;

use Feeds\App;
use Feeds\Helpers\Arr;

App::check();

class Config_General
{
    /**
     * Church Suite API key.
     *
     * @var string
     */
    public readonly string $church_suite_api;

    /**
     * Church Suite organisation subdomain (e.g. 'kingshope' for 'kingshope.churchsuite.com').
     *
     * @var string
     */
    public readonly string $church_suite_org;

    /**
     * Whether or not the app is in production mode.
     *
     * @var bool
     */
    public readonly bool $production;

    /**
     * Get values from general configuration array.
     *
     * @param array $config             General configuration array.
     * @return void
     */
    public function __construct(array $config)
    {
        $this->church_suite_api = Arr::get($config, "church_suite_api", "");
        $this->church_suite_org = Arr::get($config, "church_suite_org", "");
        $this->production = Arr::get_boolean($config, "production", true);
    }
}
