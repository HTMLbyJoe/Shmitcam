<?php

namespace App;

class GifHelper
{
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

        $animation = new \Imagick();
        $animation->setFormat('GIF');

        foreach ($close_enough as $filepath) {
            try {
                $frame = new \Imagick($filepath);
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

        $gifs_dir = base_path('storage/images/gifs/');

        if (!file_exists($gifs_dir)) {
            mkdir($gifs_dir, 0755, true);
        }

        $gif_filename = date('Y-m-d_H-i-s', $start_int) . '.' . date('Y-m-d_H-i-s', $end_int) . '.gif';

        $animation->writeImages($gifs_dir . $gif_filename, true);

        return $gifs_dir . $gif_filename;
    }


    /**
     * Get the entire range of actual filepaths that we should choose our frames from
     *
     * @return string The filename
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
     * Assemble a possible frame filepath for a given timestamp
     *
     * @return string The filename
     */
    private static function getAllHourDirectories($frame_times)
    {
        $stills_dir = base_path('storage/images/stills/');

        $hour_dirs = [];
        foreach ($frame_times as $key => $time) {
            $hour_dirs[] = $stills_dir . date('Y/m/d/H/', $time);
        }

        return array_unique($hour_dirs);
    }

    /**
     * Get all JPG files from the given directory
     *
     * @return string The filename
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
