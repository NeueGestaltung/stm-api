<?php

Kirby::plugin('stm/stm-api', [
  'options' => [
    'cache' => true,
  ],
  'collections' => [
    'arrangements' => function ($kirby) {
      return $kirby->site()->index()->filterBy('intendedTemplate', 'stmcalendar_arrangement');
    },
  ],
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
        $cache = kirby()->cache('stm.stm-api');
        $xml   = $cache->get('eventim-xml');

        if ($xml === null) {
          $remote = \Kirby\Http\Remote::get(
            'https://ticket.staatstheater-mainz.de/eventim.webshop/export/export',
            ['timeout' => 10]
          );

          if ($remote->code() !== 200) {
            return \Kirby\Http\Response::json(['error' => 'Upstream error'], 502);
          }

          $xml = $remote->content();
          $cache->set('eventim-xml', $xml, 5);
        }

        return new \Kirby\Http\Response($xml, 'application/xml', 200);
      },
    ],
    [
      'pattern' => 'stm/page-for-event/(:any)',
      'method'  => 'GET',
      'action'  => function (string $id) {
        $pages = kirby()->collection('arrangements');
        foreach ($pages as $page) {
          if (in_array($id, $page->eventimid()->split(), true)) {
            return \Kirby\Http\Response::json([
              'url'   => $page->panel()->url(),
              'title' => $page->title()->value(),
            ], 200);
          }
        }
        return \Kirby\Http\Response::json(null, 200);
      },
    ],
    [
      'pattern' => 'stm/eventim-ids',
      'method'  => 'GET',
      'action'  => function () {
        $cache = kirby()->cache('stm.stm-api');
        $xml   = $cache->get('eventim-xml');

        if ($xml === null) {
          $remote = \Kirby\Http\Remote::get(
            'https://ticket.staatstheater-mainz.de/eventim.webshop/export/export',
            ['timeout' => 10]
          );

          if ($remote->code() !== 200) {
            return \Kirby\Http\Response::json([], 200);
          }

          $xml = $remote->content();
          $cache->set('eventim-xml', $xml, 5);
        }

        $xmlObj = simplexml_load_string($xml);
        $result = [];

        foreach ($xmlObj->veranstaltung ?? [] as $v) {
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