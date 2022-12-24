<?php

set_time_limit(0);
ob_implicit_flush(true);
ob_end_flush();

$servername = $_SERVER["SERVER_NAME"];
system("php -f readAudio.php $servername");
