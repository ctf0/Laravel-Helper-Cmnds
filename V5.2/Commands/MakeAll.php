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
        $this->class = Str::title($this->ask('What is the Class name ex.abc'));

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
                'name' => Str::plural($this->class).'TableSeerder',
            ]);
        }

        if ($this->confirm('Do you wish to add a Route? [y|N]')) {
            $this->name = Str::lower($this->class);

            // create routes
            $this->createRoute();

            if ($this->confirm('Do you wish to include Views? [y|N]')) {
                // create a views
                $this->createView();
            }
        }

        if ($this->confirm('Do you wish to include Validation? [y|N]')) {
            $this->createValidation();
        }

        $this->info('All Done');
    }

    /**
     * [createRoute description].
     *
     * @return [type] [description]
     */
    protected function createRoute()
    {
        $dir     = app_path('Http/Routes');
        $content =
<<<EOT
<?php

Route::group(['prefix' => '$this->name'], function () {
    // routes here
});

// or
// Route::resource('$this->name', '{$this->class}Controller');
EOT;

        if ( ! File::exists($dir)) {
            File::makeDirectory($dir);
        }
        if ( ! File::exists("$dir/$this->class.php")) {
            File::put("$dir/$this->class.php", $content);
        }

        // add loop to the main routes.php
        $route_file         = app_path('Http/routes.php');
        $route_file_content =
<<<EOT

foreach (File::allFiles(__DIR__.'/Routes') as \$route_file) {
    require_once \$route_file->getPathname();
}
EOT;

        if ( ! strpos(File::get($route_file), $route_file_content)) {
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

        // create view files
        $methods = [
            'index',
            'create',
            'show',
            'edit',
        ];

        foreach ($methods as $one) {
            if ( ! File::exists("$dir/$one.blade.php")) {
                File::put("$dir/$one.blade.php", null);
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
        $dir     = app_path("Http/Validations/$this->class");
        $content =
<<<EOT
<?php

namespace App\Http\Validations\\$this->class;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class StoreValidation
{
    public function validate(\$request)
    {
        \$rules = [
            // ...
        ];

        \$validator = Validator::make(\$request->all(), \$rules);

        if (\$validator->fails()) {
            throw new ValidationException(
                \$validate,
                \$this->buildFailedValidationResponse(\$request, \$validator->errors()->getMessages())
            );
        }
    }

    protected function buildFailedValidationResponse(\$request, array \$errors)
    {
        if ((\$request->ajax() && ! \$request->pjax()) || \$request->wantsJson()) {
            return new JsonResponse(\$errors, 422);
        }

        return redirect()->back()
                        ->withInput(\$request->input())
                        ->withErrors(\$errors, 'default');
    }
}
EOT;

        if ( ! File::exists($dir)) {
            File::makeDirectory($dir, 0755, true);
        }
        if ( ! File::exists("$dir/StoreValidation.php")) {
            File::put("$dir/StoreValidation.php", $content);
        }
    }
}
