<?php

namespace Kregel\FormModel\Interfaces;

interface FrameworkInterface
{
    /**
     * @param array $options
     *
     * @return mixed
     */
    public function input(array $options);

    /**
     * @param array $options
     * @param $text
     *
     * @return mixed
     */
    public function textarea(array $options, $text);

    /**
     * @param array $configs
     * @param array $options
     *
     * @return mixed
     */
    public function select(array $configs, array $options);

    /**
     * @param array $options
     *
     * @return mixed
     */
    public function submit(array $options = []);

    /**
     * @param Model $model
     * @param array $options
     *
     * @return mixed
     */
    public function form(array $options);
}
