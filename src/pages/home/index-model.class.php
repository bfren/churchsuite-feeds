<?php

namespace Feeds\Pages\Home;

use Feeds\App;

App::check();

class Index_Model
{
    /**
     * Create Index model.
     *
     * @param array $this_week          Rota filter values to show this week's services.
     * @param array $upcoming           Rota filter values to show upcoming Sunday services.
     * @param array $refresh            Query values to enable refresh ICS feed.
     */
    public function __construct(
        public readonly array $this_week,
        public readonly array $upcoming,
        public readonly array $refresh
    ) {
    }
}
