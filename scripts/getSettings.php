<?php

//header("Access-Control-Allow-Origin: *");
error_reporting(E_ERROR);
require_once('../settings.inc');

$audiosource = str_ireplace("\\", "/", $audiosource);
$imagepath = str_ireplace("\\", "/", $imagepath);
$imagepaththumbs = str_ireplace("\\", "/", $imagepaththumbs);

$data = 'getSettings({'
        . 'pagetitle            : "' . $pagetitle . '", '
        . 'headertitle          : "' . $headertitle . '", '
        . 'recordspage          : ' . $recordspage . ', '
        . 'searchrecordspage    : ' . $searchrecordspage . ', '
        . 'pagerange            : ' . $pagerange . ', '
        . 'audiosource          : "' . $audiosource . '", '
        . 'imagepath            : "' . $imagepath . '",'
        . 'imagepaththumbs      : "' . $imagepaththumbs . '"'
        . '})';

echo $data;
?>