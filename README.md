#### Config file

```php
    /*
     * @param String
     * @return Collection
     */
    'resolved_relationship' => function ($class_name) {
        return $class_name::limit(15)->get();
    },
```

What this code does, is,  if there is a relationship, but it will return the resolved relationship function's evaluation. So if you want to scope this down some, then be my guest.

#### Custom frameworks.
The next step is to change the way your framework interacts with the modelInput method. There is a new "base" framework impementation, which draws from something only some classes had which is the automatic relationship resolution. If you extend the Plain framework then you will be able to use this feature by default. Other wise you can keep doing what you're doing :)

If you need custom attributes on your form inputs like `v-models` (for [vue](https://vuejs.com)) or some other javascript framework or you want to add custom classes, It's recommended that your framework only contain methods that relate to the inputs for that framework, and to not have them override the default `modelInputs` method

## Oh crap! an update broke my App!! WHAT DO I DOOO!? FIX IT NOW!!

Please, before you raise a lynch mob on Twitter, use your brain and the wonderful human powers of deductive reasoning.

So, as of 2.0, there was a huge structure change with... Well, everything. I made the whole system a bit more modular
and extensible. So instead of needing to create a new instance of your model AND FormModel, now you just need to new up
a FormModel instance. Or... If you have our facade set up, you can use the facade.


[![downloads](https://img.shields.io/packagist/dt/kregel/formmodel.svg)](https://packagist.org/packages/kregel/formmodel)

# What is this package?
This package was created to help decrease the time it takes to echo out a form relating to a given [Model](http://laravel.com/docs/master/eloquent) while still giving the developer the ultimate amount of flexibility . 

# What do I need to do to make it work?
To get it to work properly, similar to how it works in my [Warden package](https://github.com/austinkregel/warden), it's recommended to do the following
 
  1.  composer require kregel/formmodel
      or add `"kregel/formmodel":"^2.0"` to your composer.json file, just be sure to use `composer update` with that statement, or if you haven't build your dependancies use `composer install` instead.
      
      
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
    'FormModel' => Kregel\FormModel\Facades\FormModel::class,
    ...,
  ]
  ```
  4.  Publish the config file! This should be able to be done with `php artisan vendor:publish`
  5.  Use your favorite way to new up a FormModel, this can be done using the Facade or by just doing 
  
  ```php 
  $form = new Kregel\FormModel\FormModel;
  ```
  6.  
     Use something similar to the following in your controller, or in your view (maybe you injected it?)
 ```php
  $form->using(config('kregel.formmodel.using.framework'))
          ->withModel($model)
          ->submitTo(route('warden::api.create-model'))
          ->form([
              'method' => 'post',
              'enctype' =>'multipart/form-data'
          ]);
  ```

  7.  Print the results!
  
# Do you have an example?
Duhh! Let it be known that this is a method in one of the controllers from my [Warden package](https://github.com/austinkregel/warden).

```php
protected function getNewModel($model_name, FormModel $form)
{
    /*
     * We need to grab the model from the config and select one entry for
     * that model from within the database.
     */
    $model = $this->findModel($model_name);

    /*
     * Here we generate the form to update the model using the kregel/formmodel
     * package
     */
    $form_info = $form->using(config('kregel.formmodel.using.framework'))
                        ->withModel($model)
                        ->submitTo(route('warden::api.create-model'))
                        ->form([
                            'method' => 'post',
                            'enctype' =>'multipart/form-data'
                        ]);

    return view('warden::view-model')
            ->with('form', $form_info)
            ->with('model_name', $model_name);
}
```
### Extending

You can extend this system just like you would with [my Menu Package](https://github.com/austinkregel/Menu). In the section in the config 
labeled 'custom-framework'. Modify the Namespacing of the newed up object to your class and it should just work (assuming you knew 
to use the structure of the class below)

```php
<?php

namespace App\FormModel\Frameworks;

use Kregel\FormModel\Interfaces\FrameworkInputs;
use Kregel\FormModel\Interfaces\FrameworkInterface;

class MyFramework extends FrameworkInputs implements FrameworkInterface
{
    // The only method that you NEED is the form function
    public function form(Array $options = []){
        // Do Stuff (build the form)
        
        $method = empty($options['method']) ? $options['method'] : '';
        if (in_array(strtolower($method), ['get', 'post'])) {
            $real_method = $method;
        } else {
            $real_method = 'POST';
        }
        $options['method'] = $real_method;
        $options['action'] = $this->location;
        return '<form ' . $this->attributes($options) . '>' .
                // Pass the method through so the form knows how to handle it's self (with laravel)
                $this->method($method) .
                // Check and fill the csrf token if it's configured for it.
                $this->csrf() .
                $this->buildForm() .
                $this->submit([]) .
            '</form>';
    }
}
```

# Questions?
Email me (my email is on [my github page](http://github.com/austinkregel)), or you can drop an issue. :)

