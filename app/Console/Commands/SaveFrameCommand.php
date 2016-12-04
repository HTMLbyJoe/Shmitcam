<?php namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;

class SaveFrameCommand extends Command {

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'cam:save';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Take a pic';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {
        $ignore_offline = $this->input->getOption('ignore-offline');

        if (!env('WEBCAM_ONLINE') && !$ignore_offline) {
            $this->error('Webcam is offline. Set WEBCAM_ONLINE to `true` in .env or override with --ignore-offline=true');
            return false;
        }

        $latest = env('WEBCAM_IMAGE_PATH');

        $stills_dir = base_path('storage/images/stills/');
        $hour_dir = $stills_dir . date('Y/m/d/H/');

        if (!file_exists($hour_dir)) {
            mkdir($hour_dir, 0755, true);
        }

        $filename = date('Y-m-d_H-i-s') . '.jpg';

        $this->info('Taking a pic: ' . $hour_dir . $filename);
        copy($latest, $hour_dir . $filename);
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['ignore-offline', null, InputOption::VALUE_OPTIONAL, 'Do this even if the camera is offline', false],
        ];
    }

}
