<?php

return [
    'using' => [
        'csrf' => true,
        'framework' => 'bootstrap',
        'custom-framework' => function () {
            return new Kregel\FormModel\Frameworks\Plain;
        },
    ],
    /*
     * @param String
     * @return Collection
     */
    'resolved_relationship' => function ($class_name, $desired_relations) {
        try {
            $relations = $class_name::with($desired_relations)->
            limit(50)->get()->flatMap(function ($e) use ($desired_relations) {
                if (!empty($e->$desired_relations)) {
                    return ($e->$desired_relations->pluck(method_exists($e->$desired_relations, 'getFormModel') ? $e->$desired_relations->getFormModel() : 'name'));
                }
            })->unique();
        } catch (\Exception $e) {
            $relations = $class_name::orderBy($desired_relations . '_id')->groupBy($desired_relations.'_id')->get()->map(function ($el) use ($desired_relations){
                if(!empty($el->{$desired_relations . '_id'}))
                    return $el->{$desired_relations . '_id'};
                return $el->name;
            });
        }
        return $relations;
    },
];
