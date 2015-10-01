<?php namespace Kregel\FormModel;

use Illuminate\Support\ServiceProvider;

class FormModelServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		//
		$this->app->bind('formmodel', function(){
		  return new Kregel\FormModel\Facades\FormModel;
		});
    $this->app->alias('FormModel', 'FormModel');

	}

	/**
   * Bootstrap any application services.
   *
   * @return void
   */
  public function boot()
  {
    //
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
