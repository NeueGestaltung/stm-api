<?php

Kirby::plugin('stm/stm-api', [
  'blueprints' => [
    'pages/stmcalendar' => __DIR__ . '/blueprints/pages/stmcalendar.yml',
    'pages/stmcalendar_arrangement' => __DIR__ . '/blueprints/pages/stmcalendar_arrangement.yml',
    'blocks/stmcalendar' => __DIR__ . '/blueprints/blocks/stmcalendar.yml',
  ],
  'snippets' => [
    'blocks/stmcalendar' => __DIR__ . '/snippets/blocks/stmcalendar.php',
    ],
  'templates' => [
    'pages/stmcalendar_arrangement' => __DIR__ . '/pages/stmcalendar_arrangement.php',
    'pages/stmcalendar' => __DIR__ . '/pages/stmcalendar.php',
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
    [
      'pattern' => 'stm/eventim-ids',
      'method'  => 'GET',
      'action'  => function () {
        $remote = \Kirby\Http\Remote::get(
          'https://ticket.staatstheater-mainz.de/eventim.webshop/export/export',
          ['timeout' => 10]
        );

        if ($remote->code() !== 200) {
          return \Kirby\Http\Response::json([], 200);
        }

        $xml    = simplexml_load_string($remote->content());
        $result = [];

        foreach ($xml->veranstaltung ?? [] as $v) {
          $id    = (string) ($v->attributes()['id'] ?? '');
          $titel = trim((string) ($v->titel ?? $id));

          if ($id === '') {
            continue;
          }

          $result[] = [
            'value' => $id,
            'text'  => $titel ? "{$id} – {$titel}" : $id,
          ];
        }

        // deduplicate by value
        $seen   = [];
        $unique = [];
        foreach ($result as $item) {
          if (!isset($seen[$item['value']])) {
            $seen[$item['value']] = true;
            $unique[]             = $item;
          }
        }

        return \Kirby\Http\Response::json($unique, 200);
      },
    ],
  ],
]);