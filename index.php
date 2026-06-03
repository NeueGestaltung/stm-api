<?php

Kirby::plugin('stm/stm-api', [
  'blueprints' => [
    'blocks/stmcalendar' => __DIR__ . '/blueprints/blocks/stmcalendar.yml',
  ],
  'snippets' => [
    'blocks/stmcalendar' => __DIR__ . '/snippets/blocks/stmcalendar.php',
  ],
]);