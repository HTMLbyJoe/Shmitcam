<?php namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;

class MakeGifCommand extends Command {

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'gif:make';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make a GIF from stored frames';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {
        $time_start = $this->input->getOption('time-start');
        $time_end = $this->input->getOption('time-end');
        $frame_count = $this->input->getOption('frames');
        $delay = $this->input->getOption('delay');

        $this->info("The GIF will span from $time_start to $time_end");

        $start_int = strtotime($time_start);
        $end_int = strtotime($time_end);

        // How many seconds the GIF should represent
        $span_seconds = $end_int - $start_int;

        // How many seconds should pass by per frame
        $seconds_between_frames = $span_seconds / ($frame_count - 1);

        for ($i = 0; $i < $frame_count; $i++) {
            $frame_times[$i] = $start_int + ($seconds_between_frames * $i);
        }

        $huge_range_of_files = $this->getAllFilePaths($frame_times);

        $close_enough = [];
        foreach ($frame_times as $key => $timestamp) {
            $close_enough[] = $this->findClosest($huge_range_of_files, $timestamp);
        }

        $this->info(implode($close_enough, "\n"));

        // TODO: Turn these files into a GIF
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['time-start', null, InputOption::VALUE_OPTIONAL, 'What time the GIF should begin at (defaults to three hours ago)', date('Y-m-d H:i:s', strtotime('-3 hours'))],
            ['time-end', null, InputOption::VALUE_OPTIONAL, 'What time the GIF should end at (defaults to now)', date('Y-m-d H:i:s')],
            ['frames', null, InputOption::VALUE_OPTIONAL, 'How many frames to use for the GIF', 20],
            ['delay', null, InputOption::VALUE_OPTIONAL, 'The amount of time expressed in \'ticks\' that each frame should be displayed for', 20],
        ];
    }

    /**
     * Get the entire range of actual filepaths that we should choose our frames from
     *
     * @return string The filename
     */
    private function getAllFilePaths($frame_times)
    {
        $hour_dirs = $this->getAllHourDirectories($frame_times);

        $jpgs = [];
        foreach($hour_dirs as $dir) {
            $jpgs += $this->getAllJpgs($dir);
        }

        return $jpgs;
    }

    /**
     * Assemble a possible frame filepath for a given timestamp
     *
     * @return string The filename
     */
    private function getAllHourDirectories($frame_times)
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
    private function getAllJpgs($dir)
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
    private function findClosest($filepaths, $timestamp)
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
