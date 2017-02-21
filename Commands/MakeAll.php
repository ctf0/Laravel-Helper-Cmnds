<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;

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
    protected $validation;

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

        // create validations
        if ($this->confirm('Do you wish to include Validation ?')) {
            $this->validation = true;
            $this->createRequest();
        }

        // create controller
        if ($this->confirm('Do you wish to include "Route Model Binding" ?')) {
            $this->createRMB();
        } else {
            if (!$this->validation) {
                $this->callSilent('make:controller', [
                    'name'       => $this->class.'Controller',
                    '--resource' => true,
                ]);
            }

            $this->createController();
        }

        // create model
        // create migration
        $this->createModel();

        // create a seeder
        if ($this->confirm('Do you wish to create & register a DB Seeder ?')) {
            $this->callSilent('make:seeder', [
                'name' => str_plural($this->class).'TableSeerder',
            ]);

            $this->registerSeederFile();
        }

        // create routes
        if ($this->confirm('Do you wish to add Routes ?')) {
            $this->createRoute();
        }

        // create views
        if ($this->confirm('Do you wish to include Views ?')) {
            $this->createView();
        }

        // composer dump-autoload
        $this->compDump();

        $this->info('All Done');
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
        $dir = app_path('Http/Requests');
        $this->checkDirExistence($dir);

        if (!File::exists('Http/Requests/Request.php')) {
            File::copy(__DIR__.'/stubs/request/Request.php', app_path('Http/Requests/Request.php'));
        }

        $methods = [
            'Update',
            'Store',
        ];

        foreach ($methods as $one) {
            $fileName = $this->class.$one.'Request';
            if (!File::exists("$dir/$fileName.php")) {
                $stub  = File::get(__DIR__.'/stubs/request/create.stub');
                $class = str_replace('DummyClass', $fileName, $stub);

                File::put("$dir/$fileName.php", $class);
            }
        }
    }

    /**
     * [createRMB description].
     *
     * @return [type] [description]
     */
    protected function createRMB()
    {
        $dir = app_path('Http/Controllers');
        $this->checkDirExistence($dir);

        $controller = $this->class.'Controller';
        if (!File::exists("$dir/$controller.php")) {
            $stub = $this->validation
            ? File::get(__DIR__.'/stubs/controller/rmb_request.stub')
            : File::get(__DIR__.'/stubs/controller/rmb.stub');

            $class    = str_replace('DummyClass', $controller, $stub);
            $model    = str_replace('DummyModelClass', $this->class, $class);
            $modelVar = str_replace('DummyModelVariable', $this->name, $model);

            $final = $modelVar;

            File::put("$dir/$controller.php", $final);
        }
    }

    /**
     * [createController description].
     *
     * @return [type] [description]
     */
    protected function createController()
    {
        $dir = app_path('Http/Controllers');
        $this->checkDirExistence($dir);

        $controller = $this->class.'Controller';
        if (!File::exists("$dir/$controller.php")) {
            $stub  = File::get(__DIR__.'/stubs/controller/plain_request.stub');
            $class = str_replace('DummyClass', $controller, $stub);
            $model = str_replace('DummyModelClass', $this->class, $class);

            $final = $model;

            File::put("$dir/$controller.php", $final);
        }
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

        if (!File::exists("$dir/BaseModel.php")) {
            File::copy(__DIR__.'/stubs/model/BaseModel.php', "$dir/BaseModel.php");
        }

        // create model
        if (!File::exists("$dir/$this->class.php")) {
            $stub  = File::get(__DIR__.'/stubs/model/create.stub');
            $class = str_replace('DummyClass', $this->class, $stub);

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

        if (!str_contains(File::get($dir), str_plural($this->class))) {
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

        if (!File::exists("$dir/$this->class.php")) {
            $stub  = File::get(__DIR__.'/stubs/route/create.stub');
            $name  = str_replace('DummyName', $this->name, $stub);
            $class = str_replace('DummyClass', $this->class, $name);

            File::put("$dir/$this->class.php", $class);
        }

        // add loop to the main routes.php
        $search             = '(File::allFiles(__DIR__.\'/Routes\')';
        $route_file         = app_path('Http/routes.php');
        $route_file_content = File::get(__DIR__.'/stubs/route/web.stub');

        if (!str_contains(File::get($route_file), $search)) {
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
            'index',
            'show',
            'edit',
        ];

        $stub = File::get(__DIR__.'/stubs/view/create.stub');

        foreach ($methods as $one) {
            if (!File::exists("$dir/$one.blade.php")) {
                File::put("$dir/$one.blade.php", $stub);
            }
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
        if (!File::exists($dir)) {
            return File::makeDirectory($dir, 0755, true);
        }
    }

    /**
     * [compDump description].
     *
     * @return [type] [description]
     */
    protected function compDump()
    {
        $comp = new Process('composer dump-autoload');
        $comp->setWorkingDirectory(base_path());
        $comp->run();
    }
}
