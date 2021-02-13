<?php

namespace ctf0\LaravelHelperCmnds\Commands;

use Illuminate\Console\Command;

class FineTune extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ex:finetune';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'cache route/config/view/event';

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
        shell_exec('composer dump-autoload');
        $this->callSilent('optimize');
        $this->callSilent('view:cache');
        $this->callSilent('event:cache');

        $this->info('All Done');
    }
}
