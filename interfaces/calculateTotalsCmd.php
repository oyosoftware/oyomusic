<?php

set_time_limit(0);
ob_implicit_flush(true);
ob_end_flush();

$servername = filter_input(INPUT_SERVER, 'SERVER_NAME');
if (is_null($servername)) {
    $servername = filter_input(INPUT_ENV, 'SERVER_NAME');
}

system("php -f calculateTotals.php $servername");
?>