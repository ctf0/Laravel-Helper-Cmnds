### #Installation
1- copy the `Commands` folder to `App/Console`

2- add the below to `App/Console/Kernel.php`

```php
protected $commands = [
    Commands\ClearAll::class,
    Commands\MakeAll::class,
    Commands\FineTune::class,
    Commands\ReMigrate::class,
];
```

3- from the root of your project run `composer dump-autoload`

### #Usage
now you have 4 new cmnds.

```bash
ex:clear:all    # Clear (Cache/Config/Route-Cache/View/Session/Compiled/Laravel-LogFile/Pass-Resets)
ex:fine:tune    # optimize & cache config
ex:make:all     # Make (Controller/Model/Migration/Seeder/Route/View/Validation)
ex:re:migrate   # migrate:refresh + seed & cache clear
```
> none of the cmnds require any interaction except the `ex:make:all` which will ask you for the class name.

1- ex:clear:all
>  - php artisan clear-compiled
>  - php artisan cache:clear
>  - php artisan config:clear
>  - php artisan route:clear
>  - php artisan view:clear
>  - Session:flush()
>  - File::put(storage_path('logs/laravel.log'), '');
>  - php artisan auth:clear-resets `if the table was migrated`

2- ex:fine:tune
>  - php artisan optimize
>  - php artisan config:cache
>  - composer dump-autoload

3- ex:make:all
> - Validation [y/N] [Read More](https://ctf0.wordpress.com/2016/10/17/extend-formrequest-to-allow-more-functionality-in-laravel-v5-2/)
    - create `App/Http/Requests/Request.php` if not found
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
    - create seeder file & register it under `DatabaseSeeder::run()`.
>
> - Rotues [y/N] [Also Check](http://code4fun.io/post/how-to-share-data-with-all-views-in-laravel-5-3-the-right-way)
    - create `App/Http/Routes/ClassName.php`.
    - append a loop to `App/Http/routes.php` to include all the files from the `App/Http/Routes` folder.
>
> - Views [y/N]
    - create a new folder in `Resources/Views/ClassName/` + files for **'index/create/show/edit'**.
>
> - composer dump-autoload

4- ex:re:migrate
>  - php artisan cache:clear
>  - php artisan migrate:refresh --seed
