<?php

namespace Obadiah\Pages\Refresh;

use Obadiah\App;
use Obadiah\AddressBook\Person;
use Obadiah\Cache\Cache;
use Obadiah\Config\Config as C;
use Obadiah\Helpers\Image;
use Obadiah\Helpers\Psalms;
use Obadiah\Pages\Parts\Header\Header_Model;
use Obadiah\Request\Request;
use Obadiah\Response\View;

App::check();

/** @var View $this */
/** @var Index_Model $model */

// get values for link to this month's calendar
$this_month_text = $model->today->date->format(C::$formats->display_month);
$this_month_query = http_build_query(
    array("month" => $model->today->date->format(C::$formats->prayer_month_id))
);

// output header
$this->header(new Header_Model("Refresh"));

?>

<h2><?php _e($model->today->date->format(C::$formats->display_date)); ?></h2>
<p class="small text-muted">
    Today&rsquo;s entry on the Refresh calendar.<br />
    <a href="/refresh/help">Click here</a> for help and instructions.
</p>

<div class="row">

    <div class="col-12 col-md-4">
        <h3 class="mt-3">Bible Readings</h3>
        <?php if ($model->today->readings) : $readings = $model->today->readings; ?>
            <p class="small text-muted">Click on a passage to view the text on Bible Gateway, using the <?php _e(C::$rota->bible_version) ?> translation. There is more information about the five streams and how to use them <a href="/refresh/help">here</a>.</p>
            <p>Stream 1: <?php $this->part("reading", model: sprintf("%s %s", Psalms::pluralise($readings->ot_psalms), $readings->ot_psalms)); ?></p>
            <p>Stream 2: <?php $this->part("reading", model: $readings->ot_1); ?></p>
            <p>Stream 3: <?php $this->part("reading", model: $readings->ot_2); ?></p>
            <p>Stream 4: <?php $this->part("reading", model: $readings->nt_gospels); ?></p>
            <p>Stream 5: <?php $this->part("reading", model: $readings->nt_epistles); ?></p>
        <?php elseif ($model->today->date->format("N") == 7) : ?>
            <p>Look back over the week&rsquo;s readings and pray through what stood out &ndash; or look through the readings for today&rsquo;s services.</p>
        <?php else : ?>
            <p>There are no Bible readings for today.</p>
        <?php endif; ?>
        <h3 class="mt-3">Events &amp; Groups</h3>
        <?php if ($model->today->events): ?>
            <p class="small text-muted">The following events, groups and ministries are taking place today.</p>
            <?php foreach ($model->today->events as $e): ?>
                <p><?php _h("%s %s", $e->start->format(C::$formats->display_time), $e->title); ?></p>
            <?php endforeach; ?>
        <?php elseif ($model->today->date->format("N") == 7) : ?>
            <p>Ask God to bless today&rsquo;s services.</p>
        <?php else: ?>
            <p>There are no groups or events today.</p>
        <?php endif; ?>
    </div>

    <div class="col-12 col-md-4 mb-2">
        <h3 class="mt-3">People</h3>
        <?php if ($model->today->people) : ?>
            <p class="small text-muted"><?php _h(C::$refresh->footer_page_1_left); ?> <?php _h(C::$refresh->footer_page_1_right); ?></p>
            <?php foreach ($model->today->people as $person) : $name = $person instanceof Person ? $person->get_full_name(C::$prayer->show_last_name) : $person; ?>
                <div class="person d-flex align-items-center">
                    <div class="image">
                        <?php if ($person instanceof Person && $person->image_url): ?>
                            <a href="<?php _e($person->image_url) ?>" target="_blank">
                                <img src="<?php _e($person->image_url) ?>" alt="<?php _e($name); ?>" title="<?php _e($name); ?>" />
                            </a>
                        <?php else: ?>
                            <img src="<?php _e(Image::get_src("person.svg")); ?>" alt="<?php _e($name); ?>" title="<?php _e($name); ?>" />
                        <?php endif; ?>
                    </div>
                    <div class="name ps-2"><?php _e($name); ?></div>
                </div>
            <?php endforeach; ?>
        <?php elseif ($model->today->date->format("N") == 7) : ?>
            <p>Give thanks for those serving in today&rsquo;s services.</p>
        <?php else : ?>
            <p>There are no people on the prayer calendar today.</p>
        <?php endif; ?>
    </div>

    <?php if (($collect = Cache::get_lectionary()->get_collect($model->today->date)) !== null) : ?>
        <div class="col-12 col-md-4">
            <h3 class="mt-3">Collect</h3>
            <p class="small text-muted">These are set prayers used by the Church of England to gather (or &lsquo;collect&rsquo;) people and prayers together. Normally they reflect the church calendar or a particular saint.</p>
            <?php _h(str_replace("\n", "<br />", $collect)); ?>

            <?php if (($additional_collect = Cache::get_lectionary()->get_additional_collect($model->today->date)) !== null) : ?>
                <h3 class="mt-3">Additional Collect</h3>
                <p class="small text-muted">These are similar to the main Collect but with simpler language.</p>
                <?php _h(str_replace("\n", "<br />", $additional_collect)); ?>
            <?php endif; ?>
        </div>
    <?php endif; ?>

</div>

<h2><?php _e($this_month_text); ?></h2>
<p>View a printable version of this month&rsquo;s calendar <a href="/refresh/print/?<?php _e($this_month_query); ?>" target="_blank">here</a>.</p>
<p>Use <a href="webcal://<?php _e(Request::$host); ?>/refresh/ics?api=<?php _e(C::$login->api); ?>">this link</a> to add an auto-updating Refresh calendar to your favourite calendar app.</p>

<?php

$this->footer();
