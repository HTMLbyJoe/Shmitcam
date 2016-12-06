<?php namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;

class GetSunDataCommand extends Command {

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'sundata:get';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Download complete sunrise/sunset information for an entire year from usno.navy.mil';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {
        $date = $this->input->getOption('date');
        $city = $this->input->getOption('city');
        $state = $this->input->getOption('state');

        $sun_data = \App\AAhelper::getSunriseSunsetData($date, $city, $state);

        $json = json_encode($sun_data, JSON_PRETTY_PRINT);
        $this->line($json);
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['date', null, InputOption::VALUE_OPTIONAL, 'What day to get sun data for', 'today'],
            ['city', null, InputOption::VALUE_OPTIONAL, 'The city to get sun data for', 'Queens'],
            ['state', null, InputOption::VALUE_OPTIONAL, 'The state to get sun data for', 'NY'],
        ];
    }

}
