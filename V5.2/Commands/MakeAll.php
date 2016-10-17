<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

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
        $this->name  = strtolower($this->class);

        // create controller
        $this->callSilent('make:controller', [
            'name'       => $this->class.'Controller',
            '--resource' => true,
        ]);

        // create model
        // create migration
        $this->callSilent('make:model', [
            'name' => $this->class,
            '-m'   => true,
        ]);

        // create a seeder
        if ($this->confirm('Do you wish to make a Seeder? [y|N]')) {
            $this->callSilent('make:seeder', [
                'name' => str_plural($this->class).'TableSeerder',
            ]);

            $this->registerSeederFile();
        }

        // create routes
        if ($this->confirm('Do you wish to add a Route? [y|N]')) {
            $this->createRoute();
        }

        // create views
        if ($this->confirm('Do you wish to include Views? [y|N]')) {
            $this->createView();
        }

        // create validations
        $choice = $this->choice('Do you wish to include Validation?', ['>>> Choose 1, 2 or 3 <\<\<','FormRequest', 'CustomValidation', 'Non'], 3);
        switch ($choice) {
            case 'FormRequest':
                $answer = $this->ask('Validation Class name ex.xyz');
                $this->createRequest($answer.'Request');
                break;

            case 'CustomValidation':
                $answer = $this->ask('Validation Class name ex.xyz');
                $this->createValidation($answer.'Validation');
                break;

            default:
                break;
        }

        $this->info('All Done');
    }

    /**
     * [registerSeederFile description].
     *
     * @return [type] [description]
     */
    protected function registerSeederFile()
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
    }

    /**
     * [createRoute description].
     *
     * @return [type] [description]
     */
    protected function createRoute()
    {
        $dir = app_path('Http/Routes');

        $stub  = File::get(__DIR__.'/stubs/route/create.stub');
        $name  = str_replace('DummyName', $this->name, $stub);
        $class = str_replace('DummyClass', $this->class, $name);

        $this->checkDirExistence($dir);

        if ( ! File::exists("$dir/$this->class.php")) {
            File::put("$dir/$this->class.php", $class);
        }

        // add loop to the main routes.php
        $search             = '(File::allFiles(__DIR__.\'/Routes\')';
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

        $this->checkDirExistence($dir);

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
     * [createRequest description].
     *
     * @param [type] $answer [description]
     *
     * @return [type] [description]
     */
    protected function createRequest($answer)
    {
        $dir   = app_path("Http/Requests/$this->class");
        $stub  = File::get(__DIR__.'/stubs/request/create.stub');
        $class = str_replace('DummyClass', $this->class, $stub);

        $this->checkDirExistence($dir);

        if ( ! File::exists("Http/Requests/Request.php")) {
            File::copy(__DIR__.'/stubs/request/Request.php', app_path("Http/Requests/Request.php"));
        }

        if ( ! File::exists("$dir/{$answer}.php")) {
            $name = str_replace('DummyName', $answer, $class);
            File::put("$dir/{$answer}.php", $name);
        }
    }

    /**
     * [createValidation description].
     *
     * @return [type] [description]
     */
    protected function createValidation($answer)
    {
        $dir   = app_path("Http/Validations/$this->class");
        $stub  = File::get(__DIR__.'/stubs/validation/create.stub');
        $class = str_replace('DummyClass', $this->class, $stub);

        $this->checkDirExistence($dir);

        if ( ! File::exists("$dir/{$answer}.php")) {
            $name = str_replace('DummyName', $answer, $class);
            File::put("$dir/{$answer}.php", $name);
        }
    }

    /**
     * [checkDirExistence description].
     *
     * @param [type] $dir [description]
     *
     * @return [type] [description]
     */
    protected function checkDirExistence($dir)
    {
        if ( ! File::exists($dir)) {
            return File::makeDirectory($dir, 0755, true);
        }
    }
}
