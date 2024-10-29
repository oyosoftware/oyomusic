<?php

/**
 * Sets the real date based on the locale.
 * After using the locale it reverts to the system locale.
 *
 * @param $format
 * @param $timestamp
 * @param $locale
 * @return date
 */
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

/**
 * Formats time as a duration and leaves out leading zeros.
 * Format is H:i:s without redundant hours, minutes or seconds.
 *
 * @param $totalseconds
 * @return string
 */
function formattime(int $totalseconds) {
    $hours = floor($totalseconds / 3600);
    $minutes = floor(($totalseconds - ($hours * 3600)) / 60);
    $seconds = $totalseconds - ($hours * 3600) - ($minutes * 60);

    if ($hours > 0) {
        $ahours = $hours . ":";
        $aminutes = sprintf('%02d', $minutes) . ":";
        $aseconds = sprintf('%02d', $seconds);
    }
    if ($hours == 0) {
        $ahours = "";
        $aminutes = $minutes . ":";
        $aseconds = sprintf('%02d', $seconds);
    }

    $timestring = $ahours . $aminutes . $aseconds;
    return $timestring;
}

?>