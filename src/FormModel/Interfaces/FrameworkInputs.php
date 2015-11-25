<?php
namespace Kregel\FormModel\Interfaces;

use Illuminate\Database\Eloquent\Model;

abstract class FrameworkInputs
{
    /**
     * @var
     */
    protected $model;
    /**
     * @var
     */
    protected $location;

    public function plainTextarea($options, $text = '')
    {
        return '<textarea' . $this->attributes($options) . '>' .
            $text . '</textarea>';
    }

    /**
     * This function builds attributes for html elements
     * ex.
     *      id="name".
     *
     * @param  Array $attr A key value pair of attributes
     *                      for an HTML Element
     *
     * @return String $attr_string
     */
    private function attributes(Array $attr)
    {
        $attr_string = '';
        foreach ($attr as $name => $value) {
            if (is_array($value)) {
                $attr_string .= ' ' . $name . '="' . implode(' ', $value) . '"';
            } else {
                $attr_string .= ' ' . $name . '="' . $value . '"';
            }
        }

        return $attr_string;
    }

    public function plainSelect($configs, $options)
    {
        return '<select' . $this->attributes($configs) . '>
                 <option value="" disabled selected>Please select one</option>
                 ' . $this->buildOptions($options) . '
             </select>';
    }

    public function buildOptions($options)
    {
        $return = '';
        foreach ($options as $value => $text) {
            $return .= '<option ' . $this->attributes(['value' => $value]) . '>' . $text . '</option>';
        }
        return $return;
    }

    public function plainSubmit(Array $options = [])
    {
        return $this->input(array_merge(['type' => 'submit'], $options));
    }

    /**
     * @param Array $options Should contain type, and name
     *
     * @return String html submit input
     */
    public function plainInput($options = [])
    {
        return '<input' . $this->attributes($options) . '>';
    }

    public function form(Array $options = [])
    {

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
            $this->buildForm()
        . '</form>';

    }

    public function method($method)
    {
        if (!(in_array(strtolower($method), ['get', 'post']))) {
            return $this->input(['type' => 'hidden', 'name' => '_method', 'value' => $method]);
        }
        return '';
    }

    public function csrf()
    {
        if (config('kregel.formmodel.using.csrf')) {
            return $this->input(['type' => 'hidden', 'name' => '_token', 'value' => csrf_token()]);
        }
        return '';
    }

    public function buildForm()
    {
        $return = '';
        $fillable = $this->getFillable($this->model);
        foreach ($fillable as $input) {
            /*
             * Here we need to do a model check. We need ensure the input
             * or desired attribute exists on the model, if it doesn't exist
             * we will need to loop through the different relations psased through
             */
            if (isset($this->model->$input)) {
                $return .= $this->modelInput($this->model, $input);
            } elseif (!empty($relations)) {
                foreach ($relations as $relation) {
                    $old_input = null;
                    /*
                     * Here is where the relation magic happens. We need to see if,
                     * ex. user_id exists. if it does it will replace user_ with
                     * nothing so you'll be left with just id so then it will
                     * get the information for that model's relation.
                     *
                     * So the query would actually look like
                     * (going from the above example)
                     * $model->user->id
                     */
                    if (stripos($input, $relation) !== false) {
                        $old_input = $input;
                        $input = str_replace($relation . '_', '', $input);
                    }
                    /*
                    * Here we need to build the model's input field since there is
                    * a relation on the base mode. We also need to grab the old
                    * input field and any old kind of data.
                    */
                    if (isset($this->model->$relation->$input)) {
                        $return .= $this->modelInput($this->model->$relation, $input, $old_input);
                    }
                }
            } else {
                $return .= $this->modelInput($this->model, $input);
            }
        }
        return $return;
    }

    public function getVisible()
    {
        return empty($this->model->getVisible()) ? $this->model->getFillable() : $this->model->getVisible();
    }

    public function getFillable()
    {
        return !empty($this->model->getVisible()) ? $this->model->getFillable() : $this->model->getVisible();
    }


    protected function modelInput($input, $old_input = null, $edit = false){
        throw new \Exception('Some thing went wrong! You must not be setting the modelInput method!');
    }

    public function getInputType($input, $old_input = null, $edit = false)
    {
        $input = !empty($old_input) ? $old_input : $input;
        if (stripos($input, 'id') !== false |
            stripos($input, '_id') !== false
        ) {
            if ($edit === false) {
                return '<!-- There is a relation that requires the key ' . htmlentities($input) . ', assuming that it will be handled later -->';
            } else {
                return 'text';
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
            stripos($input, 'percent') !== false
        ) {
            return 'number';
        } elseif (stripos($input, 'date') !== false | stripos($input, '_date') !== false | stripos($input,
                'start') !== false | stripos($input, 'finish') !== false
        ) {
            return 'date';
            // Assume that the desired result is a boolean.
        } elseif (stripos($input, 'is_') !== false) {
            return 'select';
            // Assume that the desired result is a passwordc.
        } elseif (stripos($input, 'password') !== false) {
            return 'password';

            // Checks to make sure that it's not an email_list, list_email, some_email_list, or someemaillist field
        } elseif (stripos($input, 'email') !== false & stripos($input, 'list') === false) {
            return 'email';
        } elseif (stripos($input, 'path') !== false) {
            return 'file';
        } else {
            return 'text';
        }
    }

    /**
     * Eventually will be used to convert common shortnames or
     * common coding errors to common english.
     *
     * @param String $input
     *
     * @return String
     */
    protected function inputToRead($input)
    {
        if ($input === 'path') {
            $input = 'file';
        } elseif ($input === 'desc') {
            $input = 'description';
        }
        return ucwords(preg_replace('/[-_]/', ' ', $input));
    }

    /**
     * Setter function for the desired model.
     * @param Model $model
     * @return $this
     */
    public function withModel(Model $model)
    {
        $this->model = $model;
        return $this;
    }

    /**
     * Setter function for the desired submit location
     * @param $location
     * @return $this
     */
    public function submitTo($location)
    {
        $this->location = $location;
        return $this;
    }
}