<?php

namespace App\Console\Commands;

use App\SReports\Vacations_report;
use Illuminate\Console\Command;

class sendIncidencesReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:incidencesReport';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envia por email el reporte de las incidencias de los colaboradores';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $config = \App\Utils\Configuration::getConfigurations();
        if($config->incidents_report->enabled){
            $res = Vacations_report::makeVacationsReport();
            return $res;
        }
    }
}
