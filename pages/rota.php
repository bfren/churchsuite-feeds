<?php

namespace Feeds\Pages;

use Feeds\Cache\Cache;
use Feeds\Config\Config as C;
use Feeds\Lectionary\Lectionary;
use Feeds\Rota\Builder;
use Feeds\Rota\Rota;

defined("IDX") || die("Nice try.");

// create cache and get rota and lectionary
$cache = new Cache(C::$dir->cache, C::$cache->duration_in_seconds);
$rota = $cache->get_rota(fn() => new Rota());
$lectionary = $cache->get_lectionary(fn() => new Lectionary());

// get and apply filters
$default_filters = array(
    "from" => date(C::$formats->sortable_date)
);
$filters = array_merge($default_filters, $_GET);
$services = $rota->apply_filters($filters);

// build rota
$builder = new Builder();
$combined_days = $builder->build_combined_rota($lectionary, $services);

// get action page
$action_page = match($action) {
    "ics" => "rota-ics.php",
    default => "rota-html.php"
};

// load action page
require_once($action_page);