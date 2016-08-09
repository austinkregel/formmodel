<?php

namespace Kregel\FormModel\Frameworks;

class Materialize extends Plain
{
    /**
     * Generate the form.
     *
     * @param array $options
     *
     * @return string
     */
    public function form(array $options = [])
    {
        $method = empty($options['method']) ? $options['method'] : '';
        if (in_array(strtolower($method), ['get', 'post'])) {
            $real_method = $method;
        } else {
            $real_method = 'POST';
        }
        $options['method'] = $real_method;
        $options['action'] = $this->location;

        return '<form '.$this->attributes($options).'>'.// Pass the method through so the form knows how to handle it's self (with laravel)
        $this->method($method).// Check and fill the csrf token if it's configured for it.
        $this->csrf().$this->buildForm().$this->submit(['class' => 'btn waves-effect waves-light']).'</form>';
    }

    /**
     * Generate a submit button for the form.
     *
     * @param array $options
     *
     * @return string
     */
    public function submit(array $options = [])
    {
        $label = (!empty($options['name']) ? ucwords($options['name']) : '');

        return '<div class="input-field">
                '.(empty($label) | (substr($label, 0,
                1) == '_') ? '' : '<label for="'.$this->genId($label).'">'.$label.'</label>').parent::plainSubmit(array_merge([
            'class' => 'btn waves-effect waves-light pull-right',
        ], $options)).'</div>
        </div>';
    }

    /**
     * Generate a select.
     *
     * @param array $configs
     * @param array $options
     *
     * @return string
     */
    public function select(array $configs, array $options)
    {
        $label = (!empty($options['name']) ? ucwords($options['name']) : '');

        return '
        <div class="input-field">
        '.parent::plainSelect(array_merge([
            'id' => $this->genId($label),
        ], $configs), $options).(empty($label) | (substr($label, 0,
                1) == '_') ? '' : '<label for="'.$label.'">'.$this->inputToRead($label).'</label>').'
        </div>
         ';
    }

    /**
     * Generate a textarea.
     *
     * @param array  $options
     * @param string $text
     *
     * @return string
     */
    public function textarea(array $options, $text = '')
    {
        $label = (!empty($options['name']) ? ucwords($options['name']) : '');

        return '
            <div class="input-field">
                '.parent::plainTextarea(array_merge([
            'class' => 'materialize-textarea',
            'id'    => $this->genId($label),
        ], $options), $text).'
                '.(empty($label) | (substr($label, 0,
                1) == '_') ? '' : '<label for="'.$this->genId($label).'">'.$this->inputToRead($label).'</label>').'
            </div>';
    }

    /**
     * Generate an input area.
     *
     * @param array $options
     *
     * @return string
     */
    public function input(array $options)
    {
        $label = (!empty($options['name']) ? ucwords($options['name']) : '');

        return '
        <div class="input-field">
                '.parent::plainInput(array_merge([
            'class' => 'validate',
            'id'    => $this->genId($label),
        ], $options)).(empty($label) | (substr($label, 0,
                1) == '_') ? '' : '<label for="'.$this->genId($label).'">'.$this->inputToRead($label).'</label>').'
        </div>
        ';
    }
}
