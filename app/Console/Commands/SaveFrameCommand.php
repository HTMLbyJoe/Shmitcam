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
        $this->info('Taking a pic');
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['host', null, InputOption::VALUE_OPTIONAL, 'The host address to serve the application on.', 'localhost'],
            ['port', null, InputOption::VALUE_OPTIONAL, 'The port to serve the application on.', 8000],
        ];
    }

}
