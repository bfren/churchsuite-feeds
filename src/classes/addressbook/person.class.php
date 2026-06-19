<?php

namespace Obadiah\AddressBook;

use Obadiah\App;

App::check();

class Person
{
    /**
     * Create Person object.
     *
     * @param int $id                   ChurchSuite ID.
     * @param string $first_name        First name.
     * @param string $last_name         Last name.
     * @param bool $is_child            Whether or not this person is a child.
     * @param ?string $image_url        Optional URL to the person's photo (if they have uploaded one).
     * @return void
     */
    public function __construct(
        public readonly int $id,
        public readonly string $first_name,
        public readonly string $last_name,
        public readonly bool $is_child,
        public readonly ?string $image_url
    ) {}

    /**
     * Return the person's full name.
     *
     * @param bool $show_last_name      If false the last name will be converted to be an initial.
     * @return string                   Full name.
     */
    public function get_full_name(bool $show_last_name = true): string
    {
        return sprintf("%s %s", $this->first_name, $show_last_name ? $this->last_name : substr($this->last_name, 0, 1));
    }
}
