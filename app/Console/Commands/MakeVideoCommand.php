<?php namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use \App\GifHelper;
use App\TumblrHelper;

class MakeVideoCommand extends Command {

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'video:make';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make a video from stored frames';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {
        $date = $this->input->getOption('date');
        $sunrise = $this->input->getOption('sunrise');
        $sunset = $this->input->getOption('sunset');
        $upload_to_tumblr = $this->input->getOption('upload-to-tumblr');

        $options = [
            'date' => $date,
        ];

        if ($sunrise) {
            $this->info('The video will be created based on the sunrise');
        } elseif ($sunset) {
            $this->info('The video will be created based on the sunset');
            $options['is_sunset'] = true;
        } else {
            $this->error('Must specify one of sunSET or sunRISE');
            return;
        }

        $vid_filepath = GifHelper::makeVideoOfSunrise($options);

        if ($vid_filepath) {
            $this->info('Video output to: ' . $vid_filepath);
            if ($upload_to_tumblr) {
                $uploaded = TumblrHelper::upload($vid_filepath, 'video');
                $this->info('Video uploaded to: ' . $uploaded['permalink']);
            }
        } else {
            $this->error('There was a problem. There may not be enough frames to make a video with that range yet :/');
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
            ['sunrise', 'r', InputOption::VALUE_NONE, 'Make the video of the sunrise'],
            ['sunset', 's', InputOption::VALUE_NONE, 'Make the video of the sunset'],
            ['date', null, InputOption::VALUE_OPTIONAL, 'What day to create the video of (will be made based on the sunrise/sunset times for the city and state set in .env)', 'today'],
            ['upload-to-tumblr', null, InputOption::VALUE_NONE, 'If this switch is present, upload the resulting video to Tumblr immediately after rendering'],
        ];
    }
}
