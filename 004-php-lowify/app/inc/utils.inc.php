<?php

/**
 * Transforms a string date+time into a formatted string date+time
 *
 * @param string $date The date to transform
 * @return string The formatted date.
 * @see https://www.php.net/manual/en/datetime.format.php
 **/
function dateInDMY (string $date) : string {
    $format = "d/m/Y";

    $dateTimeObject = new DateTime($date);
    return $dateTimeObject->format($format);
}

/**
 * Transforms a int time into a formatted string time
 *
 * @param int $number The time to transform
 * @return string The formatted time.
 **/
function timeInMMSS(int $number): string {
    $minutes = floor($number / 60);
    $secondes = $number % 60;
    // Format the time using zero-padded two-digit values to maintain a consistent style
    return sprintf("%02d:%02d", $minutes, $secondes);
}

/**
 * Transforms a int number of viewers into a formatted string number
 *
 * @param int $number The number to transform
 * @return string The formatted number.
 **/
function numberWithLetter(int $number): string {
    if ($number >= 1000000) {
        return round(($number / 1000000),1) . 'M';
    } else if ($number >= 1000) {
        return round(($number / 1000),1) . 'k';
    } else {
        return (string)$number;
    }
}

/**
 * Transforms a int note into a formatted string note to maintain a consistent style
 *
 * @param float $number The note to transform
 * @return string The formatted note.
 **/
function noteFormatted(float $number): string {
    return sprintf("%.02f" . "/5", $number);
}