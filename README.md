## Installation

- `composer require ctf0/laravel-helper-cmnds`

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

[Wiki](https://github.com/ctf0/Laravel-Helper-Cmnds/wiki/Usage)
