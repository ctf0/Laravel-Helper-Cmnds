<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;
use Illuminate\Support\Facades\Artisan;

class FineTune extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ex:fine:tune';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'optimize & cache route/config';

    /**
     * Create a new command instance.
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
        $comp = new Process('composer dump-autoload');
        $comp->setWorkingDirectory(base_path());
        $comp->run();

        Artisan::call('optimize');
        Artisan::call('config:cache');

        $this->info('All Done');
    }
}
