# What is this package?
This package was created to help decrease the time it takes to echo out a form relating to a given [Model](http://laravel.com/docs/master/eloquent) while still giving the developer the ultimate amount of flexibility . 

# What do I need to do to make it work?
To get it to work properly, similar to how it works in my Warden package(comming soon :) ), it's recommended to do the following
 
  1.  composer require kregel/formmodel
      or add `"kregel/formmodel":"1.*"` to your composer.json file, just be sure to use `composer update` with that statement, or if you haven't build your dependancies use `composer install` instead.
      
      
  2.  Register the service provider with your `config/app.php` file
  
  ```php
  'providers' => [
    ...,
    Kregel\FormModel\FormModelServiceProvider::class,
    ...,
  ]
  ```
  3.  (optional) Add the alias to your `config/app.php` file
  
  ```php
  'aliases' => [
    ...,
    'FormModel' => Kregel\FormModel\Facades\FormModel,
    ...,
  ]
  ```
  4.  Publish the config file! This should be able to be done with `php artisan vendor:publish`
  5.  Use your favorite way to new up a FormModel, this can be done using the Facade or by just doing 
  
  ```php 
  $form = new Kregel\FormModel\FormModel
  ```
  6.  To actually get the desired output of the form you'll need to call the `modelForm` method the parameters it needs are listed
    *  Any class that extends Model (so basically your desired model)
    *  The fields you want to have filled or shown to the end user. I use the code below to resolve the desired fields from my User model.
    
    ```php
    $field_names = !empty($user->getVisible()) ? 
                          $user->getVisible() : 
                          $user->getFillable();
    ```
    *  The route you want it to go to, it's assumed that you want to have a custom route for posting, putting, deleting or getting the information. 
    *  This is the method type you want to use. (POST, PUT, DELETE, or GET)
  7.  Print the results!
  
# Do you have an example?
Duhh! Let it be known that this is a method in one of my controllers.
```php
public function getUser($id, FormModel $form){
    $user = User::find($id);

    $field_names = !empty($user->getVisible()) ? 
                      $user->getVisible() : 
                       $user->getFillable();
                       
    $form_info = $form->modelForm($user, $field_names, '/user/manage', [], 'PUT');
    
    return view('view-user')
            ->with('form', $form_info);
}
```

# Questions?
Email me (my email is on [my github page](http://github.com/austinkregel)), or you can drop an issue. :)
