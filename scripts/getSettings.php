<?php

header("Access-Control-Allow-Origin: *");
error_reporting(22519);
require_once('../settings.inc');

$data = 'getSettings({'
            . 'pagetitle            : "'.   $pagetitle          . '", '
            . 'headertitle          : "'.   $headertitle        . '", '
            . 'recordspage          : ' .   $recordspage        . ', '
            . 'searchrecordspage    : ' .   $searchrecordspage  . ', '
            . 'pagerange            : ' .   $pagerange          . ', '
            . 'audiosource          : "' .  $audiosource        . '", '
            . 'imagepath            : "' .  $imagepath          . '",'
            . 'imagepaththumbs      : "' .  $imagepaththumbs    . '"'
        . '})';

echo $data;

?>

