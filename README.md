This Is Mostly For Personal Use, If You Like What U See :thumbsup: Go Ahead And Give It A Try :heart_eyes:

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
    ex:fine:tune        optimize & cache config
    ex:make:all         Make (Controller,Model,Migration,Seeder,Route,View,Validation)
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
>  - php artisan config:cache

3- ex:make:all
>  - php artisan make:controller --resource
>  - php artisan make:model -m
>  - php artisan make:seeder [y/N]
>
> - Rotues [y/N]
>  - creates a new folder `App/Http/Routes` & add new route file equal to the class name
>  - append a loop to `App/Http/routes.php` to include all the files from the `App/Http/Routes` folder
>
> - Views [y/N]
>  - create a new folder in `Resources/Views` equal to the class name + files for **'index/create/show/edit'**
>
> - Validation [y/N]
>  - create a new folder `App/Http/Validations` equal to the class name + file for **'StoreValidation'** [Read More](https://gist.github.com/ctf0/bb137c135b6d9383184d4deec0b24d56)
>  - for authorization [Read More](https://gist.github.com/ctf0/5cde91273c33ade6da6e2a0c8b7f47bf)

## v5.3
- soon.


## ToDo

* [ ] Find away to implement `route:cache` as currently it gives error.
* [ ] Prepopulate the `ModelTableSeerder::run()` with `Model::create()`.
* [ ] Find away to automatically register the **'ModelTableSeerder'** into the `DatabaseSeeder::run()`.
* [ ] Make `Models Folder` and add **BaseModel** while make others extend it.
* [ ] Turn into Package.
