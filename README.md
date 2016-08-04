# Laravel-Cmnds
Some Helper Console Cmnds For Laravel To Speed The Workflow

## v5.2
1- put the files into `App/Console/Commands`

2- add the below to `App/Console/Kernel.php`

```php
protected $commands = [
    Commands\ClearAll::class,
    Commands\MakeAll::class,
    Commands\FineTune::class,
];
```

3- from the root of your project run `composer dump-autoload`

### Usage
now you have 3 new cmnds.

```shell
ex
    ex:clear:all        Clear cache/config/route/view/compiled/pass-resets
    ex:fine:tune        optimize & cache route/config
    ex:make:all         Make (Controller,Model,Migration,Seeder,Route,View)
```
**none of theme require any interaction except the `ex:make:all` which will ask you for the class name.**

1- ex:clear:all
>  - php artisan clear-compiled
>  - php artisan cache:clear
>  - php artisan config:clear
>  - php artisan route:clear
>  - php artisan view:clear
>  - php artisan auth:clear-resets  `// if the table was migrated`

2- ex:fine:tune
>  - php artisan optimize
>  - php artisan route:cache
>  - php artisan config:cache

3- ex:make:all
>  - php artisan make:controller --resource
>  - php artisan make:model -m
>  - php artisan make:seeder [y/N]
>
> - Rotues [y/N]
>  - creates a new folder `App/Http/Routes` & add new route files equal to the class name
>  - add a loop with req_once in `App/Http/routes.php` to include all the files from the `App/Http/Routes` folder
> - Views [y/N]
>  - create a new folder in `Resources/Views` equal to the class name + a generic file `index.blade.php`

## v5.3
- soon.
