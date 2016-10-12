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
    protected $class;
    protected $name;

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
        $this->class = title_case($this->ask('What is the Class name ex.abc'));

        Artisan::call('make:controller', [
            'name'       => $this->class.'Controller',
            '--resource' => true,
        ]);

        Artisan::call('make:model', [
            'name' => $this->class,
            '-m'   => true,
        ]);

        if ($this->confirm('Do you wish to make a Seeder? [y|N]')) {
            Artisan::call('make:seeder', [
                'name' => str_plural($this->class).'TableSeerder',
            ]);

            $this->addSeeder();
        }

        $this->name = Str::lower($this->class);

        // create routes
        if ($this->confirm('Do you wish to add a Route? [y|N]')) {
            $this->createRoute();
        }

        // create views
        if ($this->confirm('Do you wish to include Views? [y|N]')) {
            $this->createView();
        }

        // create validations
        if ($this->confirm('Do you wish to include Validation? [y|N]')) {
            $this->createValidation();
        }

        $this->info('All Done');
    }

    /**
     * [addSeeder description].
     *
     * @return [type] [description]
     */
    protected function addSeeder()
    {
        $stub = File::get(__DIR__.'/stubs/db/seeder.stub');
        $seed = str_replace('DummySeed', str_plural($this->class), $stub);
        $dir  = database_path('seeds/DatabaseSeeder.php');

        if ( ! str_contains(File::get($dir), str_plural($this->class))) {
            $file = file($dir);
            for ($i = 0; $i < count($file); ++$i) {
                if (strstr($file[$i], '$this->')) {
                    $file[$i] = $file[$i].$seed;
                    break;
                }
            }

            return File::put($dir, $file);
        }

        return;
    }

    /**
     * [createRoute description].
     *
     * @return [type] [description]
     */
    protected function createRoute()
    {
        $dir   = app_path('Http/Routes');
        $stub  = File::get(__DIR__.'/stubs/route/create.stub');
        $name  = str_replace('DummyName', $this->name, $stub);
        $class = str_replace('DummyClass', $this->class, $name);

        if ( ! File::exists($dir)) {
            File::makeDirectory($dir);
        }

        if ( ! File::exists("$dir/$this->class.php")) {
            File::put("$dir/$this->class.php", $class);
        }

        // add loop to the main routes.php
        $search             = 'foreach (File::allFiles(__DIR__.\'/Routes\')';
        $route_file         = app_path('Http/routes.php');
        $route_file_content = File::get(__DIR__.'/stubs/route/web.stub');

        if ( ! str_contains(File::get($route_file), $search)) {
            File::append($route_file, $route_file_content);
        }
    }

    /**
     * [createView description].
     *
     * @return [type] [description]
     */
    protected function createView()
    {
        $dir = resource_path("views/$this->name");

        if ( ! File::exists($dir)) {
            File::makeDirectory($dir);
        }

        $methods = [
            'create',
            'show',
            'edit',
        ];

        $stub = File::get(__DIR__.'/stubs/view/create.stub');

        foreach ($methods as $one) {
            if ( ! File::exists("$dir/$one.blade.php")) {
                File::put("$dir/$one.blade.php", $stub);
            }
        }
    }

    /**
     * [createValidation description].
     *
     * @return [type] [description]
     */
    protected function createValidation()
    {
        $dir   = app_path("Http/Validations/$this->class");
        $stub  = File::get(__DIR__.'/stubs/validation/create.stub');
        $class = str_replace('DummyClass', $this->class, $stub);

        if ( ! File::exists($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        $methods = [
            'Store',
            'Update',
        ];

        foreach ($methods as $type) {
            if ( ! File::exists("$dir/{$type}Validation.php")) {
                $name = str_replace('DummyName', $type, $class);
                File::put("$dir/{$type}Validation.php", $name);
            }
        }
    }
}
