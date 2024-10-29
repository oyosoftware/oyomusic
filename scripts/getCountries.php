<?php

error_reporting(E_ERROR);

require_once('../settings.inc');
require_once('../include/oyoCountryList.php');

$link = mysqli_connect($server, $username, $password, $database);
mysqli_set_charset($link, "utf8");

$sql = "select * from countries order by country";
$result = mysqli_query($link, $sql);

$countryList = new oyoCountryList("en");
$countries = array();

while ($row = mysqli_fetch_assoc($result)) {
    $country = (object) [];
    $country->id = (int) $row["Id"];
    $country->country = htmlspecialchars($row["Country"]);
    $country->countrycode = strtolower($countryList->getCountryCode($country->country));
    $countries[] = $country;
}

$countries = 'getCountries(' . json_encode($countries, JSON_PRETTY_PRINT) . ")";
echo $countries;

mysqli_close($link);
?>