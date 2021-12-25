<?php

$cmd = "php -f intMP3toSQL.php";
$descriptorspec = array(
    0 => array("pipe", "r"), // stdin is a pipe that the child will read from
    1 => array("pipe", "w"), // stdout is a pipe that the child will write to
    2 => array("pipe", "/tmp/error-output.txt", "a") // stderr is a file to write to
);

$process = proc_open($cmd, $descriptorspec, $pipes);



echo "hallo";
