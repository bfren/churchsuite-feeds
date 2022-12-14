<?php

namespace Feeds\Pages\Rota;

use Feeds\App;
use Feeds\Config\Config as C;

App::check();

/** @var View $this */
/** @var string $model */

$param = array(
    "search" => $model,
    "version" => C::$rota->bible_version
);

$url = sprintf("https://www.biblegateway.com/passage/?%s", http_build_query($param));

?>

<a href="<?php _e($url) ?>" target="_blank"><?php _e($model) ?></a>
