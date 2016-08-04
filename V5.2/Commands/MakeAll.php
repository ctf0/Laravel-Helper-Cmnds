<?php

namespace App\Console\Commands;

use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;

class MakeAll extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ex:make:all';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make (Model,Controller,Migration,Seeder,Route,View)';

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
        $class = Str::title($this->ask('What is the Class name ex.abc'));

        Artisan::call('make:controller', [
            'name'       => $class.'Controller',
            '--resource' => true,
        ]);
        Artisan::call('make:model', [
            'name' => $class,
            '-m'   => true,
        ]);

        if ($this->confirm('Do you wish to make a Seeder? [y|N]')) {
            Artisan::call('make:seeder', [
                'name' => Str::plural($class).'TableSeerder',
            ]);
        }

        if ($this->confirm('Do you wish to add a Route? [y|N]')) {

            $name = Str::lower($class);

            // create routes/classname
            $dir     = app_path('Http/Routes');
            $content = "<?php\n\nRoute::group(['prefix' => '".$name."'], function () {\n    // routes here \n});";

            if ( ! File::exists($dir)) {
                File::makeDirectory($dir);
            }
            File::put($dir."/$class.php", $content);

            // add req_once to the main routes.php
            $route_file         = app_path('Http/routes.php');
            $route_file_content = "\nforeach (File::allFiles(__DIR__.'/Routes') as \$route_file) {\n    require_once \$route_file->getPathname();\n}";

            if ( ! strpos(File::get($route_file), $route_file_content)) {
                File::append($route_file, $route_file_content);
            }

            // create a view folder for the class
            if ($this->confirm('Do you also want to create a view folder? [y|N]')) {

                $dir = resource_path("views/$name");
                if ( ! File::exists($dir)) {
                    File::makeDirectory($dir);
                }

                if ( ! File::exists($dir.'/index.blade.php')) {
                    File::put($dir.'/index.blade.php', null);
                }
            }
        }

        $this->info('All Done');
    }
}
