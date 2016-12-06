<?php

namespace App;

use \Symfony\Component\DomCrawler\Crawler;
use Cache;

class AAhelper
{
    const TASK_SUN_RISE_SET = 0;
    const TASK_MOON_RISE_SET = 1;
    const TASK_CIVIL_TWILIGHT = 2;
    const TASK_NAUTICAL_TWILIGHT = 3;
    const TASK_ASTRONOMICAL_TWILIGHT = 4;

    public static function getSunDataByDayApi($date, $place)
    {
        if ($date === 'today') {
            $timestamp = time();
        } else {
            $timestamp = strtotime($date);
        }

        $date = date('n/j/Y', $timestamp);

        $query = [
            'date' => $date,
            'loc' => $place,
        ];

        $url = 'http://api.usno.navy.mil/rstt/oneday';
        $response = self::getRequest($url, $query);

        return json_decode($response, true)['sundata'];
    }

    public static function getSunriseSunsetData($date, $city, $state)
    {
        $year = date('Y', strtotime($date));

        $table_sunrise_sunset = self::getTableForYear($year, $city, $state, self::TASK_SUN_RISE_SET);
        $table_civil_twilight = self::getTableForYear($year, $city, $state, self::TASK_CIVIL_TWILIGHT);

        $sunrise_sunset = self::getDataForDayFromTable($table_sunrise_sunset, $date);
        $civil_twilight = self::getDataForDayFromTable($table_civil_twilight, $date);

        return [
            'civil_twilight_begin' => $civil_twilight[0],
            'sunrise' => $sunrise_sunset[0],
            'sunset' => $sunrise_sunset[1],
            'civil_twilight_end' => $civil_twilight[1],
        ];
    }

    public static function getDataForDayFromTable($table, $time)
    {
        $timestamp = strtotime($time);
        $day = date('j', $timestamp);
        $month = date('n', $timestamp);

        // The table is 0 indexed so the first days of the month are in $table[0]
        $row_number = $day - 1;

        // Multiply this by 11 for each month since they look like this: `  0525 1829`
        $month_offset = ($month - 1) * 11;

        // Add four since each row begins with two digits for
        // the row label, and two for the spaces after the digits
        $month_offset += 4;

        // Use $month_offset to get the data for that day
        // (it's always nine characters long; ex: `0525 1829`)
        $day_data_str = substr($table[$row_number], $month_offset, 9);

        // Each day has two four-digit numbers separated by a space so turn them into an array
        $day_data = explode(' ', $day_data_str);

        foreach ($day_data as $key => $time) {
            // Add the colon so they look like times (because they are!)
            $day_data[$key] = substr_replace($day_data[$key], ':', 2, 0);
        }

        return $day_data;
    }

    public static function getTableForYear($year, $city, $state, $task_id = self::TASK_SUN_RISE_SET)
    {
        $cache_key = "table:$task_id:$year:$state:$city";

        if (Cache::has($cache_key)) {
            // Fetch the table from the cache if it exists
            return Cache::get($cache_key);
        }

        $query = [
            'year' => $year,
            'place' => $city,
            'state' => $state,
            'task' => $task_id,
        ];

        $url = 'http://aa.usno.navy.mil/cgi-bin/aa_rstablew.pl';
        $html = (string) self::getRequest($url, $query);

        $crawler = new Crawler($html);
        $table_str = $crawler->filter('pre')->first()->text();
        $table_str = trim($table_str);

        // Split the table into an array (one line per element)
        $table_arr = explode("\n", $table_str);

        // Just get the lines that have the actual data (no headers)
        $lines = array_slice($table_arr, 9, 31);

        // Save it into the cache forever before returning
        Cache::forever($cache_key, $lines);
        return $lines;
    }

    public static function getRequest($url, $query = [])
    {
        $defaults = [
            'id' => env('AA_USNO_ID', 'shmitcam'),
        ];

        $query = array_merge($defaults, $query);

        $client = new \GuzzleHttp\Client();
        $response = $client->get($url, ['query' => $query]);
        return $response->getBody();
    }
}
