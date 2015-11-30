<?php

return [
  'using' => [
    'csrf' => true,
    'framework' => 'bootstrap',
    'custom-framework' => function(){
      return new Name\Of\Your\Framework;
    }
  ],
];
