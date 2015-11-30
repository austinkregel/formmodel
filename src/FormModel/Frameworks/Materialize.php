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
    private function genId($label)
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
                'name' => $old_input,
                'v-model' => 'data.' . $old_input,
                'id' => $this->genId($old_input)
            ], [
                false => 'No',
                true => 'Yes'
            ]);
        } elseif ($type === 'text') {
            return $this->textarea([
                'type' => $type,
                'name' => $old_input,
                'v-model' => 'data.' . $old_input,
                 'id' => $this->genId($old_input)
            ], (!empty($this->model->$input) && !(stripos($input, 'password') !== false)) ? $this->model->$input : '');
        } else {
            return $this->input([
                'type' => $type,
                'name' => $old_input,
                'v-model' => 'data.' . $old_input,
                'value' => (!empty($this->model->$input) && !(stripos($input,
                            'password') !== false)) ? $this->model->$input : '',
                'id' => $this->genId($old_input)
            ]);
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
                1) == '_') ? '' : '<label for="' . $label . '">' . $label . '</label>') .
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
                1) == '_') ? '' : '<label for="' . $this->genId($label) . '">' . $label . '</label>') .
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
                1) == '_') ? '' : '<label for="' . $this->genId($label) . '">' . $label . '</label>') . '
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