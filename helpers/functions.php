<?php

function localdate(string $format, int $timestamp, string $locale) {

    $oldlocale = setlocale(LC_TIME, 0);
    setlocale(LC_TIME, $locale);

    $date = '';
    for ($i = 0; $i < strlen($format); $i++) {

        $char = substr($format, $i, 1);
        switch ($char) {
            case 'D':
                $date .= strftime('%a', $timestamp);
                break;
            case 'l':
                $date .= strftime('%A', $timestamp);
                break;
            case 'M':
                $date .= strftime('%b', $timestamp);
                break;
            case 'F':
                $date .= strftime('%B', $timestamp);
                break;
            default:
                $date .= date($char, $timestamp);
                break;
        }
    }

    setlocale(LC_TIME, $oldlocale);

    return $date;
}

function formattime(int $totalseconds) {
    $days = floor($totalseconds / 86400);
    $hours = floor(($totalseconds - ($days * 86400)) / 3600);
    $minutes = floor(($totalseconds - ($days * 86400) - ($hours * 3600)) / 60);
    $seconds = $totalseconds - ($days * 86400) - ($hours * 3600) - ($minutes * 60);

    if ($days > 0) {
        $adays = $days . " ";
        $ahours = $hours . ":";
        $aminutes = sprintf('%02d', $minutes) . ":";
        $aseconds = sprintf('%02d', $seconds);
    }
    if ($days == 0) {
        $adays = "";
    }
    if ($days == 0 and $hours > 0) {
        $ahours = $hours . ":";
        $aminutes = sprintf('%02d', $minutes) . ":";
        $aseconds = sprintf('%02d', $seconds);
    }
    if ($days == 0 and $hours == 0) {
        $ahours = "";
        $aminutes = $minutes . ":";
        $aseconds = sprintf('%02d', $seconds);
    }

    $timestring = $adays . $ahours . $aminutes . $aseconds;
    return $timestring;
}

function strunacc($string) {
    $strFrom = "ÀÁÂÃÄÅÆàáâãäåæĀāĂăĄąÇçĆćĈĉĊċČčĎďĐđÈÉÊËèéêëĒēĔĕĖėĘęĚěĜĝĞğĠġĢģĤĥĦħĨÌÍÎÏìíîïĩĪīĬĭĮįİıĲĳĴĵĶķĸĹĺĻļĽľĿŀŁłÑñŃńŅņŇňŉŊŋÒÓÔÕÖòóôõöŌōŎŏŐőŒœØøŔŕŖŗŘřßŚśŜŝŞşŠšŢţŤťŦŧÙÚÛÜùúûüŨũŪūŬŭŮůŰűŲųŴŵÝýÿŶŷŸŹźŻżŽžёЁ";
    $strFrom = array_map(function ($i) use ($strFrom) {
        return mb_substr($strFrom, $i, 1);
    }, range(0, mb_strlen($strFrom) - 1));

    $strTo = "AAAAAAAaaaaaaaAaAaAaCcCcCcCcCcDdDdEEEEeeeeEeEeEeEeEeGgGgGgGgHhHhIIIIIiiiiiIiIiIiIiIiJjKkkLlLlLlLlLlNnNnNnNnnNnOOOOOoooooOoOoOoEeOoRrRrRrSSsSsSsSsTtTtTtUUUUuuuuUuUuUuUuUuUuWwYyyYyYZzZzZzeE";
    $strTo = array_map(function ($i) use ($strTo) {
        return mb_substr($strTo, $i, 1);
    }, range(0, mb_strlen($strTo) - 1));

    $string = str_replace($strFrom, $strTo, $string);
    return $string;
}

function stracccmp($string1, $string2) {
    $string1 = strunacc($string1);
    $string2 = strunacc($string2);
    $value = strcmp($string1, $string2);
    return $value;
}

function stracccasecmp($string1, $string2) {
    $string1 = strunacc($string1);
    $string2 = strunacc($string2);
    $value = strcasecmp($string1, $string2);
    return $value;
}

function access_escape($string) {
    $symbolsin = array("[", "'", '"', "?", "*", "#", "%", "_");
    $symbolsout = array("[[]", "''", '""', "[?]", "[*]", "[#]", "[%]", "[_]");
    $string = str_replace($symbolsin, $symbolsout, $string);
    return $string;
}

?>