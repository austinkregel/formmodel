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

    public function test_can_use_bootstrap_formmodel_framework_dynamically()
    {
        $this->assertTrue(
            $this->can_i_use_a_formmodel_framework('bootstrap')
        );

        $this->assertTrue(
            $this->can_i_use_a_formmodel_framework('bootstrap-vue')
        );

        $this->assertTrue(
            $this->can_i_use_a_formmodel_framework('materialize')
        );

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
        /*
         * This anonymous class is just suppose to resemble any old model.
         * wich has a relation.
         */
        $model = new class() extends \Illuminate\Database\Eloquent\Model {
            protected $fillable = [
                'name', 'project_id', 'ping_to',
            ];

            public function project()
            {
                // TODO: Make this an actual class i can test against.
                return $this->hasMany('SomeClass');
            }
        };
        // We know that this method will work because of the other tests being ran
        $form = $this->formModel()
            // We also know that this method will work.
            ->using($framework)
            // TODO: Test the withModel method
            ->withModel($model)
            // TODO: Verify that the action in the form is set to this url
            ->submitTo('/some/test/path')
            /// TODO: Make sure this is valid HTMl
            ->form([
                'method'  => 'post',
                'enctype' => 'multipart/form-data',
            ]);
        // The form method should return some <form> string
        // It SHOULD be valid html...
        $this->assertTrue(is_html($form));
    }
}
