<?php


namespace Kregel\FormModel\Frameworks;


class MaterializeVue extends Materialize
{
    /**
     * Generate the form.
     * @param array $options
     * @return string
     */
    public function form(Array $options =[]){
        return '<div id="vue-form-wrapper">
                        <div id ="response" v-show="response">
                            {{ response }}
                            <div class="close" @click="close">&times;</div>
                        </div>' .
                        parent::form($options) . '
                    </div>' .
            view('formmodel::vue')->with('components', $this->vue_components)->with('type', $options['method']);
    }

    public function modelInput($input, $old_input = null, $edit = false){
        $this->vue_components[] = $input;
        parent::modelInput($input, $input, $edit);
    }
}