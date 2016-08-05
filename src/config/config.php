<?php

return [
    'using' => [
        'csrf'             => true,
        'framework'        => 'bootstrap',
        'custom-framework' => function () {
            return new Path\To\My\Framework();
        },
    ],
    /*
     * @param String
     * @return Collection
     */
    'resolved_relationship' => function ($class_name) {
        return $class_name::limit(15)->get();
    },
];
