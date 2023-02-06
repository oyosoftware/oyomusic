<?php

error_reporting(E_ERROR);

$albumid = filter_input(INPUT_GET, "albumid");

require_once('../settings.inc');

$link = mysqli_connect($server, $username, $password, $database);
mysqli_set_charset($link, "utf8");

$audiosource = str_ireplace("\\", "/", $audiosource);

switch (true) {
    case mb_substr($audiosource, 0, 2) === "//":
        break;
    case mb_substr($audiosource, 1, 2) === ":/":
        break;
    case mb_substr($audiosource, 0, 7) === "file://":
        break;
    case mb_substr($audiosource, 0, 1) === "/":
        $documentroot = filter_input(INPUT_SERVER, 'DOCUMENT_ROOT');
        if (is_null($documentroot)) {
            $documentroot = filter_input(INPUT_ENV, 'DOCUMENT_ROOT');
        }
        $audiosource = $documentroot . $audiosource;
        break;
    default:
        $audiosource = "../" . $audiosource;
        break;
}

$sql = "select * from albums inner join tracks on id=albumid where id=$albumid or boxsetid=$albumid";
$result = mysqli_query($link, $sql);

$filesexist = false;
while ($row = mysqli_fetch_assoc($result)) {
    $file = $audiosource . $row["Folder"] . '/' . $row["FileName"];
    if (file_exists($file)) {
        $filesexist = true;
        break;
    }
}

echo $filesexist;

mysqli_close($link);
?>