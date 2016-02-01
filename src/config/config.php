<?php

return [
  'using' => [
    'csrf' => true,
    'framework' => 'bootstrap',
    'custom-framework' => function () {
      return new Path\To\My\Framework();
    },
  ],
];
