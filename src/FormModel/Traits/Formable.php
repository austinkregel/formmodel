<?php

namespace Kregel\FormModel\Traits;

trait Formable
{
    /**
     * @return mixed
     */
    public function getFormName()
    {
        if (property_exists($this, 'form_model')) {
            return $this->form_name;
        }

        return 'name';
    }

    /**
     * @param mixed $form_name
     */
    public function setFormName($form_name)
    {
        $this->form_name = $form_name;
    }
}
