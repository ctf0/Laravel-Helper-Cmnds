<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ReMigrate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ex:re:migrate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'remigrate & seed';

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
        Artisan::call('cache:clear');
        Artisan::call('migrate:refresh', [
            '--seed' => true,
        ]);
    }
}
