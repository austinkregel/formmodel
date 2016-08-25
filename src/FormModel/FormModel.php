<?php

namespace Kregel\FormModel;

class FormModel
{
    public function using($string)
    {
        switch ($string) {
            case 'bootstrap':
                return new Frameworks\Bootstrap();
            case 'materialize':
                return new Frameworks\Materialize();
            default:
                $custom_thing = config('kregel.formmodel.using.custom-framework');

                return call_user_func($custom_thing);
        }
    }
}
