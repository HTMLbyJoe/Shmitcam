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
        $frames = $this->input->getOption('frames');
        $delay = $this->input->getOption('delay');

        $this->info("The GIF will span from $time_start to $time_end");

        $stills_dir = base_path('storage/images/stills/');

        $start_int = strtotime($time_start);
        $end_int = strtotime($time_end);

        // How many seconds the GIF should represent
        $span_seconds = $end_int - $start_int;

        // How many seconds should pass by per frame
        $seconds_between_frames = $span_seconds / ($frames - 1);

        for ($i = 0; $i < $frames; $i++) {
            $frame_times[$i] = $start_int + ($seconds_between_frames * $i);
            $frame_times[$i] = date('Y-m-d H:i:s', $frame_times[$i]);
        }

        $this->info(implode($frame_times, "\n"));

        // TODO: Grab the actual frames that are closest to these times, and turn them into a GIF
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

}
