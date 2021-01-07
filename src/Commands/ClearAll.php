<?php

namespace ctf0\LaravelHelperCmnds\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class ClearAll extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ex:clear';

    protected $files;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear bootstrap-files/cache/config/route/view/compiled/pass-resets';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        $this->files = app('files');

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
        $this->callSilent('optimize:clear');
        $this->callSilent('config:clear');
        $this->callSilent('route:clear');
        $this->callSilent('view:clear');

        // cache
        $this->callSilent('cache:clear');
        app('cache')->store('file')->flush();

        // session
        session()->flush();
        $this->files->cleanDirectory(config('session.files'));
        
        if (Schema::hasTable(config('session.table'))) {
            \DB::table(config('session.table'))->truncate();
        }

        // log
        $this->files->put(storage_path('logs/laravel.log'), '');

        // password_resets
        if (Schema::hasTable('password_resets')) {
            $this->callSilent('auth:clear-resets');
        }

        // add any extra cmnds to run
        event('clearAll.done');

        // composer dump-autoload.
        shell_exec('composer dump-autoload');

        $this->info('All Done');
    }
}
