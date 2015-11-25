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
                $custom_thing = config('kregel.formmodel.using.framework');
                return $custom_thing();

        }
    }
//FormModel::using(bootstrap-vue)->withModel($user)->submitTo($location)->form();
//$form->using('bootstrap-vue')->withModel($model)->submitTo($location)->form();
    /**
     * This is the main baby for FormModel. This is the quickest way to
     * make new forms for models for creation or for editing/updating.
     * It will use and extract the fillale or the visible properties from
     * Eloquent models. It will always prefer things in the visible attribute
     * This is because there might be an attribute from the fillable attribute
     * that you might not want to allow the end user to see.
     *
     * ex. Some kind of relation, I often use the User->id realtion and I often
     * want to hide the User->id relation and just use the Auth::user()->id
     * When the form is posted.
     *
     * @param Model $model Instance of Illuminate\Database\Eloquent\Model
     * @param Array $fillable The desired viewable fields from a model, filter
     *                          using the controller
     * @param String $location The desired post/put/delete/get url
     * @param Array $relations a list of the possible relations for that model
     * @param String $method The POST/GET/DELETE/PUT method.
     *
     * @return String (an HTML form)
     */
    public function modelForm($model, $fillable, $location, $relations, $method = 'GET')
    {
        $return = '';
        if (in_array(strtolower($method), ['get', 'post'])) {
            $real_method = $method;
        } else {
            $real_method = 'POST';
        }

        $return .= '<div id="vue-form-wrapper"><div id ="response" v-show="response">{{ response }}<div class="close" @click="close">&times;</div></div>';

        $return = $return . $this->submit() . '</div>' .
            view('formmodel::vue')->with('components', $this->vue_components)->with('type', $method);
        return $return;
    }


}
