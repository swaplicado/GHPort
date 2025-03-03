<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\SReports\Vacations_report;

class sendDailyIncidencesReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:dailyIncidencesReport';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envia por email el reporte diario de las incidencias de los colaboradores';

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
            Vacations_report::makeDailyReport();
            // return $res;
        }
    }
}
