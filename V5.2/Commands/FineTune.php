<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
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
        Artisan::call('optimize');
        Artisan::call('route:cache');
        Artisan::call('config:cache');

        $this->info('All Done');
    }
}
