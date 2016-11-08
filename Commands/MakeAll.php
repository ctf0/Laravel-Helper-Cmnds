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
        $this->createModel();

        // create a seeder
        if ($this->confirm('Do you wish to make a Seeder ?')) {
            $this->callSilent('make:seeder', [
                'name' => str_plural($this->class).'TableSeerder',
            ]);

            $this->registerSeederFile();
        }

        // create routes
        if ($this->confirm('Do you wish to add a Route ?')) {
            $this->createRoute();
        }

        // create views
        if ($this->confirm('Do you wish to include Views ?')) {
            $this->createView();
        }

        // create validations
        $choice = $this->choice('Do you wish to include Validation ?', ['>>> Choose 1, 2 <\<\<', 'FormRequest', 'Non'], 2);

        if ($choice == 'FormRequest') {
            $answer = $this->ask('Validation Class name ex.xyz');
            $this->createRequest($answer.'Request');
        }

        $this->info('All Done');
    }

    /**
     * [createModel description].
     *
     * @return [type] [description]
     */
    protected function createModel()
    {
        $dir = app_path('Http/Models');
        $this->checkDirExistence($dir);

        if ( ! File::exists("$dir/BaseModel.php")) {
            File::copy(__DIR__.'/stubs/model/BaseModel.php', "$dir/BaseModel.php");
        }

        // create model
        $stub  = File::get(__DIR__.'/stubs/model/create.stub');
        $class = str_replace('DummyClass', $this->class, $stub);

        if ( ! File::exists("$dir/$this->class.php")) {
            File::put("$dir/$this->class.php", $class);
        }

        // create migration
        $table = str_plural(snake_case(class_basename($this->class)));
        $this->callSilent('make:migration', [
            'name'     => "create_{$table}_table",
            '--create' => $table,
        ]);
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
        $this->checkDirExistence($dir);

        $stub  = File::get(__DIR__.'/stubs/route/create.stub');
        $name  = str_replace('DummyName', $this->name, $stub);
        $class = str_replace('DummyClass', $this->class, $name);

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
        $dir = app_path("Http/Requests/$this->class");
        $this->checkDirExistence($dir);

        $stub  = File::get(__DIR__.'/stubs/request/create.stub');
        $class = str_replace('DummyClass', $this->class, $stub);

        if ( ! File::exists('Http/Requests/Request.php')) {
            File::copy(__DIR__.'/stubs/request/Request.php', app_path('Http/Requests/Request.php'));
        }

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
