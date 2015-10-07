<?php

namespace Kregel\FormModel\Facades;

use Illuminate\Support\Facades\Facade;

class FormModel extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'formmodel';
    }
} 
