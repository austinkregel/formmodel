<?php

namespace Kregel\FormModel\Interfaces;

use Illuminate\Database\Eloquent\Model;
use Kregel\FormModel\Traits\Formable;

abstract class FrameworkInputs
{
    use Formable;
    /**
     * @var
     */
    public $model;
    /**
     * @var
     */
    public $vue_components;
    /**
     * @var
     */
    protected $location;

    /**
     * @var
     */
    protected $accessor;

    public function plainTextarea($options, $text = '')
    {
        return '<textarea'.$this->attributes($options).'>'.
        (is_string($text) ? $text : collect($text)).'</textarea>';
    }

    /**
     * This function builds attributes for html elements
     * ex.
     *      id="name".
     *
     * @param array $attr A key value pair of attributes
     *                    for an HTML Element
     *
     * @return string $attr_string
     */
    public function attributes(array $attr)
    {
        $attr_string = '';
        foreach ($attr as $name => $value) {
            if (is_array($value)) {
                $attr_string .= ' '.$name.'="'.implode(' ', $value).'"';
            } else {
                $attr_string .= ' '.$name.'="'.$value.'"';
            }
        }

        return $attr_string;
    }

    public function plainSelect($configs, $options)
    {
        if (!empty($configs['default'])) {
            $default = $configs['default'];

            unset($configs['default']);
        } else {
            $default = ' ';
        }
        $default_text = empty($configs['default_text']) ? '' : $configs['default_text'];

        return '    <select'.$this->attributes($configs).'>'.
        '<option value="" disabled '.(is_numeric($default) ? '' : 'selected').'>'.$default_text."</option>\n"
        .$this->buildOptions($options, is_numeric($default) ? $default : false)."
       </select>\n";
    }

    public function buildOptions($options, $hasDefault = false)
    {
        $return = '';

        foreach ($options as $value => $text) {
            $attr = [];
            if ($hasDefault !== false && $value === $hasDefault) {
                $attr['selected'] = 'selected';
            }
            $attr['value'] = $value;
            $return .= '                  <option'.$this->attributes($attr).'>'.$text."</option>\n";
        }

        return $return;
    }

    public function plainSubmit(array $options = [])
    {
        return $this->input(array_merge(['type' => 'submit'], $options));
    }

    /**
     * @param array $options Should contain type, and name
     *
     * @return string html submit input
     */
    public function plainInput($options = [])
    {
        return '<input'.$this->attributes($options).'>';
    }

    /**
     * @param array $options
     *
     * @return string|html
     */
    public function form(array $options = [])
    {
        $method = empty($options['method']) ? $options['method'] : '';
        if (!in_array(strtolower($method), ['get', 'post'])) {
            $options['method'] = 'POST';
        }
        // Throw in all the attributes meant for the form
        return '<form '.$this->attributes($options['form']).'>'.
        $this->method($options['method']).
        $this->buildForm().
        $this->submit().'</form>';
    }

    public function method($method)
    {
        if (!in_array(strtolower($method), ['get', 'post'])) {
            return $this->input(['type' => 'hidden', 'name' => '_method', 'value' => $method]);
        }

        return '';
    }

    public function csrf()
    {
        if (config('kregel.formmodel.using.csrf')) {
            return $this->input(['type' => 'hidden', 'name' => '_token', 'value' => csrf_token()]);
        }

        return '';
    }

    protected $models = [];

    public function buildForm()
    {
        return implode('', array_map(function ($input) {
            return $this->modelInput($input);
        }, $this->getFillable($this->model)));
    }

    public function getFillable()
    {
        return empty($this->model->getVisible()) ? $this->model->getFillable() : $this->model->getVisible();
    }

    /**
     * This is the main baby for FormModel. This is the quickest way to
     * make new forms for models for creation or for editing/updating.
     * It will use and extract the fillbale or the visible properties from
     * Eloquent models. It will always prefer things in the visible attribute
     * This is because there might be an attribute from the fillable attribute
     * that you might not want to allow the end user to see.
     *
     * ex. Some kind of relation, I often use the User->id realtion and I often
     * want to hide the User->id relation and just use the Auth::user()->id
     * When the form is posted.
     *
     * @param string $input
     * @param string $old_input
     * @param bool   $edit
     *
     * @throws \Exception
     *
     * @return string (an HTML form)
     */
    protected function modelInput($input, $old_input = null, $edit = false)
    {
        $type = $this->getInputType($input, $old_input, $edit);
        if ($type === 'relationship') {
            $options = $this->getRelationalDataAndModels($this->model, $input, true) ?? $this->getRelationFromLoggedInUserIfPossible($input);
            $ops = [];
            if (!empty($options)) {
                foreach ($options as $option) {
                    if (method_exists($option, 'getFormName')) {
                        $this->accessor = $option->getFormName();
                    } else {
                        $this->accessor = 'name';
                    }
                    $ops[$option->id] = ucwords(preg_replace('/[-_]+/', ' ', $option->{$this->accessor}));
                }

                $desired_relation = trim($input, '_id');
                $default = empty($this->model->$desired_relation->id) ? '' : $this->model->$desired_relation->id;

                return $this->select([
                    'default_text' => 'Please select a '.$desired_relation.' to assign this to',
                    'default'      => empty($default) ? '' : $default,
                    'type'         => 'select',
                    'class'        => 'form-control',
                    'name'         => $input,
                ], $ops);
            }
        }
        // Determine block.
        return $this->spitOutHtmlForModelInputToConsume($type, $input);
    }

    /**
     * This function uses naming conventions to determine what a fillable attribute might be.
     *
     * @param $input
     * @param null $old_input
     * @param bool $edit
     *
     * @return string
     */
    public function getInputType($input, $old_input = null, $edit = false)
    {
        $input = !empty($old_input) ? $old_input : $input;
        if (stripos($input, 'id') !== false |
            stripos($input, '_id') !== false
        ) {
            return 'relationship';
        } elseif (
            (stripos($input, 'number') !== false &
                (
                    stripos($input, 'home_') === false &
                    stripos($input, 'fax_') === false &
                    stripos($input, 'recorder_') === false &
                    stripos($input, 'direct_') === false &
                    stripos($input, 'cell_') === false &
                    stripos($input, 'model') === false &
                    stripos($input, 'phone') === false
                )
            ) |
            (stripos($input, 'count') !== false &
                stripos($input, 'county') === false) |
            stripos($input, 'percent') !== false
        ) {
            return 'number';
        } elseif (stripos($input, 'date') !== false | stripos($input, '_date') !== false | stripos($input,
                'start') !== false | stripos($input, 'finish') !== false
        ) {
            return 'date';
            // Assume that the desired result is a boolean.
        } elseif (stripos($input, 'is_') !== false || stripos($input, 'can_') !== false || stripos($input, 'has_') !== false) {
            return 'select';
            // Assume that the desired result is a passwordc.
        } elseif (stripos($input, 'password') !== false) {
            return 'password';

            // Checks to make sure that it's not an email_list, list_email, some_email_list, or someemaillist field
        } elseif (stripos($input, 'email') !== false & stripos($input, 'list') === false) {
            return 'email';
        } elseif (stripos($input, 'path') !== false) {
            return 'file';
        } else {
            return 'text';
        }
    }

    /**
     * Eventually will be used to convert common shortnames or
     * common coding errors to common english.
     *
     * @param string $input
     *
     * @return string
     */
    protected function inputToRead($input)
    {
        if ($input === 'path') {
            $input = 'file';
        } elseif ($input === 'desc') {
            $input = 'description';
        }

        return ucwords(preg_replace('/[-_]/', ' ', $input));
    }

    /**
     * Setter function for the desired model.
     *
     * @param Model $model
     *
     * @return $this
     */
    public function withModel(Model $model)
    {
        $this->model = $model;

        return $this;
    }

    /**
     * Setter function for the desired submit location.
     *
     * @param $location
     *
     * @return $this
     */
    public function submitTo($location)
    {
        $this->location = $location;

        return $this;
    }

    /**
     * Generate the label/id/for for the inputs.
     *
     * @param $label
     *
     * @return string
     */
    protected function genId($label)
    {
        return strtolower(preg_replace('/[-\s]+/', '_', $label));
    }

    /**
     * This should get the realtions of the given model and.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param $desired_relation
     *
     * @return \Illuminate\Support\Collection|null
     */
    public function getRelationalDataAndModels($model, $desired_relation, $debug = false)
    {
        // $relations = $model->getRelations();
        $desired_relation = $this->trimCommonRelationEndings($desired_relation);
        // Grab all the model relationships that don't return as a collection
        if (method_exists($model, $desired_relation)) {
            return $this->getResolvedRelationship($model, $desired_relation);
        } elseif (method_exists($model, $desired_relation.'s')) {
            return $this->getResolvedRelationship($model, $desired_relation.'s');
        }
        // Return null because clearly, nothing matches what we need.
    }

    /**
     * This will see if the desired relation is a relation.
     *
     * @param $model
     * @param $desired_relation
     * @param array $singleRelations
     * @param array $multiRelations
     *
     * @return bool
     */
    protected function getResolvedRelationship($model, $desired_relation, $singleRelations = [
        BelongsTo::class,
        HasOne::class,
        MorphTo::class,
        MorphOne::class,
    ], $multiRelations = [
        HasMany::class,
        BelongsToMany::class,
        MorphMany::class,
        MorphToMany::class,
    ])
    {
        $relation_class = get_class($model->$desired_relation());
        if ($this->in_array($relation_class, $singleRelations)) {
            // It has a single relation so it should be collected and sent back.
            return collect([$model->$desired_relation]);
        } elseif ($this->in_array($relation_class, $multiRelations)) {
            // This is already a collection so we don't have to collect anything
            // TODO: Determine if we should have a limit function here or not...

            return $model->$desired_relation()->limit(15)->get();
        } else {
            echo "{$desired_relation} is not a relation \n";
        }

        $desired_class = get_class($model->$desired_relation()->getRelated());
        $closure = config('kregel.formmodel.resolved_relationship');

        return $closure($desired_class);
    }

    /**
     * @param $desired_relation
     *
     * @return \Illuminate\Support\Collection|null
     */
    public function getRelationFromLoggedInUserIfPossible($desired_relation)
    {
        return $this->getRelationalDataAndModels(auth()->user(), $desired_relation);
    }

    /**
     * This will remove the common endings of relationships (like _id).
     *
     * @param $relation
     *
     * @return string
     */
    public function trimCommonRelationEndings($relation)
    {
        if (stripos($relation, '_id') !== false) {
            return trim($relation, '_id');
        }

        return $relation;
    }

    /**
     * Check if an item is actually in an array.
     *
     * @param $needle
     * @param $haystack
     *
     * @return bool
     */
    protected function in_array($needle, $haystack)
    {
        return count(array_filter($haystack, function ($hay) use ($needle) {
            return $hay === $needle;
        })) > 0;
    }

    /**
     * This should...
     *
     * @param $type
     * @param $input
     *
     * @return html
     */
    public function spitOutHtmlForModelInputToConsume($type, $input)
    {
        if ($type === 'select') {
            return $this->select([
                'default_text' => 'Please select a '.trim($input, '_id'),
                'type'         => $type,
                'name'         => $input,
            ], [
                false => 'No',
                true  => 'Yes',
            ]);
        } elseif (in_array($type, ['password', 'email', 'date', 'number'])) {
            return $this->input([
                'type'  => $type,
                'name'  => $input,
                'class' => 'form-control',
                'id'    => $this->genId($input),
            ]);
        }

        return $this->textarea(['type' => $type, 'name' => $input, 'id' => $this->genId($input)],
            (!empty($this->model->$input) && !(stripos($input,
                        'password') !== false)) ? $this->model->$input : '');
    }
}
