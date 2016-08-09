<?php

namespace Kregel\FormModel\Frameworks;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Kregel\FormModel\Interfaces\FrameworkInputs;
use Kregel\FormModel\Interfaces\FrameworkInterface;

class Bootstrap extends Plain
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
        $this->csrf().$this->buildForm().$this->submit([]).'</form>';
    }

    public function submit(array $options = [])
    {
        $label = (!empty($options['name']) ? ucwords($options['name']) : '');

        return '<div class="form-group">
                '.(empty($label) | (substr($label, 0,
                1) == '_') ? '' : '<div class="col-md-4 control-label"><label>'.$label.'</label></div>').'<div class="col-md-6">'.parent::plainSubmit(array_merge([
            'class' => 'btn btn-primary pull-right',
        ], $options)).'</div>
        </div>';
    }

    public function select(array $configs, array $options)
    {
        $label = (!empty($configs['name']) ? str_replace('_', ' ', ucwords(trim($configs['name']))) : '');

        return '<div class="form-group row">
                '.(empty($label) ? '' : '<div class="col-md-4 control-label text-right"><label>'.$label.'</label></div>').'<div class="col-md-6">'.parent::plainSelect($configs, $options).'
        </div>
        </div>';
    }

    public function textarea(array $options, $text = '')
    {
        $label = (!empty($options['name']) ? ucwords($options['name']) : '');

        return '<div class="form-group row">
    '.(empty($label) | (substr($label, 0,
                1) == '_') ? '' : '<div class="col-md-4 control-label text-right"><label>'.$label.'</label></div>').'<div class="col-md-6">'.parent::plainTextarea(array_merge([
            'class' => 'form-control',
        ], $options), $text).'</div>
        </div>
        ';
    }

    public function input(array $options)
    {
        $label = (!empty($options['name']) ? ucwords($options['name']) : '');

        return '<div class="form-group row">
                '.(empty($label) | (substr($label, 0,
                1) == '_') ? '' : '<label class="col-md-4 control-label text-right" style="padding:8px 0;margin:0;">'.$label.'</label>').'<div class="col-md-6">'.parent::plainInput(array_merge([
            'class' => 'form-control',
        ], $options)).'</div>
        </div>';
    }
}
