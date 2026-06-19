<?php

namespace Obadiah\Pages\Prayer;

use Obadiah\App;
use Obadiah\AddressBook\Person;
use Obadiah\Cache\Cache;
use Obadiah\Helpers\Hash;

App::check();

/** @var View $this */
/** @var Person $model */

// get details
$colour = $model->is_child ? "info" : "warning";
$name = strtolower($model->get_full_name());
$hash = Hash::person($model);

// if this person is not register for the prayer calendar, highlight them in red
if (!in_array($hash, array_keys(Cache::get_people()))) {
    $colour = "danger";
}

// output button HTML
$html = "<button type=\"button\" class=\"btn btn-sm btn-%s m-1\" data-name=\"%s\" data-hash=\"%s\">%s</button>";
_h($html, $colour, $name, $hash, $model->get_full_name());
