<?php

namespace App;

class AAhelper
{

    public static $base_api_url = 'http://api.usno.navy.mil/';

    public static function getSunDataByDay($date, $place)
    {
        if ($date === 'today') {
            $timestamp = time();
        } else {
            $timestamp = strtotime($date);
        }

        $date = date('n/j/Y', $timestamp);

        $path = 'rstt/oneday';
        $query = [
            'date' => $date,
            'loc' => $place,
        ];

        $data = self::getRequest($path, $query)['sundata'];

        return $data;
    }

    public static function getRequest($path, $query = [])
    {
        $defaults = [
            'id' => env('AA_USNO_ID', 'shmitcam'),
        ];

        $query = array_merge($defaults, $query);

        $client = new \GuzzleHttp\Client(['base_uri' => self::$base_api_url]);
        $response = $client->get($path, ['query' => $query]);

        return json_decode($response->getBody(), true);
    }
}
