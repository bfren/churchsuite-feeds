<?php

namespace Obadiah\Cli\Commands;

use Obadiah\App;
use Obadiah\Cli\Command;

App::check();

class Migrate extends Command
{
    /**
     * Create command.
     *
     * @return void
     */
    public function __construct() {}

    /**
     * Migrate to the latest version.
     *
     * @return void
     */
    public function execute(): void
    {

    }
}
