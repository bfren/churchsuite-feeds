<?php

namespace Feeds\Pages;

use Feeds\App;

App::check();

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title><?php echo $title; ?> | Church Suite Feeds</title>
    <link href="/resources/css/bootstrap.min.css" rel="stylesheet" />
    <link href="/resources/css/feeds.min.css" rel="stylesheet" />
    <link href="/resources/css/print.min.css" rel="stylesheet" />
</head>

<body>

    <?php require_once("header-nav.php"); ?>

    <main>
        <div class="container-fluid">
