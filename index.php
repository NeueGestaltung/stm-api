<?php

Kirby::plugin('stm/stm-api', [
  'blueprints' => [
    'blocks/stmcalendar' => __DIR__ . '/blueprints/blocks/stmcalendar.yml',
  ],
  'snippets' => [
    'blocks/stmcalendar' => __DIR__ . '/snippets/blocks/stmcalendar.php',
  ],
  'routes' => [
    [
      'pattern' => 'stm/eventim-export',
      'method'  => 'GET',
      'action'  => function () {
        $remote = \Kirby\Http\Remote::get(
          'https://ticket.staatstheater-mainz.de/eventim.webshop/export/export',
          ['timeout' => 10]
        );

        if ($remote->code() !== 200) {
          return \Kirby\Http\Response::json(['error' => 'Upstream error'], 502);
        }

        return new \Kirby\Http\Response($remote->content(), 'application/xml', 200);
      },
    ],
  ],
]);