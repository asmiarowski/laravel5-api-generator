# laravel5-api-generator
Generates boilerplate for laravel REST API: migration, controller, model, request and route.

In app\Providers\AppServiceProvider@boot
```
if ($this->app->environment() == 'local') {
  $this->app->register('Smiarowski\Generators\GeneratorsServiceProvider');
}
```
