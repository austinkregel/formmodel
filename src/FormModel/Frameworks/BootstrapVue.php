<?php

namespace Kregel\FormModel\Frameworks;

class BootstrapVue extends Bootstrap
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
        $this->form = parent::form($this->options);

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
                $options = $this->getRelationFromLoggedInUserIfPossible($input) ?? $this->getRelationalDataAndModels($this->model, $input);
                $ops = [];
                if (!empty($options)) {
                dd($options);
                    if (!$options->isEmpty()) {
                        foreach ($options as $option) {
                            if(method_exists($option, 'getFormName'))
                                $this->accessor = $option->getFormName();
                            else 
                                $this->accessor = 'name';
                            $ops[$option->id] = ucwords(preg_replace('/[-_]+/', ' ', $option->{$this->accessor}));
                        }

                        // $ops = array_map(function($option){
                        //     if(method_exists($option, 'getFormName')){
                        //         $accessor = $option->getFormName();
                        //     } else {
                        //         $accessor = 'name';
                        //     }
                        //     return $option->{$accessor};
                        // }, $options);


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
                'v-model'      => 'data.'.$input,
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
                    'v-el'     => str_slug($input),
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
}
