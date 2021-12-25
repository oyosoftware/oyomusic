<?php

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $artistid = $_GET['artistid'];
}

error_reporting(22519);
require_once('../settings.inc');
require_once('../helpers/functions.php');

$link = mysqli_connect($server, $username, $password, $database);
$sql = "use " . $database;
if (!mysqli_query($link, $sql))
    die("Database doesn't exist.");

mysqli_set_charset($link, "utf8");

$data = 'getParentArtists([';

$symbols = array("\\", "/", ":", "*", "?", "\"", "<", ">", "|", ".", "-");

$sql = "select * from artists where id=$artistid";
$result = mysqli_query($link, $sql);
$row = mysqli_fetch_assoc($result);
$name = $row["Name"];
$name = str_replace($symbols, "", $name);
$name = strunacc($name);

$sql = "select * from albums where artistid=$artistid and folder <> '' order by folder";
$result = mysqli_query($link, $sql);
$row = mysqli_fetch_assoc($result);
$folder = $row["Folder"];
$boxsetid = $row["BoxsetId"];
if ($boxsetid > 0) {
    $len = mb_strrpos($folder, "/");
    $folder = mb_substr($folder, 0, $len);
}
$previousfolder = $folder;

do {
    $len = mb_strrpos($folder, "/");
    $folder = mb_substr($folder, 0, $len);
    $pos = mb_strrpos($folder, "/");
    $newname = mb_substr($folder, $pos + 1);
    $originalnewname = $newname;
    $newname = str_replace($symbols, "", $newname);
    $newname = strunacc($newname);

    $commapos = mb_strrpos($originalnewname, ",");

    if ($commapos == 0) {

        $sqloriginalnewname = mysqli_real_escape_string($link, $originalnewname);
        $sqlnewname = mysqli_real_escape_string($link, $newname);
        $sqlpreviousfolder = mysqli_real_escape_string($link, $previousfolder);

        $sql = "select artists.id as artistid, name, albumcount, folder, " .
                "locate(substr(folder, 1, char_length(folder) - locate('/', reverse(folder))), '$sqlpreviousfolder') as pos " .
                "from artists left join albums on artists.id=artistid " .
                "where name = '$sqloriginalnewname' or name = '$sqlnewname' " .
                "having (pos > 0 or isnull(pos)) " .
                "order by name limit 1";
    } else {

        $where = "";
        $array1 = preg_split("/[\W]/u", $originalnewname);
        $array2 = preg_split("/[\W]/u", $newname);
        $originalfirststring = $array1[0];
        $firststring = $array2[0];
        for ($i = 0; $i < count($array1); $i++) {
            if ($array1[$i] != "") {
                $where .= "(name like '%$array1[$i]%' or name like '%$array2[$i]%') and ";
            }
        }
        $len = mb_strlen($where);
        $where = mb_substr($where, 0, $len - 5);

        $sqloriginalfirststring = mysqli_real_escape_string($link, $originalfirststring);
        $sqlfirststring = mysqli_real_escape_string($link, $firststring);
        $sqlpreviousfolder = mysqli_real_escape_string($link, $previousfolder);

        $sql = "select artists.id as artistid, name, albumcount, folder, " .
                "locate('$sqloriginalfirststring', name) + locate('$sqlfirststring', name) as pos1, " .
                "locate(substr(folder, 1, char_length(folder) - locate('/', reverse(folder))), '$sqlpreviousfolder') as pos2 " .
                "from artists left join albums on artists.id=artistid " .
                "where $where " .
                "having pos1 > 0 and (pos2 > 0 or isnull(pos2)) " .
                "order by pos1, name limit 1";
    }

    //echo $sql . "\r\n";
    $result = mysqli_query($link, $sql);
    $rows = mysqli_affected_rows($link);
    if ($rows > 0) {
        $row = mysqli_fetch_assoc($result);
        $newname = $row["name"];
        $newname = str_replace($symbols, "", $newname);
        $newname = strunacc($newname);
        if ($name != $newname) {
            $data .= '{id: ' . $row["artistid"] . ', name: "' . $row["name"] . '", albumcount: ' . $row["albumcount"] . '}, ';
        }
    }
    $name = $newname;
    $previousfolder = $folder;
} while ($rows > 0);

$parentfolder = $artistfolder;

$data .= '])';
echo $data;

mysqli_close($link);
?>