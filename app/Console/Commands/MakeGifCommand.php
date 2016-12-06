<?php namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use \App\GifHelper;

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

        $filename = GifHelper::makeGif($time_start, $time_end, $frame_count, $delay);

        if ($filename) {
            $this->info('GIF output to: ' . $filename);
        } else {
            $this->error('There was a problem. There may not be enough frames to make a GIF with that range yet :/');
        }
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
