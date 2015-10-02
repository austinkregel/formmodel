<?php
namespace Kregel\FormModel;

use Illuminate\Database\Eloquent\Model;

class FormModel
{
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
     * @param Array  $fillable  The desired viewable fields from a model, filter
     *                          using the controller
     * @param String $location  The desired post/put/delete/get url 
     *  
     * @param Array  $relations a list of the possible relations for that model
     * 
     * @param String $method    The POST/GET/DELETE/PUT method.
     *
     * @return String (an HTML form)
     */
    public function modelForm($model, $fillable, $location, $relations, $method = 'GET')
    {
        $bootstrap = config('formmodel.using.bootstrap');
        if (in_array(strtolower($method), ['get', 'post'])) {
            $real_method = $method;
        } else {
            $real_method = 'POST';
        }
        
        $return = '<form class="form-horizontal" action="'.$location.'" method="'.$real_method.'" enctype="multipart/form-data">';
        
        if (config('formmodel.using.csrf')) {
            $return .= $this->input(['type'=>'hidden', 'name'=>'_token', 'value'=>csrf_token()]);
        }
        if (!(in_array(strtolower($method), ['get', 'post']))) {
            $return .= $this->input(['type'=>'hidden', 'name'=>'_method', 'value'=>$method]);
        }
            
        /**
         * This feature is coming soon. It needs more testing.
         */
        if (!empty($relations)) {
            $relations = implode(',', $relations);
            $return .= $this->input(['type' => 'hidden', 'name'=>'_relations', 'value'=>$relations]);
        }
        foreach ($fillable as $input) {
            /**
             * Here we need to do a model check. We need ensure the input
             * or desired attribute exists on the model, if it doesn't exist
             * we will need to loop through the different relations psased through
             */
            if (isset($model->$input)) {
                $return .= $this->modelInput($model, $input);
            } elseif (!empty($relations)) {
                foreach ($relations as $relation) {
                    $old_input = null;
                /**
                 * Here is where the relation magic happens. We need to see if, 
                 * ex. user_id exists. if it does it will replace user_ with
                 * nothing so you'll be left with just id so then it will
                 * get the information for that model's relation.
                 * 
                 * So the query would actually look like
                 * (going from the above example)
                 * $model->user->id
                 */
                if (stripos($input, $relation)!== false) {
                    $old_input = $input;
                    $input = str_replace($relation.'_', '', $input);
                }
                /**
                 * Here we need to build the model's input field since there is
                 * a relation on the base mode. We also need to grep the old 
                 * input field and any old kind of data.
                 */
                if (isset($model->$rel->$input)) {
                    $return .= $this->modelInput($model->$rel, $input, $old_input);
                }
                }
            } else {
                $return .= $this->modelInput($model, $input);
            }
        }


        return $return. $this->submit();
    }

    /**
     * # N/A request
     * This will allow you to get the proper input type for an HTML form.
     * It will extract the names from the.
     *
     * @param String $input
     * @param Boolean $boottrasp
     * @param Model $model
     * @param Boolean $edit
     *
     * @return String (an HTML input element)
     */
    private function modelInput(Model $model, $input, $old_input = null)
    {
        $return = '';
        $old_input =!empty($old_input)?$old_input:$input;
        if (stripos($input, 'id') !== false |
            stripos($input, '_id') !== false) {
            if ($edit === false) {
                return '<!-- There is a relation that requires the key '.htmlentities($input).', assuming that it will be handled later -->';
            } else {
                $type = 'text';
            }
        } elseif (
            (stripos($input, 'number') !== false &
              (
               stripos($input, 'home_') === false &
                stripos($input, 'fax_') === false &
                stripos($input, 'recorder_') === false &
                stripos($input, 'direct_') === false &
                stripos($input, 'cell_') === false &
                stripos($input, 'model') === false
              )
            ) |
            (stripos($input, 'count') !== false &
            stripos($input, 'county') === false) |
            stripos($input, 'percent') !== false) {
            $type = 'number';
        } elseif (stripos($input, 'date') !== false | stripos($input, '_date') !== false | stripos($input, 'start') !== false | stripos($input, 'finish') !== false) {
            $type = 'date';
        // Assume that the desired result is a boolean.
        } elseif (stripos($input, 'is_') !== false) {
            $type = 'select';
        // Assume that the desired result is a passwordc.
        } elseif (stripos($input, 'password') !== false) {
            $type = 'password';
            
        // Checks to make sure that it's not an email_list, list_email, some_email_list, or someemaillist field
        } elseif (stripos($input, 'email') !== false & stripos($input, 'list') === false) {
            $type = 'email';
        } elseif (stripos($input, 'path') !== false) {
            $type = 'file';
        } else {
            $type = 'text';
        }
        
        if (config('formmodel.using.bootstrap')) {
            if ($type === 'select') {
                $return .= $this->bootstrapBoolInput([
                  'type' => $type,
                  'class' => 'form-control',
                  'name' => $old_input
                ]);
            } else {
                $return .= $this->bootstrapInput([
                'type' => $type,
                'id' => $input,
                'class' => 'form-control',
                'name' => $old_input,
                'placeholder' => $this->inputToRead($old_input),
                'label' => $this->inputToRead($old_input),
                'value' => (!empty($model->$input) && !(stripos($input, 'password') !== false))?$model->$input:''
              ]);
            }
        } else { //not bootstrap
          if ($type === 'select') {
              $return .= $this->boolInput([
                'type' => $type,
                'class' => 'form-control',
                'name' => $old_input
              ]);
              ;
          } else {
              $return .= $this->input([
              'type' => $type,
              'id' => $input,
              'name' => $old_input,
              'placeholder' => $this->inputToRead($old_input),
              'label' => $this->inputToRead($old_input),
              'value' => !empty($model->$input)?$model->$input:''
            ]);
          }
        }
        return $return;
    }
    
    /**
     * @param Array  $options Should contain type, and name
     * 
     * @return String html submit input
     */
    public function input($options = [])
    {
        $label = !empty($options['label'])?'<label>'.$options['label']:'';
        return $label.'<input'.$this->attributes($options). '>'.(!empty($label)?'</label>':'');
    }
  
   /**
   * @param Array  $options Should contain type, and name
   * 
   * @return String html submit input
   */
    public function boolSelect($options = [])
    {
        $label = !empty($options['label'])?'<label>'.$this->inputToRead($options['label']):'';
        return $label.'<select'.$this->attributes.'><option value="0">No</option><option value="1">Yes</option></select>'.!empty($label)?'</label>':'';
    }
    /**
     * It's not pretty, but it works, will clean up later.
     * @param Array   $options Should contain type, label, and name
     * 
     * @return String html submit input
     */
    public function bootstrapInput($options = [])
    {
        $label = !empty($options['label'])?
              '<label class="col-md-12 ">
                <div class="col-md-3 text-right control-label">'. $this->inputToRead($options['label']).'</div>':'';
                
        return '<div class="form-group">'.
                (!empty($label)?($label):'').
                '<div class="col-md-'.(!empty($label)?'9':'12').'">
                  <input'.$this->attributes($options). '>
                </div>'.
                (!empty($label)?'</label>':'')
              .'</div>';
    }

    /**
     * It's not pretty, but it works, will clean up later.
     * @param Array   $options Should contain type, label, and name
     * 
     * @return String html submit input
     */

      public function bootstrapBoolSelect($options = [])
      {
          $label = !empty($options['label'])?'<label class="col-md-12"><div class="col-md-3">'.$this->inputToRead($options['label']).'</div>':'';
          return '<div class="form-group">'.
                (!empty($label)?($label):'').
                '<div class="col-md-9">
                  <select'.$this->attributes.'>
                    <option value="0">No</option>
                    <option value="1">Yes</option>
                  </select>'.(!empty($label)?
                '</div></label>':'').
              '</div>';
      }
    
    /**
     * 
     * @param String $value
     * @param Array  $options
     * 
     * @return String html submit input
     */
    public function submit()
    {
        if (config('formmodel.using.bootstrap')) {
            return $this->bootstrapInput(['type'=> 'submit', 'class' => 'btn btn-primary pull-right', 'value' => 'Submit']);
        } else {
            return $this->bootstrapInput(['type'=> 'submit', 'value' => 'Submit']);
        }
    }
    
    /**
     * This function builds attributes for html elements
     * ex. 
     *      id="name"
     * 
     * @param  Array  $attr A key value pair of attributes
     *                      for an HTML Element
     * 
     * @return String $attr_string 
     */
    private function attributes(Array $attr)
    {
        $attr_string = '';
        foreach ($attr as $name => $value) {
            if (is_array($value)) {
                $attr_string .= ' '.$name.'="'.implode(' ', $value).'"';
            } else {
                $attr_string .= ' '.$name.'="'.$value.'"';
            }
        }
        
        return $attr_string;
    }
    /**
     * # N/A request
     * Eventually will be used to convert common shortnames or 
     * common coding errors to common english.
     *
     * @param String $input
     *
     * @return String
     */
    private function inputToRead($input)
    {
        if ($input === 'path') {
            $input = 'file';
        } elseif ($input === 'desc') {
            $input = 'description';
        }

        return ucwords(preg_replace('/[-_]/', ' ', $input));
    }
}
