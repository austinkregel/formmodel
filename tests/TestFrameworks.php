<?php

/**
 * Created by PhpStorm.
 * User: austinkregel
 * Date: 8/3/16
 * Time: 10:40 PM.
 */
class TestFrameworks extends TestCase
{
    public function test_can_create_new_form_model_instance()
    {
        $this->formModel();
    }

    public function test_can_use_bootstrap_formmodel_framework()
    {
        $this->assertTrue(
            $this->can_i_use_a_formmodel_framework('bootstrap')
        );
    }

    public function test_can_use_bootstrap_vue_formmodel_framework()
    {
        $this->assertTrue(
            $this->can_i_use_a_formmodel_framework('bootstrap-vue')
        );
    }

    public function test_can_use_materialize_formmodel_framework()
    {
        $this->assertTrue(
            $this->can_i_use_a_formmodel_framework('materialize')
        );
    }

    public function test_can_use_materialize_vue_formmodel_framework()
    {
        $this->assertTrue(
            $this->can_i_use_a_formmodel_framework('materialize-vue')
        );
    }

    private function can_i_use_a_formmodel_framework($framework)
    {
        $form = $this->formModel();
        $form->using($framework);

        return true;
    }

    public function test_can_tell_if_frameworks_are_broken_dynamically()
    {
        $this->should_tell_if_frameworks_are_broken('bootstrap');
        $this->should_tell_if_frameworks_are_broken('bootstrap-vue');
        $this->should_tell_if_frameworks_are_broken('materialize-vue');
        $this->should_tell_if_frameworks_are_broken('materialize');
    }

    private function should_tell_if_frameworks_are_broken($framework)
    {
        echo $framework;
        $model = new class() extends \Illuminate\Database\Eloquent\Model {
            protected $fillable = [
                'name', 'project_id', 'ping_to',
            ];

            public function project()
            {
                return $this->hasMany('App\Projects');
            }
        };
        $form = $this->formModel()
            ->using($framework)
            ->withModel($model)
            ->submitTo('/some/test/path')
            ->form([
                'method'  => 'post',
                'enctype' => 'multipart/form-data',
            ]);
        $this->assertTrue(is_html($form));
    }
}
