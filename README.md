## Installation

- `composer require ctf0/helper-cmnds`

- add the service provider to `config/app.php`
```php
'providers' => [
    ctf0\LaravelHelperCmnds\LaravelHelperCmndsServiceProvider::class
]
```

## Usage

```bash
ex:clear       # Clear (Cache/Config/Route-Cache/View/Session/Compiled/Laravel-LogFile/Pass-Resets)
ex:finetune    # optimize & cache config
ex:make        # Make (Controller/Model/Migration/Seeder/Route/View/Validation)
ex:remigrate   # migrate:refresh + seed & cache clear
```

1- ex:clear
>  - php artisan clear-compiled
>  - php artisan cache:clear
>  - php artisan config:clear
>  - php artisan route:clear
>  - php artisan view:clear
>  - Session::flush()
>  - File::put(storage_path('logs/laravel.log'), '');
>  - php artisan auth:clear-resets `if the table was migrated`
>  - composer dump-autoload

* an event gets fired when this command has finished in case you want to run something else after it, and you can hook into it through:
    ```php
    // app/Providers/EventServiceProvider.php
    public function boot()
    {
        parent::boot();

        Event::listen('clearAll.done', function () {
            // any other cmnds you want to run
        });
    }
    ```

2- ex:finetune
>  - composer dump-autoload
>  - php artisan optimize
>  - php artisan config:cache
>  - php artisan route:cache

3- ex:make (for a two word className ex. `SubPage`, write it as `sub_page`)
> - Validation [y/N] [Read More](https://ctf0.wordpress.com/2016/10/16/extend-formrequest-to-allow-more-functionality-in-laravel-v5-3/).
    - create 2 classes for **Update & Store** `php artisan make:request {name}`
    - register the **FormRequest** classes to the controller automatically
>
> - Controller
    - if "Route Model Binding" we will add the **Model** class to the controller automatically
    - php artisan make:controller --resource
>
> - Model & Migration
    - create `App/Http/Models/BaseModel.php` if not found
    - create `App/Http/Models/ClassName.php`
    - `php artisan make:migration {name} --create`
>
> - Seeder [y/N]
    - php artisan make:seeder
    - create a seeder file & register it under `DatabaseSeeder::run()`.
>
> - Routes [y/N] [Also Check](http://code4fun.io/post/how-to-share-data-with-all-views-in-laravel-5-3-the-right-way)
    - creates a new folder in `routes/WebRoutes/ClassName.php`.
    - append a loop to `web.php` to include all the files from the `routes/WebRoutes` folder.
>
> - Views [y/N]
    - create a new folder in `resources/views/pages/ClassName/` + files for **'index/create/show/edit'**.
>
> - composer dump-autoload

4- ex:remigrate
>  - php artisan ex:clear
>  - php artisan migrate:refresh --seed
