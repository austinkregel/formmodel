<?php


namespace Kregel\FormModel\Frameworks;

use Illuminate\Database\Eloquent\Model;
use Kregel\FormModel\Interfaces\FrameworkInputs;
use Kregel\FormModel\Interfaces\FrameworkInterface;
class Bootstrap extends FrameworkInputs implements FrameworkInterface
{
    public function input(Array $options)
    {
        $label = (!empty($options['name']) ? ucwords($options['name']) : '');
        return '<div class="form-group">
                '.(empty($label) | (substr($label,0,1) == '_') ?'':'<label class="col-md-4 control-label">' . $label . '</label>').
            '<div class="class="col-md-6">' . parent::plainInput(array_merge([
            'class' => 'form-control'
        ], $options)) . '</div>
        </div>';
    }

    public function textarea(Array $options, $text = '')
    {
        $label = (!empty($options['name']) ? ucwords($options['name']) : '');
        return '<div class="form-group">
    '.(empty($label) | (substr($label,0,1) == '_') ?'':'<label class="col-md-4 control-label">' . $label . '</label>').
            '<div class="class="col-md-6">' . parent::plainTextarea(array_merge([
           'class' => 'form-control'
        ], $options), $text) . '</div>
        </div>';
    }

    public function select(Array $configs, Array $options)
    {
        $label = (!empty($options['name']) ? ucwords($options['name']) : '');
        return '<div class="form-group">
                '.(empty($label) | (substr($label,0,1) == '_') ?'':'<label class="col-md-4 control-label">' . $label . '</label>').
        '<div class="class="col-md-6">'
        . parent::plainSelect($configs, array_merge([
            'class' => 'form-control'
        ], $options)) . '</div>
        </div>';
    }

    public function submit(Array $options = [])
    {
        $label = (!empty($options['name']) ? ucwords($options['name']) : '');

        return '<div class="form-group">
                '.(empty($label) | (substr($label,0,1) == '_') ?'':'<label class="col-md-4 control-label">' . $label . '</label>').
        '<div class="class="col-md-6">' .  parent::plainSubmit(array_merge([
                'class' => 'btn btn-primary pull-right'
            ], $options)) . '</div>
        </div>';
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
        $type = $this->getInputType($input, $input, $edit);
        if ($type === 'select') {
            return $this->select([
                'type' => $type,
                'class' => 'form-control',
                'name' => $input,
                'v-model' => 'data.' . $input,
            ], [
                false => 'No',
                true => 'Yes'
            ]);
        } elseif ($type === 'text') {
            return $this->textarea([
                'type' => $type,
                'class' => 'form-control',
                'name' => $input,
                'v-model' => 'data.' . $input,
                'placeholder' => $this->inputToRead($input),
                'label' => $this->inputToRead($input),
            ], (!empty($this->model->$input) && !(stripos($input, 'password') !== false)) ? $this->model->$input : '');
        } else {
            return $this->input([
                'type' => $type,
                'class' => 'form-control',
                'name' => $input,
                'v-model' => 'data.' . $input,
                'placeholder' => $this->inputToRead($input),
                'label' => $this->inputToRead($input),
                'value' => (!empty($this->model->$input) && !(stripos($input,
                            'password') !== false)) ? $this->model->$input : '',
            ]);
        }
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
                $this->submit([]) .
            '</form>';
    }
}
