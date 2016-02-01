<?php

namespace Kregel\FormModel\Interfaces;

interface FrameworkInterface
{
    /**
     * @param array $options
     *
     * @return mixed
     */
    public function input(Array $options);

    /**
     * @param array $options
     * @param $text
     *
     * @return mixed
     */
    public function textarea(Array $options, $text);

    /**
     * @param array $configs
     * @param array $options
     *
     * @return mixed
     */
    public function select(Array $configs, Array $options);

    /**
     * @param array $options
     *
     * @return mixed
     */
    public function submit(Array $options = []);

    /**
     * @param Model $model
     * @param array $options
     *
     * @return mixed
     */
    public function form(Array $options);
}
