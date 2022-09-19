<?php

namespace Feeds\Config;

use Feeds\App;

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
     * Array of roles on this rota, with override descriptions and abbreviations.
     *
     * @var Config_Rota_Role[]
     */
    public readonly array $roles;

    /**
     * Get values from rota configuration array.
     *
     * @param array $config             Rota configuration array.
     * @return void
     */
    public function __construct(array $config)
    {
        $this->bible_version = $config["bible_version"];
        $this->default_days = $config["default_days"];
        $roles = array();
        foreach ($config["roles"] as $role) {
            $roles[] = new Config_Rota_Role($role);
        }
        $this->roles = $roles;
    }
}
