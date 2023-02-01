<?php

error_reporting(E_ERROR);

if (filter_input(INPUT_SERVER, 'REQUEST_METHOD') === 'GET') {
    $letter = filter_input(INPUT_GET, "letter");
}

require_once('../settings.inc');

$link = mysqli_connect($server, $username, $password, $database);
mysqli_set_charset($link, "utf8");

$sql = "select * from artists where letter like '$letter%' and albumcount > 0 order by name";
$result = mysqli_query($link, $sql);

$data = 'getArtists([';

while ($row = mysqli_fetch_assoc($result)) {
    $data .= '{'
            . 'id: ' . $row["Id"] . ", "
            . 'name: "' . $row["Name"] . '"'
            . '}, ';
}

$data .= '])';
echo $data;

mysqli_close($link);
?>