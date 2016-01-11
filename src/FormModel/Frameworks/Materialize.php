<?php


namespace Kregel\FormModel\Frameworks;

use Kregel\FormModel\Interfaces\FrameworkInputs;
use Kregel\FormModel\Interfaces\FrameworkInterface;

class Materialize extends FrameworkInputs implements FrameworkInterface
{
    /**
     * Generate a submit button for the form.
     * @param array $options
     * @return string
     */
    public function submit(Array $options = [])
    {
        $label = (!empty($options['name']) ? ucwords($options['name']) : '');

        return '<div class="input-field">
                ' . (empty($label) | (substr($label, 0, 1) == '_')
            ? '' : '<label for="' . $this->genId($label) . '">' . $label . '</label>') .
        parent::plainSubmit(array_merge([
            'class' => 'btn waves-effect waves-light pull-right'
        ], $options)) . '</div>
        </div>';
    }

    /**
     * Generate the label/id/for for the inputs
     * @param $label
     * @return string
     */
    protected function genId($label)
    {
        return strtolower(preg_replace('/[-\s]+/', '_', $label));
    }

    /**
     * # N/A request
     * This will allow you to get the proper input type for an HTML form.
     * It will extract the names from the a model.
     *
     * TODO: Clean up the methods. Shrink the size of this. To much for one method.
     *
     * @param String $input
     * @param Array $input
     * @param Boolean $edit
     *
     * @return String (an HTML input element)
     */
       protected function modelInput($input, $old_input = null, $edit = false)
    {
        $type = $this->getInputType($input, $old_input, $edit);
        if (strlen($type) > 12) {
            if (stripos($input, '_id') !== false) {
                $input = $input;
                $input = $this->modelSwapType($input);
                if (!empty(config('kregel.warden.models'))) {
                    $name = trim($input, '_id');
                    $class = config('kregel.warden.models.'.$name.'.model');
                    if (!empty($class)) {
                        $class = new $class();
                        $options = Auth::user()->$name;
                    } else {
                        $options = $this->model->$name;
                    }
                } else {
                    $options = Auth::user()->$input;
                }
                $ops = [];
                if (!empty($options)) {
                    foreach ($options as $option) {
                        $ops[$option->id] = ucwords(preg_replace('/[-_]+/', ' ', $option->name));
                    }
                    return $this->select([
                        'default' => 'Please select a '.trim($input, '_id').' to assign this to',
                        'type' => 'select',
                        'name' => $input,
                        'v-model' => 'data.'.$input,
                        '@update' => 'updateSelect',
                        'id' => $this->genId($input),
                        'lazy' => '',
                    ], $ops);
                }
            }
        }
        if ($type === 'select') {
            return $this->select([
                'type' => $type,
                'name' => $input,
                'v-model' => 'data.'.$input,
                'id' => $this->genId($input),
            ], [
                false => 'No',
                true => 'Yes',
            ]);
        } elseif ($type === 'text') {
            return $this->textarea([
                'type' => $type,
                'name' => $input,
                'v-model' => 'data.'.$input,
                'id' => $this->genId($input),
            ], (!empty($this->model->$input) && !(stripos($input, 'password') !== false)) ? $this->model->$input : '');
        } elseif ($type === 'file') {
            $label = (!empty($options['name']) ? ucwords($options['name']) : '');
            $returnable = '<div class="file-field input-field"><div class="btn"><span>Your file</span>
                '.
                parent::plainInput([
                    'type' => $type,
                    'name' => $input,
                    'v-el' => str_slug($input),
                    'class' => 'validate',
                    'id' => $this->genId($label),
                    'multiple' => '',
                ]).(empty($label) | (substr($label, 0,
                        1) == '_') ? '' : '<label for="'.$this->genId($label).'">'.$this->inputToRead($label).'</label>').'
                </div>
                <div class="file-path-wrapper">
                <input class="file-path validate" type="text" placeholder="Upload one or more files">
              </div>
            </div>';
            return $returnable;
        } else {
            // Default input type
        }
    }


    /**
     * Generate a select
     * @param array $configs
     * @param array $options
     * @return string
     */
    public function select(Array $configs, Array $options)
    {
        $label = (!empty($options['name']) ? ucwords($options['name']) : '');

        return '<div class="input-field">
        ' . parent::plainSelect($configs, array_merge( [
            'id' => $this->genId($label)
        ], $options)) .
        (empty($label) | (substr($label, 0,
                1) == '_') ? '' : '<label for="' . $label . '">' . $this->inputToRead($label) . '</label>') .
        '</div>';
    }

    /**
     * Generate a textarea
     * @param array $options
     * @param string $text
     * @return string
     */
    public function textarea(Array $options, $text = '')
    {
        $label = (!empty($options['name']) ? ucwords($options['name']) : '');
        return '<div class="input-field">' .
            parent::plainTextarea(array_merge([
            'class' => 'materialize-textarea',
            'id' => $this->genId($label)
        ], $options), $text) .
        (empty($label) | (substr($label, 0,
                1) == '_') ? '' : '<label for="' . $this->genId($label) . '">' . $this->inputToRead($label) . '</label>') .
        '</div>';
    }

    /**
     * Generate an input area.
     * @param array $options
     * @return string
     */
    public function input(Array $options)
    {
        $label = (!empty($options['name']) ? ucwords($options['name']) : '');
        return '<div class="input-field">
                ' .
        parent::plainInput(array_merge([
            'class' => 'validate',
            'id' => $this->genId($label)
        ], $options)) . (empty($label) | (substr($label, 0,
                1) == '_') ? '' : '<label for="' . $this->genId($label) . '">' . $this->inputToRead($label) . '</label>') . '
        </div>';
    }

    /**
     * Generate the form.
     * @param array $options
     * @return string
     */
    public function form(Array $options = []) {
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
                $this->submit(['class' => 'btn waves-effect waves-light']) .
            '</form>';
    }
}
