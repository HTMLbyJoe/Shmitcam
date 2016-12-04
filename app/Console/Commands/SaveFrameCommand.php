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
        $max_frames = $this->input->getOption('frames');
        $delay = $this->input->getOption('delay');

        if (!env('WEBCAM_ONLINE') && !$ignore_offline) {
            $this->error('Webcam is offline. Set WEBCAM_ONLINE to `true` in .env or override with --ignore-offline=true');
            return false;
        }

        $latest = env('WEBCAM_IMAGE_PATH');

        $this->info('Taking a pic');

        $images_dir = base_path('storage/images/');
        $pending_dir = $images_dir . 'pending-frames/';

        if (file_exists($pending_dir . 'animation.gif')) {
            // The GIF should not exist yet!
            // If it does, that means the process was killed while creating it
            // So let's just bail out of this whole thing since there are probably too many frames now
            $this->error('GIF found where it doesn\'t belong. Bailing out now.');
            $broken_dir = $images_dir . date('Y-m-d_H-i-s') . '_killed';
            rename($pending_dir, $broken_dir);
            return false;
        }

        if (!file_exists($pending_dir)) {
            mkdir($pending_dir, 0755, true);
        }

        $filename = date('Y-m-d_H-i-s') . '.jpg';

        copy($latest, $pending_dir . $filename);

        $file_iterator = new \FilesystemIterator($pending_dir, \FilesystemIterator::SKIP_DOTS);
        $count = iterator_count($file_iterator);

        if ($count >= $max_frames) {
            $animation = new \Imagick();
            $animation->setFormat('GIF');

            $frames = scandir($pending_dir);

            foreach($frames as $filename) {
                $extension = pathinfo($filename, PATHINFO_EXTENSION);

                if ($filename[0] === '.' || $extension !== 'jpg') {
                    continue;
                }

                try {
                    $frame = new \Imagick($pending_dir . $filename);
                    $animation->addImage($frame);
                    $animation->setImageDelay($delay);
                    $animation->nextImage();
                } catch (\Exception $e) {
                    $error_message = 'Something weird happened while getting this frame. Skipping it.' . "\n";
                    $error_message .= $e->getMessage();
                    $this->error($error_message);
                }
            }

            $animation->writeImages($pending_dir . 'animation.gif', true);

            $completed_dir = $images_dir . date('Y-m-d_H-i-s');
            rename($pending_dir, $completed_dir);
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
            ['ignore-offline', null, InputOption::VALUE_OPTIONAL, 'Do this even if the camera is offline', false],
            ['frames', null, InputOption::VALUE_OPTIONAL, 'How many frames to use per GIF', 20],
            ['delay', null, InputOption::VALUE_OPTIONAL, 'The amount of time expressed in \'ticks\' that each frame should be displayed for', 20],
        ];
    }

}
