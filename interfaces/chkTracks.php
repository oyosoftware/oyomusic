<?php

error_reporting(E_ERROR);

$dsn = "odbc:driver={Microsoft Access Driver (*.mdb, *.accdb)};dbq=C:\\Users\\Ruud\\Documenten\\Hobby\\Muziek\\Verzameling\\MP3.mdb;charset=utf16le";
$pdolink = new PDO($dsn);

$sql = "select track.TrackID, AlbumID, DiscNo, Index,"
        . "Mid(StrConv(Title, 64),   1, 254) as ucTitle1,"
        . "Mid(StrConv(Title, 64),   255, 254) as ucTitle2,"
        . "Length, AudioBitrate,"
        . "Mid(StrConv(Folder, 64),   1, 254) as ucFolder1,"
        . "Mid(StrConv(Folder, 64),   255, 254) as ucFolder2,"
        . "Mid(StrConv(Filename, 64),   1, 254) as ucFilename1,"
        . "Mid(StrConv(Filename, 64),   255, 254) as ucFilename2,"
        . "LastModified "
        . "from track inner join audiofilelink on track.trackid = audiofilelink.trackid order by folder, filename";

$pdoresult = $pdolink->query($sql);

while ($row = $pdoresult->fetch()) {
    $tracktitle = mb_convert_encoding($row["ucTitle1"] . $row["ucTitle2"], 'UTF-8', 'UTF-16LE');
    $folder = mb_convert_encoding($row["ucFolder1"] . $row["ucFolder2"], 'UTF-8', 'UTF-16LE');
    $filename = mb_convert_encoding($row["ucFilename1"] . $row["ucFilename2"], 'UTF-8', 'UTF-16LE');

    $words = preg_split("[ ] ", $filename);
    foreach ($words as $word) {

        $firstletter = mb_substr($word, 0, 1);
        $ord = mb_ord($firstletter);
        if ($ord == null) {
            echo $folder . $filename . "\r\n";
        }
//        if ($ord >= 97 and $ord <= 122) {
//            echo $filename . "\r\n";
//            echo $firstletter . " " . $ord . "\r\n";
//        }
    }
}
