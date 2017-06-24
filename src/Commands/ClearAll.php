<?php

namespace ctf0\LaravelHelperCmnds\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;
use Symfony\Component\Process\Process;

class ClearAll extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ex:clear';

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
        Session::flush();
        File::put(storage_path('logs/laravel.log'), '');

        if (Schema::hasTable('password_resets')) {
            $this->callSilent('auth:clear-resets');
        }

        // add any extra cmnds to run
        event('clearAll.done');

        // composer dump-autoload.
        $this->compDump();

        $this->info('All Done');
    }

    /**
     * composer dump-autoload.
     *
     * @return [type] [description]
     */
    protected function compDump()
    {
        $comp = new Process('composer dump-autoload');
        $comp->setWorkingDirectory(base_path());

        return $comp->run();
    }
}
