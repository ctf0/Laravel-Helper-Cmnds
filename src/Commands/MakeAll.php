<?php

namespace ctf0\LaravelHelperCmnds\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class MakeAll extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ex:make';

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
        $this->class = studly_case($this->ask('What is the Class name ex.abc'));
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
            if ($this->validation) {
                $this->createController();
            } else {
                $this->callSilent('make:controller', [
                    'name'       => $this->class . 'Controller',
                    '--resource' => true,
                ]);
            }
        }

        // create migration
        if ($this->confirm('Do you wish to create a Migration File ?')) {
            $table = str_plural(snake_case(class_basename($this->class)));
            $this->callSilent('make:migration', [
                'name'     => "create_{$table}_table",
                '--create' => $table,
            ]);
        }

        // create model
        $this->createModel();

        // create a seeder
        if ($this->confirm('Do you wish to create & register a DB Seeder ?')) {
            $this->callSilent('make:seeder', [
                'name' => str_plural($this->class) . 'TableSeeder',
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
        shell_exec('composer dump-autoload');

        $this->info('All Done');
    }

    /**
     * [createRequest description].
     *
     * @param [type] $answer [description]
     *
     * @return [type] [description]
     */
    protected function createRequest()
    {
        $dir = app_path('Http/Requests');
        $this->checkDirExistence($dir);

        $methods = [
            'Update',
            'Store',
        ];

        foreach ($methods as $one) {
            $fileName = $this->class . $one . 'Request';
            if (!app('files')->exists("$dir/$fileName.php")) {
                $stub  = app('files')->get(__DIR__ . '/stubs/request/create.stub');
                $class = str_replace('DummyClass', $fileName, $stub);

                app('files')->put("$dir/$fileName.php", $class);
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

        $controller = $this->class . 'Controller';
        if (!app('files')->exists("$dir/$controller.php")) {
            $stub = $this->validation
            ? app('files')->get(__DIR__ . '/stubs/controller/rmb_request.stub')
            : app('files')->get(__DIR__ . '/stubs/controller/rmb.stub');

            $class = str_replace('DummyClass', $controller, $stub);
            $model = str_replace('DummyModelClass', $this->class, $class);
            $view  = str_replace('DummyView', $this->name, $model);

            app('files')->put("$dir/$controller.php", $view);
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

        $controller = $this->class . 'Controller';
        if (!app('files')->exists("$dir/$controller.php")) {
            $stub  = app('files')->get(__DIR__ . '/stubs/controller/plain_request.stub');
            $class = str_replace('DummyClass', $controller, $stub);
            $model = str_replace('DummyModelClass', $this->class, $class);
            $view  = str_replace('DummyView', $this->name, $model);

            $final = $view;

            app('files')->put("$dir/$controller.php", $final);
        }
    }

    /**
     * [createModel description].
     *
     * @return [type] [description]
     */
    protected function createModel()
    {
        $dir = app_path('Models');
        $this->checkDirExistence($dir);

        if (!app('files')->exists("$dir/BaseModel.php")) {
            app('files')->copy(__DIR__ . '/stubs/model/BaseModel.php', "$dir/BaseModel.php");
        }

        // create model
        if (!app('files')->exists("$dir/$this->class.php")) {
            $stub  = app('files')->get(__DIR__ . '/stubs/model/create.stub');
            $class = str_replace('DummyClass', $this->class, $stub);

            app('files')->put("$dir/$this->class.php", $class);
        }
    }

    /**
     * [registerSeederFile description].
     *
     * @return [type] [description]
     */
    protected function registerSeederFile()
    {
        $stub = app('files')->get(__DIR__ . '/stubs/db/seeder.stub');
        $seed = str_replace('DummySeed', str_plural($this->class), $stub);
        $dir  = database_path('seeds/DatabaseSeeder.php');

        if (!str_contains(app('files')->get($dir), str_plural($this->class))) {
            $file = file($dir);
            for ($i = 0; $i < count($file); ++$i) {
                if (strstr($file[$i], '$this->')) {
                    $file[$i] = $file[$i] . $seed;
                    break;
                }
            }

            return app('files')->put($dir, $file);
        }
    }

    /**
     * [createRoute description].
     *
     * @return [type] [description]
     */
    protected function createRoute()
    {
        $dir = base_path('routes/WebRoutes');
        $this->checkDirExistence($dir);

        if (!app('files')->exists("$dir/$this->class.php")) {
            $stub  = app('files')->get(__DIR__ . '/stubs/route/create.stub');
            $name  = str_replace('DummyName', $this->name, $stub);
            $class = str_replace('DummyClass', $this->class, $name);

            app('files')->put("$dir/$this->class.php", $class);
        }

        // add loop to the main routes.php
        $search             = '(app(\'files\')->allFiles(__DIR__.\'/WebRoutes\')';
        $route_file         = base_path('routes/web.php');
        $route_file_content = app('files')->get(__DIR__ . '/stubs/route/web.stub');

        if (!str_contains(app('files')->get($route_file), $search)) {
            app('files')->append($route_file, $route_file_content);
        }
    }

    /**
     * [createView description].
     *
     * @return [type] [description]
     */
    protected function createView()
    {
        $dir = resource_path("views/pages/$this->name");
        $this->checkDirExistence($dir);

        $methods = [
            'index',
            'create',
            'show',
            'edit',
        ];

        $stub = app('files')->get(__DIR__ . '/stubs/view/create.stub');

        foreach ($methods as $one) {
            if (!app('files')->exists("$dir/$one.blade.php")) {
                app('files')->put("$dir/$one.blade.php", $stub);
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
        if (!app('files')->exists($dir)) {
            return app('files')->makeDirectory($dir, 0755, true);
        }
    }
}
