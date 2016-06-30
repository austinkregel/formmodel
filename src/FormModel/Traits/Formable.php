<?php

namespace Kregel\FormModel\Traits;

trait Formable
{
    protected $form_name;

    /**
     * @return mixed
     */
    public function getFormName()
    {
        return $this->form_name;
    }

    /**
     * @param mixed $form_name
     */
    public function setFormName($form_name)
    {
        $this->form_name = $form_name;
    }
}
