<?php

namespace Kregel\FormModel\Frameworks;

use Kregel\FormModel\Interfaces\FrameworkInputs;
use Kregel\FormModel\Interfaces\FrameworkInterface;

class Plain extends FrameworkInputs implements FrameworkInterface
{
    /**
     * @param array $options
     *
     * @return mixed
     */
    public function input(array $options)
    {
        return parent::plainInput($options);
    }

    /**
     * @param array $options
     * @param $text
     *
     * @return mixed
     */
    public function textarea(array $options, $text)
    {
        return parent::plainTextarea($options, $text);
    }

    /**
     * @param array $configs
     * @param array $options
     *
     * @return mixed
     */
    public function select(array $configs, $options)
    {
        return parent::plainSelect($configs, $options);
    }

    /**
     * @param array $options
     *
     * @return mixed
     */
    public function submit(array $options = [])
    {
        return parent::plainSubmit($options);
    }

    /**
     * @param Model $model
     * @param array $options
     *
     * @return mixed
     */
    public function form(array $options = [])
    {
        $this->options = $options;
        $this->form = parent::form(array_merge([
            'form' => [
                'class'  => 'form-horizontal',
                'action' => request()->url(),
                'method' => 'POST',

            ],
        ], $this->options));

        return view('formmodel::form_types.plain', [
            'form_'      => $this->form,
            'components' => $this->vue_components,
            'type'       => $this->options['method'],
        ]);
    }
}
