<?php

error_reporting(E_ERROR);

$filename = filter_input(INPUT_GET, "source");
readfile($filename);
?>
