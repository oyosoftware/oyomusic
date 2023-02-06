<?php

set_time_limit(0);
ob_implicit_flush(true);
ob_end_flush();

$servername = filter_input(INPUT_SERVER, 'SERVER_NAME');
if (is_null($servername)) {
    $servername = filter_input(INPUT_ENV, 'SERVER_NAME');
}
$documentroot = filter_input(INPUT_SERVER, 'DOCUMENT_ROOT');
if (is_null($documentroot)) {
    $documentroot = filter_input(INPUT_ENV, 'DOCUMENNT_ROOT');
}

system("php -f readAudio.php $servername $documentroot");
?>