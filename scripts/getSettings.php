<?php

error_reporting(E_ERROR);
require_once('../settings.inc');

$audiosource = str_ireplace("\\", "/", $audiosource);
$imagepath = str_ireplace("\\", "/", $imagepath);
$imagepaththumbs = str_ireplace("\\", "/", $imagepaththumbs);

$settings = (object) [];
$settings->pagetitle = $pagetitle;
$settings->headertitle = $headertitle;
$settings->recordspage = $recordspage;
$settings->searchrecordspage = $searchrecordspage;
$settings->pagerange = $pagerange;
$settings->audiosource = $audiosource;
$settings->imagepath = $imagepath;
$settings->imagepaththumbs = $imagepaththumbs;

$settings = 'getSettings(' . json_encode($settings, JSON_PRETTY_PRINT) . ")";
echo $settings;
?>