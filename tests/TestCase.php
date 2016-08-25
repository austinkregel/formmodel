<?php

require 'vendor/autoload.php';
use Kregel\FormModel\FormModel;
use Mockery as m;

abstract class TestCase extends PHPUnit_Framework_TestCase
{
    protected $formmodel = null;

    public function setUp()
    {
    }

    public function tearDown()
    {
        m::close();
    }

    protected function formModel()
    {
        return new FormModel();
    }
}

if (!function_exists('config')) {
    function config()
    {
    }
}
if (!function_exists('is_html')) {
    function is_html($string)
    {
        return preg_match('/<[^<]+>/', $string, $m) != 0;
    }
}
if (!function_exists('view')) {
    function view($view, $data)
    {
        // This is just a quick hack because of course views are going to
        // Spit out real html.... Pfff....
        return '<form>'.$data['type'].$data['form_'].'</form>';
    }
}

if (!function_exists('auth')) {
    function auth()
    {
        return new class() {
            public function user()
            {
                return new class() extends \Illuminate\Database\Eloquent\Model {
                };
            }
        };
    }
}
