<?php

namespace Kregel\FormModel;

use Illuminate\Database\Eloquent\Model;

class FormModel
{
    /**
     * @var
     */
    private $vue_components;

    public function using($string){

        switch($string){
            case 'bootstrap':
                return new Frameworks\Bootstrap;
            case 'materialize':
                return new Frameworks\Materialize;
            case 'bootstrap-vue':
                return new Frameworks\BootstrapVue;
            case 'materialize-vue':
                return new Frameworks\MaterializeVue;
            default:
                $custom_thing = config('kregel.formmodel.using.custom-framework');
                return call_user_func($custom_thing);

        }
    }
//FormModel::using(bootstrap-vue)->withModel($user)->submitTo($location)->form();
//$form->using('bootstrap-vue')->withModel($model)->submitTo($location)->form();
}
