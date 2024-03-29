<?php

namespace Feeds\Pages\Parts\Header;

use Feeds\App;

App::check();

class Header_Model
{
    /**
     * Create Header model.
     *
     * @param string $title             Page title.
     * @param null|string $subtitle     Optional page subtitle.
     * @param null|string $class        Optional class for HTML tag.
     * @param bool $overflow_scroll     Enable overflow scroll for this page.
     */
    public function __construct(
        public readonly string $title,
        public readonly ?string $subtitle = null,
        public readonly ?string $class = null,
        public readonly bool $overflow_scroll = false
    ) {
    }
}
