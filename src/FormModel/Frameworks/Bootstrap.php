<?php

namespace Kregel\FormModel\Frameworks;

use Illuminate\Database\Eloquent\Model;
use Kregel\FormModel\Interfaces\FrameworkInputs;
use Kregel\FormModel\Interfaces\FrameworkInterface;

class Bootstrap extends FrameworkInputs implements FrameworkInterface
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

    /**
     * # N/A request
     * This will allow you to get the proper input type for an HTML form.
     * It will extract the names from the a model.
     *
     * TODO: Clean up the methods. Shrink the size of this. To much for one method.
     *
     * @param string $input
     * @param array  $input
     * @param bool   $edit
     *
     * @return string (an HTML input element)
     */
    protected function modelInput($input, $old_input = null, $edit = false)
    {
        $type = $this->getInputType($input, $old_input, $edit);
        if (strlen($type) > 12) {
            if (stripos($input, '_id') !== false) {
                if (!empty(config('kregel.warden.models'))) {
                    // Check if Warden exists
                    $name = trim($input, '_id');
                    $options = (auth()->user()->$name !== null) ? auth()->user()->$name : $this->model->$name;
                    /* grab the model relation. what to do if there is no relation?
  185036112                      convert it to a collection later on...*/
                    if (empty($options)) {
                        $model = config('kregel.warden.models.'.$name.'.model');
                        if (!empty($model)) {
                            $options = $model::all();
                        }
                    }
                } else {
                    $options = (auth()->user()->$input !== null) ? auth()->user()->$input : $this->model->$input;
                }
                $ops = [];
                if (!$options instanceof Collection && !empty($options)) {
                    $options = collect([$options]);
                }
                if (!empty($options)) {
                    if (!$options->isEmpty()) {
                        foreach ($options as $option) {
                            $ops[$option->id] = ucwords(preg_replace('/[-_]+/', ' ', $option->name));
                        }
                        $default = empty($this->model->{trim($input, '_id')}->id) ? '' : $this->model->{trim($input, '_id')}->id;

                        return $this->select([
                            'default_text' => 'Please select a '.trim($input, '_id').' to assign this to',
                            'default'      => empty($default) ? '' : $default,
                            'type'         => 'select',
                            'class'        => 'form-control',
                            'name'         => $input,
                            'id'           => $this->genId($input),
                        ], $ops);
                    }
                }
            }
        }
        if ($type === 'select') {
            return $this->select([
                'default_text' => 'Please select a '.trim($input, '_id'),
                'type'         => $type,
                'name'         => $input,
                'id'           => $this->genId($input),
            ], [
                false => 'No',
                true  => 'Yes',
            ]);
        } elseif (in_array($type, [
            'text',
        ])) {
            return $this->textarea([
                'type'    => $type,
                'name'    => $input,
                'id'      => $this->genId($input),
            ], (!empty($this->model->$input) && !(stripos($input,
                        'password') !== false)) ? $this->model->$input : '');
        } elseif ($type === 'file') {
            $label = (!empty($options['name']) ? ucwords($options['name']) : '');
            $returnable = '<div class="file-field input-field"><div class="btn"><span>Your file</span>
                '.parent::plainInput([
                    'type'     => $type,
                    'name'     => $input,
                    'class'    => 'form-control',
                    'id'       => $this->genId($label),
                    'multiple' => '',
                ]).(empty($label) | (substr($label, 0,
                        1) == '_') ? '' : '<label for="'.$this->genId($label).'">'.$label.'</label>').'
                </div>
                <div class="file-path-wrapper">
                <input class="file-path validate" type="text" placeholder="Upload one or more files">
              </div>
            </div>';

            return $returnable;
        } elseif (in_array($type, [
            'password',
            'email',
            'date',
            'number',
        ])) {
            return $this->input([
                'type'    => $type,
                'name'    => $input,
                'class'   => 'form-control',
                'id'      => $this->genId($input),
            ]);
        }
    }

    public function select(array $configs, array $options)
    {
        $label = (!empty($configs['name']) ? str_replace('_', ' ', ucwords(trim($configs['name'], '_id'))) : '');

        return '<div class="form-group row">
                '.(empty($label) ? '' : '<div class="col-md-4 control-label text-right"><label>'.$label.'</label></div>').'<div class="col-md-6">'.parent::plainSelect($configs,
            array_merge([
                'class' => 'form-control',
            ], $options)).'
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
