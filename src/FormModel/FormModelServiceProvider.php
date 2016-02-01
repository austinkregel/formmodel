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
    }

  /**
   * Bootstrap any application services.
   */
  public function boot()
  {
      $this->loadViewsFrom(__DIR__.'/../resources/views', 'formmodel');
      $this->publishes([
          __DIR__.'/../resources/views' => base_path('resources/views/vendor/formmodel'),
      ], 'views');
      $this->publishes([
          __DIR__.'/../config/config.php' => config_path('kregel/formmodel.php'),
      ], 'config');
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
