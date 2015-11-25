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
            '<div class="class="col-md-6">' . parent::plainInput(array_merge($options,[
            'class' => 'form-control'
        ])) . '</div>
        </div>';
    }

    public function textarea(Array $options, $text = '')
    {
        $label = (!empty($options['name']) ? ucwords($options['name']) : '');
        return '<div class="form-group">
    '.(empty($label) | (substr($label,0,1) == '_') ?'':'<label class="col-md-4 control-label">' . $label . '</label>').
            '<div class="class="col-md-6">' . parent::plainTextarea(array_merge($options,[
           'class' => 'form-control'
        ]), $text) . '</div>
        </div>';
    }

    public function select(Array $configs, Array $options)
    {
        $label = (!empty($options['name']) ? ucwords($options['name']) : '');
        return '<div class="form-group">
                '.(empty($label) | (substr($label,0,1) == '_') ?'':'<label class="col-md-4 control-label">' . $label . '</label>').
        '<div class="class="col-md-6">'
        . parent::plainSelect($configs, array_merge($options,[
            'class' => 'form-control'
        ])) . '</div>
        </div>';
    }

    public function submit(Array $options = [])
    {
        $label = (!empty($options['name']) ? ucwords($options['name']) : '');

        return '<div class="form-group">
                '.(empty($label) | (substr($label,0,1) == '_') ?'':'<label class="col-md-4 control-label">' . $label . '</label>').
        '<div class="class="col-md-6">' .  parent::plainSubmit(array_merge($options,[
            'class' => 'btn btn-control'
        ])) . '</div>
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
     * @param Array $old_input
     * @param Boolean $edit
     *
     * @return String (an HTML input element)
     */
    protected function modelInput($input, $old_input = null, $edit = false)
    {
        $type = $this->getInputType($input, $old_input, $edit);
        if ($type === 'select') {
            return $this->select([
                'type' => $type,
                'class' => 'form-control',
                'name' => $old_input,
                'v-model' => 'data.' . $old_input,
            ], [
                false => 'No',
                true => 'Yes'
            ]);
        } elseif ($type === 'text') {
            return $this->textarea([
                'type' => $type,
                'class' => 'form-control',
                'name' => $old_input,
                'v-model' => 'data.' . $old_input,
                'placeholder' => $this->inputToRead($old_input),
                'label' => $this->inputToRead($old_input),
            ], (!empty($this->model->$input) && !(stripos($input, 'password') !== false)) ? $this->model->$input : '');
        } else {
            return $this->input([
                'type' => $type,
                'class' => 'form-control',
                'name' => $old_input,
                'v-model' => 'data.' . $old_input,
                'placeholder' => $this->inputToRead($old_input),
                'label' => $this->inputToRead($old_input),
                'value' => (!empty($this->model->$input) && !(stripos($input,
                            'password') !== false)) ? $this->model->$input : '',
            ]);
        }
    }
}