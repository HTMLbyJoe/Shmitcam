<?php

namespace App;

class GifHelper
{
    public static function makeGifOfSunrise($date, $city, $state, $options = [], $is_sunset = false)
    {
        $default_options = [
            'frame_count' => 20,
            'delay' => 20,
        ];

        $options = array_merge($default_options, $options);

        $sun_times = self::getSunTimes($date, $city, $state, $is_sunset);

        $time_begin = $sun_times[0];
        $time_end = $sun_times[1];

        return self::makeGif(
            $time_begin,
            $time_end,
            $options['frame_count'],
            $options['delay']
        );
    }

    public static function makeGifOfSunset($date, $city, $state, $options = [])
    {
        return self::makeGifOfSunrise($date, $city, $state, $options, true);
    }

    public static function makeVideoOfSunrise($options = [])
    {
        $default_options = [
            'date' => 'today',
            'city' => env('CAMERA_CITY'),
            'state' => env('CAMERA_STATE'),
            'framerate' => 30,
            'is_sunset' => false,
        ];

        $options = array_merge($default_options, $options);

        $date = $options['date'];
        $city = $options['city'];
        $state = $options['state'];
        $is_sunset = $options['is_sunset'];
        $framerate = $options['framerate'];

        $sun_times = self::getSunTimes($date, $city, $state, $is_sunset);

        $time_begin = $sun_times[0];
        $time_end = $sun_times[1];

        return self::makeVideo(
            $time_begin,
            $time_end,
            $framerate
        );
    }

    public static function makeVideoOfSunset($options = [])
    {
        $options['is_sunset'] = true;
        return self::makeGifOfSunrise($options);
    }

    public static function getSunTimes($date, $city, $state, $is_sunset = false)
    {
        $timestamp = strtotime($date);
        $month_day = date('F j', $timestamp);

        $sun_data = \App\AAhelper::getSunriseSunsetData($month_day, $city, $state);

        $offsets = [
            'civil_twilight_begin' => env('GIF_SUNRISE_BEGIN_OFFSET', 0),
            'sunrise' => env('GIF_SUNRISE_END_OFFSET', 0),
            'sunset' => env('GIF_SUNSET_BEGIN_OFFSET', 0),
            'civil_twilight_end' => env('GIF_SUNSET_END_OFFSET', 0),
        ];

        foreach ($sun_data as $key => $time) {
            // Apply offsets set in .env
            $sun_data[$key] = strtotime($time, $timestamp) + (60 * $offsets[$key]);
        }

        if (!$is_sunset) {
            $time_begin = date('Y/m/d H:i:s', $sun_data['civil_twilight_begin']);
            $time_end = date('Y/m/d H:i:s', $sun_data['sunrise']);
        } else {
            $time_begin = date('Y/m/d H:i:s', $sun_data['sunset']);
            $time_end = date('Y/m/d H:i:s', $sun_data['civil_twilight_end']);
        }

        return [$time_begin, $time_end];
    }

    public static function makeGif($time_start = '-3 hours', $time_end = 'now', $frame_count = 20, $delay = 20)
    {
        $start_int = strtotime($time_start);
        $end_int = strtotime($time_end);

        // How many seconds the GIF should represent
        $span_seconds = $end_int - $start_int;

        // How many seconds should pass by per frame
        $seconds_between_frames = $span_seconds / ($frame_count - 1);

        for ($i = 0; $i < $frame_count; $i++) {
            $frame_times[$i] = $start_int + ($seconds_between_frames * $i);
        }

        $huge_range_of_files = self::getAllFilePaths($frame_times);

        if (empty($huge_range_of_files)) {
            return false;
        }

        $close_enough = [];
        foreach ($frame_times as $key => $timestamp) {
            $close_enough[] = self::findClosest($huge_range_of_files, $timestamp);
        }

        $gifs_dir = base_path('storage/images/gifs/');

        if (!file_exists($gifs_dir)) {
            mkdir($gifs_dir, 0755, true);
        }

        $gif_filename = date('Y-m-d_H-i-s', $start_int) . '.' . date('Y-m-d_H-i-s', $end_int) . '.gif';

        $gif_filepath = $gifs_dir . $gif_filename;

        self::gifFromFrames($close_enough, $gif_filepath, $delay);

        return $gif_filepath;
    }

    public static function makeVideo($time_start = '-3 hours', $time_end = 'now', $framerate = 30)
    {
        $start_int = strtotime($time_start);
        $end_int = strtotime($time_end);

        $huge_range_of_files = self::getAllFilePathsByRange($start_int, $end_int);

        if (empty($huge_range_of_files)) {
            return false;
        }

        $frames = array_filter($huge_range_of_files, function ($time) use ($start_int, $end_int) {
            return $time > $start_int && $time < $end_int;
        }, ARRAY_FILTER_USE_KEY);

        $vids_dir = base_path('storage/images/videos/');

        if (!file_exists($vids_dir)) {
            mkdir($vids_dir, 0755, true);
        }

        $vid_filename = date('Y-m-d_H-i-s', $start_int) . '.' . date('Y-m-d_H-i-s', $end_int) . '.mov';

        $vid_filepath = $vids_dir . $vid_filename;

        self::videoFromFrames($frames, $vid_filepath, $framerate);

        return $vid_filepath;
    }

    public static function gifFromFrames($frames, $gif_filepath, $delay = 20)
    {
        $animation = new \Imagick();
        $animation->setFormat('gif');

        foreach ($frames as $filepath) {
            try {
                $frame = new \Imagick($filepath);
                $frame->scaleImage(500, 0);

                $animation->addImage($frame);
                $animation->setImageDelay($delay);
                $animation->nextImage();
            } catch (\Exception $e) {
                // TODO: Do real error logging
                // $error_message = 'Something weird happened while getting this frame. Skipping it.' . "\n";
                // $error_message .= $e->getMessage();
                // $this->error($error_message);
            }
        }

        $animation->writeImages($gif_filepath, true);
    }

    public static function videoFromFrames($frames, $video_filepath, $framerate = 30)
    {
        $video_temp_path = sys_get_temp_dir() . '/shmitcam/vid';

        if (!is_dir($video_temp_path)) {
            mkdir($video_temp_path, 0755, true);
        }

        array_map('unlink', glob("$video_temp_path/frame-*"));

        $i = 0;
        foreach ($frames as $filepath) {
            copy($filepath, $video_temp_path . sprintf('/frame-%04d.jpg', $i));
            $i++;
        }

        $framerate = 10;
        $command = sprintf(
            'avconv -framerate %d -f image2 -i %s/frame-%%04d.jpg -c:v libx264 -crf 1 %s -y',
            $framerate,
            escapeshellarg($video_temp_path),
            escapeshellarg($video_filepath)
        );

        return exec($command);
    }

    /**
     * Get the entire range of actual filepaths that we should choose
     * our frames from, based on a start time and an end time
     *
     * @return array Array of actual filenames
     */
    private static function getAllFilePathsByRange($start_int, $end_int)
    {
        $frame_times = [];
        $seconds_in_an_hour = 3600;

        for ($i = $start_int; $i <= $end_int; $i += $seconds_in_an_hour) {
            $frame_times[] = $i;
        }

        return self::getAllFilePaths($frame_times);
    }

    /**
     * Get the entire range of actual filepaths that we should
     * choose our frames from, based on an array of times
     *
     * @return array Array of actual filenames
     */
    private static function getAllFilePaths($frame_times)
    {
        $hour_dirs = self::getAllHourDirectories($frame_times);

        $jpgs = [];
        foreach($hour_dirs as $dir) {
            $jpgs += self::getAllJpgs($dir);
        }

        return $jpgs;
    }

    /**
     * Assemble possible frame directory paths for a set of given timestamps
     *
     * @return array Array of directory paths
     */
    private static function getAllHourDirectories($frame_times)
    {
        $stills_dir = base_path('storage/images/stills/');

        $hour_dirs = [];
        foreach ($frame_times as $time) {
            $hour_dirs[] = $stills_dir . date('Y/m/d/H/', $time);
        }

        return array_unique($hour_dirs);
    }

    /**
     * Get all JPG files from the given directory
     *
     * @return array Array of JPG filepaths
     */
    private static function getAllJpgs($dir)
    {
        if (!is_dir($dir)) {
            return [];
        }

        $files = scandir($dir);

        $jpgs = [];

        foreach($files as $filename) {
            $extension = pathinfo($filename, PATHINFO_EXTENSION);

            if ($filename[0] === '.' || $extension !== 'jpg') {
                continue;
            }

            $timestamp = \DateTime::createFromFormat('Y-m-d_H-i-s\.\j\p\g', $filename)->getTimestamp();
            $jpgs[$timestamp] = $dir . $filename;
        }

        return $jpgs;
    }

    /**
     * Find the closest filepath to the given timestamp
     *
     * @param $filepaths The list of actual JPG frames that exist
     * @param $timestamp The timestamp we're looking for something close enough
     * @return string The closest filepath
     */
    private static function findClosest($filepaths, $timestamp)
    {
        $closest = null;

        foreach ($filepaths as $filetime => $value) {
            if ($closest === null || abs($timestamp - $closest) > abs($filetime - $timestamp)) {
                $closest = $filetime;
            }
        }

        return $filepaths[$closest];
    }
}
