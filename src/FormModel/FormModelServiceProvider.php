<?php

namespace Kregel\FormModel;

use Illuminate\Support\ServiceProvider;

class FormModelServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Register the service provider.
     */
    public function register() 
    {
        //
        $this->app->bind('formmodel', function () {
          return new FormModel;
        });
        $this->app->alias('formmodel', Facades\FormModel::class);
    }

  /**
   * Bootstrap any application services.
   */
  public function boot()
  {
      $this->publishes([
          __DIR__.'/config/config.php' => config_path('kregel/formmodel.php'),
      ]);
  }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }
}
