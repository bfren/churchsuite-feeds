<?php

namespace Feeds\Pages\Upload;

use Feeds\Admin\Bible_File;
use Feeds\Admin\Prayer_File;
use Feeds\Admin\Rota_File;
use Feeds\App;
use Feeds\Config\Config as C;
use Feeds\Pages\Parts\Header\Header_Model;
use Feeds\Response\View;

App::check();

/** @var View $this */
/** @var Index_Model $model */

// output header
$this->header(new Header_Model("Upload", subtitle:"Use this page to upload and update CSV files generated by Church Suite."));

// output alert
$this->alert($model->result);

?>

<!-- Rota file upload -->
<h2>Rota</h2>
<p>Upload a rota CSV file here. (For instructions click <a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#rota-instructions">here</a>.)</p>
<form class="row row-cols-md-auto g-3 mb-3 align-items-center needs-validation" method="POST" action="/upload" enctype="multipart/form-data" novalidate>
    <div class="col-12 position-relative">
        <label class="visually-hidden" for="name">Rota Name</label>
        <input type="text" class="form-control" id="name" name="name" placeholder="Name e.g. '22-2'" required />
        <div class="invalid-tooltip">Please enter the rota name.</div>
    </div>
    <div class="col-12 position-relative">
        <label class="visually-hidden" for="file-rota">Rota File</label>
        <input class="form-control" type="file" id="file-rota" name="file" required />
        <div class="invalid-tooltip">Please select a rota CSV file generated by Church Suite.</div>
    </div>
    <div class="col-12">
        <button type="submit" class="btn btn-primary" name="submit" value="rota">Upload</button>
    </div>
</form>

<?php if ($model->rota_files) : ?>
    <p>The following files are currently uploaded:</p>
    <ul>
        <?php foreach ($model->rota_files as $file) : ?>
            <li>
                <?php _e($file); ?> (last modified <?php _e(Rota_File::get_last_modified($file)); ?>)
                <a class="badge rounded-pill text-bg-danger fw-bold check-first" href="/upload/delete_rota/?file=<?php _e($file); ?>">delete</a>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<!-- Prayer Calendar file upload -->
<h2>Prayer Calendar</h2>
<p>Upload prayer calendar CSV files here. (For instructions click <a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#prayer-calendar-instructions">here</a>.)</p>
<form class="row row-cols-sm-auto g-3 mb-3 align-items-center needs-validation" method="POST" action="/upload" enctype="multipart/form-data" novalidate>
    <div class="col-12">
        <input type="text" readonly class="form-control-plaintext" value="Adults" />
    </div>
    <div class="col-12 position-relative">
        <label class="visually-hidden" for="file-prayer-adults">Rota File</label>
        <input class="form-control" type="file" id="file-prayer-adults" name="file" required />
        <div class="invalid-tooltip">Please select an adults prayer calendar CSV file generated by Church Suite.</div>
    </div>
    <div class="col-12">
        <button type="submit" class="btn btn-primary" name="submit" value="prayer-adults">Upload</button>
    </div>
</form>
<form class="row row-cols-sm-auto g-3 mb-3 align-items-center needs-validation" method="POST" action="/upload" enctype="multipart/form-data" novalidate>
    <div class="col-12">
        <input type="text" readonly class="form-control-plaintext" value="Children" />
    </div>
    <div class="col-12 position-relative">
        <label class="visually-hidden" for="file-prayer-children">Rota File</label>
        <input class="form-control" type="file" id="file-prayer-children" name="file" required />
        <div class="invalid-tooltip">Please select a children prayer calendar CSV file generated by Church Suite.</div>
    </div>
    <div class="col-12">
        <button type="submit" class="btn btn-primary" name="submit" value="prayer-children">Upload</button>
    </div>
</form>

<?php if ($model->prayer_files) : ?>
    <p>The following files are currently uploaded:</p>
    <ul>
        <?php foreach ($model->prayer_files as $file) : ?>
            <li>
                <?php _e($file); ?> (last modified <?php _e(Prayer_File::get_last_modified($file)); ?>)
                <a class="badge rounded-pill text-bg-danger fw-bold check-first" href="/upload/delete_prayer/?file=<?php _e($file); ?>">delete</a>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<!-- Bible plan upload -->
<h2>Bible Plan</h2>
<p>Upload the Bible Plan text file here.</p>
<form class="row row-cols-md-auto g-3 mb-3 align-items-center needs-validation" method="POST" action="/upload" enctype="multipart/form-data" novalidate>
    <div class="col-12 position-relative">
        <label class="visually-hidden" for="file-bible">Rota File</label>
        <input class="form-control" type="file" id="file-bible" name="file" required />
        <div class="invalid-tooltip">Please select a Bible Plan CSV file.</div>
    </div>
    <div class="col-12">
        <button type="submit" class="btn btn-primary" name="submit" value="bible">Upload</button>
    </div>
</form>

<?php if ($model->bible_files) : ?>
    <p>The following files are currently uploaded:</p>
    <ul>
        <?php foreach ($model->bible_files as $file) : ?>
            <li>
                <?php _e($file); ?> (last modified <?php _e(Bible_File::get_last_modified($file)); ?>)
                <a class="badge rounded-pill text-bg-danger fw-bold check-first" href="/upload/delete_bible">delete</a>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<!-- Generate Church Suite rota feed instructions modal -->
<div class="modal fade" id="rota-instructions" tabindex="-1" aria-labelledby="rota-instructions-label" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="rota-instructions-label">Generating Rota CSV File</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h5>Step One</h5>
                <p>Log in to <a href="<?php _e($model->church_suite_href); ?>" target="_blank">Church Suite</a>.</p>
                <h5>Step Two</h5>
                <p>Click <a href="<?php _e($model->rota_href); ?>" target="_blank">here</a> to open the export page for the rota. (The dates will be set automatically to the current rota period - '<?php echo $model->rota_period; ?>', <?php echo $model->rota_period_first_day->format(C::$formats->display_day_and_month); ?> to <?php echo $model->rota_period_last_day->format(C::$formats->display_day_and_month); ?>.)</p>
                <h5>Step Three</h5>
                <p>Select the dates you wish to export - this should cover the entire period of the rota.</p>
                <h5>Step Four</h5>
                <p>Click the 'Generate' button. (This should generate a report showing you every single rota between the dates you selected.)</p>
                <h5>Step Five</h5>
                <p>Hover your mouse over the 'More' button and select 'Download CSV'.</p>
                <h5>Step Six</h5>
                <p>Close this box to return to the main screen, enter the name of the rota (e.g. '22-2'), choose the file you just downloaded, and click the 'Upload' button.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Generate Church Suite prayer calendar feed instructions modal -->
<div class="modal fade" id="prayer-calendar-instructions" tabindex="-1" aria-labelledby="prayer-calendar-instructions-label" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="prayer-calendar-instructions-label">Generating Prayer Calendar CSV File</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h5>Step One</h5>
                <p>Log in to <a href="<?php _e($model->church_suite_href); ?>" target="_blank">Church Suite</a>.</p>
                <h5>Step Two</h5>
                <p>Click <a href="<?php _e($model->prayer_adults_href); ?>" target="_blank">here</a> to open the export page for adults.<br />
                    Click <a href="<?php _e($model->prayer_children_href); ?>" target="_blank">here</a> to open the export page for children.</p>
                <h5>Step Three</h5>
                <p>Hover your mouse over the 'More' button and select 'Download CSV'.</p>
                <h5>Step Four</h5>
                <p>Close this box to return to the main screen, choose the file you just downloaded, and click the 'Upload' button.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<?php

$this->footer();
