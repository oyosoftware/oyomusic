<?php

set_time_limit(0);
ob_implicit_flush(true);
ob_end_flush();

$servername = filter_input(INPUT_SERVER, 'SERVER_NAME');
$documentroot = filter_input(INPUT_SERVER, 'DOCUMENT_ROOT');

system("php -f collectImages.php $servername $documentroot");
