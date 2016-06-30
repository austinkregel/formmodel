<?php

namespace Kregel\FormModel\Frameworks;

use Illuminate\Support\Collection;

class MaterializeVue extends Materialize
{
    public $vue_components = [];
    public $options = [];
    public $form = '';

    /**
     * Generate the form.
     *
     * @param array $options
     *
     * @return string
     */
    public function form(array $options = [])
    {
        $this->options = $options;
        $this->form = parent::form(array_merge(['@submit.prevent' => 'makeRequest'], $this->options));

        return view('formmodel::form_types.bootstrap-vue', [
        'form_'      => $this->form,
        'components' => $this->vue_components,
        'type'       => $this->options['method'],
    ]);
    }

    public function modelInput($input, $old_input = null, $edit = false)
    {
        $this->vue_components[] = $input;

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
                            $this->accessor = !empty($option->form_name) ? $option->form_name : 'name';
                            $ops[$option->id] = ucwords(preg_replace('/[-_]+/', ' ', $option->{$this->accessor}));
                        }
                        $default = empty($this->model->{trim($input, '_id')}->id) ? '' : $this->model->{trim($input, '_id')}->id;

                        return $this->select([
                        'default_text' => 'Please select a '.trim($input, '_id').' to assign this to',
                        'default'      => empty($default) ? '' : $default,
                        'type'         => 'select',
                        'name'         => $input,
                        'v-model'      => 'data.'.$input,
                        '@update'      => 'updateSelect',
                        'id'           => $this->genId($input),
                    ], $ops);
                    }
                }
            }
        }
        if ($type === 'select') {
            return $this->select([
            'type'    => $type,
            'name'    => $input,
            'v-model' => 'data.'.$input,
            'id'      => $this->genId($input),
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
            'v-model' => 'data.'.$input,
            'id'      => $this->genId($input),
        ], (!empty($this->model->$input) && !(stripos($input,
                    'password') !== false)) ? $this->model->$input : '');
        } elseif ($type === 'file') {
            $label = (!empty($options['name']) ? ucwords($options['name']) : '');
            $returnable = '<div class="file-field input-field"><div class="btn"><span>Your file</span>
                '.parent::plainInput([
                'type'     => $type,
                'name'     => $input,
                'v-el'     => str_slug($input),
                'class'    => 'validate',
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
            'v-model' => 'data.'.$input,
            'id'      => $this->genId($input),
        ]);
        }
    }
}
