<?php

namespace Feeds\Rota;

use DateTimeImmutable;
use Feeds\App;

App::check();

class Combined_Day
{
    /**
     * Create Combined_Day object.
     *
     * @param DateTimeImmutable $date           DateTime object referencing midnight of the specified day.
     * @param null|string $name                 Lectionary name (e.g. '15th after Trinity').
     * @param string $collect                   Collect of the week.
     * @param Combined_Service[] $services      Array of services on this day.
     * @return void
     */
    public function __construct(
        public readonly DateTimeImmutable $date,
        public readonly ?string $name,
        public readonly string $collect,
        public readonly array $services
    ) {
    }
}
