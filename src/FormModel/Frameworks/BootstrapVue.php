<?php

namespace Kregel\FormModel\Frameworks;

class BootstrapVue extends Bootstrap
{
    /**
     * Generate the form.
     * @param array $options
     * @return string
     */
    public function form(Array $options =[]){
        $this->options = $options;
        $this->form = parent::form(array_merge([ '@submit.prevent' => 'makeRequest' ], $this->options));

        return view('formmodel::form', [
            'form'           => $this->form,
            'vue_components' => $this->vue_components,
            'method'         => $this->options['method']
        ]);
    }

    public function modelInput($input, $old_input = null, $edit = false){
        $this->vue_components[] = $old_input;
        return parent::modelInput($input, $old_input, $edit);
    }
}
