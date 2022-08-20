<?php

namespace Feeds\Pages;

use Feeds\Config\Config as C;
use Feeds\Helpers\Arr;
use Feeds\Request\Request;

defined("IDX") || die("Nice try.");

// handle post requests
if (Request::$method == "POST") {
    // check password and redirect to home if it is correct
    // if it is not unset auth variable and increment count
    if (Arr::get($_POST, "pass") == C::$login->pass) {
        Request::authorise();
        Request::redirect("/");
    } else {
        Request::deny();
    }
}

// check login attempts
Request::get_login_attempts() < C::$login->max_attempts || die("You're done - try again later.");

// output header
$title = "Login";
require_once("parts/header.php");

?>

<p>Please enter the password to access.</p>

<form class="row row-cols-lg-auto g-3 align-items-center" method="POST">
    <div class="col-12">
        <label class="visually-hidden" for="pass">Password</label>
        <div class="input-group">
            <div class="input-group-text">*</div>
            <input type="password" class="form-control" name="pass" id="pass" placeholder="Password" />
        </div>
    </div>
    <div class="col-12">
        <button type="submit" class="btn btn-primary">Submit</button>
    </div>
</form>

<script type="text/javascript">
    document.getElementById("pass").focus();
</script>

<?php require_once("parts/footer.php"); ?>