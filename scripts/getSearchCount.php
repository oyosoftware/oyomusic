<?php

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (isset($_GET['search'])) $search = $_GET['search'];
    if (isset($_GET['country'])) $country = $_GET['country'];
    if (isset($_GET['year'])) $year = $_GET['year'];
    if (isset($_GET['genre'])) $genre = $_GET['genre'];
}

error_reporting(22519);
require_once('../settings.inc');
require_once('../helpers/functions.php');

$link = mysqli_connect($server, $username, $password, $database);
$sql = "use " . $database;
if (!mysqli_query($link, $sql))
    die("Database doesn't exist.");

mysqli_set_charset($link, "utf8");

$sql = "select count(*) as records " .
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

if ($country <> "") {
    $sql .= "and country = '$country' ";
}
if ($year <> 0) {
    $sql .= "and released = $year ";
}
if ($genre <> "") {
    $sql .= "and genre = '$genre' ";
}

$sql .= "union ";

$sql .= "select count(*) as records " .
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

if ($country <> "") {
    $sql .= "and country = '$country' ";
}
if ($year <> 0) {
    $sql .= "and released = $year ";
}
if ($genre <> "") {
    $sql .= "and genre = '$genre' ";
}

$result = mysqli_query($link, $sql);

$records = 0;
while ($row = mysqli_fetch_assoc($result)) {
    $records += $row["records"];
}

echo $records;

mysqli_close($link);
?>