<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class ClearAll extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ex:clear:all';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear cache/config/route/view/compiled/pass-resets';

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
        $this->callSilent('clear-compiled');
        $this->callSilent('cache:clear');
        $this->callSilent('config:clear');
        $this->callSilent('route:clear');
        $this->callSilent('view:clear');

        if (Schema::hasTable('password_resets')) {
            $this->callSilent('auth:clear-resets');
        }

        $this->info('All Done');
    }
}
