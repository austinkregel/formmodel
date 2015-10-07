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
          return new Kregel\FormModel\Facades\FormModel();
        });
        $this->app->alias('FormModel', 'Kregel\FormModel\FormModel');
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
