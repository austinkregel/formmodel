<?php

namespace Kregel\FormModel;

class FormModel
{
    public static function using($string)
    {
        switch ($string) {
            case 'bootstrap':
                return new Frameworks\Bootstrap();
            case 'materialize':
                return new Frameworks\Materialize();
            case 'plain':
                return new Frameworks\Plain();
            default:
                $custom_thing = config('kregel.formmodel.using.custom-framework');
                return call_user_func($custom_thing);
        }
    }
}
