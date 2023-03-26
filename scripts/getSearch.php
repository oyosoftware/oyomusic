<?php

error_reporting(E_ERROR);

$search = filter_input(INPUT_GET, "search");
$country = filter_input(INPUT_GET, "country");
$year = filter_input(INPUT_GET, "year");
$genre = filter_input(INPUT_GET, "genre");
$pageno = filter_input(INPUT_GET, "pageno");

require_once('../settings.inc');
require_once('../include/date_time.php');

$link = mysqli_connect($server, $username, $password, $database);
mysqli_set_charset($link, "utf8");

$first = $searchrecordspage * $pageno - $searchrecordspage;

$searches = array();

$sql = "select artists.id as artistid, name, country, " .
        "albums.id as albumid, released, title, disccount, " .
        "format, playingtime, genre, " .
        "imagefilename, " .
        "1 as query " .
        "from artists " .
        "left join countries on countries.id = countryid " .
        "inner join albums on artists.id = artistid " .
        "left join formats on formats.id = formatid " .
        "left join genres on genres.id = genreid " .
        "where boxsetindex = -1 ";

$search = str_replace('\\', '\\\\', $search);
$search = str_replace('"', '\"', $search);
$search = str_replace('%', '\%', $search);
$search = mysqli_real_escape_string($link, $search);
$searchname = "name like '%$search%'";
$searchtitle = "title like '%$search%'";
$sql .= "and ($searchname or $searchtitle) ";

if (!empty($country)) {
    $sql .= "and country = '$country' ";
}
if (!empty($year)) {
    $sql .= "and released = $year ";
}
if (!empty($genre)) {
    $sql .= "and genre = '$genre' ";
}

$sql .= "union ";

$sql .= "select artists.id as artistid, name, country, " .
        "albums.id as albumid, released, title, disccount, " .
        "format, playingtime, genre, " .
        "imagefilename, " .
        "2 as query " .
        "from artists " .
        "left join countries on countries.id = countryid " .
        "inner join albums on artists.id = artistid " .
        "left join formats on formats.id = formatid " .
        "left join genres on genres.id = genreid " .
        "where boxsetindex = -1 ";

$sql .= "and not ($searchname or $searchtitle) ";
$words = mb_split(' ', $search);
foreach ($words as $key => $word) {
    if ($key == 0) {
        $searchname = "(";
        $searchtitle = "(";
    }
    if ($key > 0) {
        $searchname = $searchname . " and ";
        $searchtitle = $searchtitle . " and ";
    }
    $searchname = $searchname . "name like '%$word%'";
    $searchtitle = $searchtitle . "title like '%$word%'";
}
$searchname = $searchname . ")";
$searchtitle = $searchtitle . ")";
$sql .= "and ($searchname or $searchtitle) ";

if (!empty($country)) {
    $sql .= "and country = '$country' ";
}
if (!empty($year)) {
    $sql .= "and released = $year ";
}
if (!empty($genre)) {
    $sql .= "and genre = '$genre' ";
}

$sql .= "order by query, name, released, title ";
$sql .= "limit $first, $searchrecordspage";

$result = mysqli_query($link, $sql);

while ($row = mysqli_fetch_assoc($result)) {
    $search = (object) [];
    $search->artistid = (int) $row["artistid"];
    $search->name = $row["name"];
    $search->country = $row["country"];
    $search->albumid = (int) $row["albumid"];
    $search->released = (int) $row["released"];
    $search->title = $row["title"];
    $search->disccount = (int) $row["disccount"];
    $search->format = $row["format"];
    $time = formattime($row["playingtime"]);
    $search->playingtime = $time;
    $search->genre = $row["genre"];
    $search->imagefilename = $row["imagefilename"];
    $searches[] = $search;
}

$searches = 'getSearch(' . json_encode($searches, JSON_PRETTY_PRINT) . ")";
echo $searches;

mysqli_close($link);
?>