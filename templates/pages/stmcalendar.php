<?php
/**
 * Template: stmcalendar
 * Spielplan overview — ALL events from XML feed, grouped by month.
 * Events with a matching Kirby arrangement page get a link.
 */

$today = date('Y-m-d');

$deMonths = [
    1 => 'Januar', 'Februar', 'März', 'April', 'Mai', 'Juni',
    'Juli', 'August', 'September', 'Oktober', 'November', 'Dezember',
];

$formatDate = function (int $ts) use ($deMonths): string {
    return date('j', $ts) . '. ' . $deMonths[(int) date('n', $ts)] . ' ' . date('Y', $ts);
};

$formatMonth = function (int $ts) use ($deMonths): string {
    return $deMonths[(int) date('n', $ts)] . ' ' . date('Y', $ts);
};

// ── Build eventimid → arrangement page lookup ────────────────────────────────
$arrangementByEventimId = [];
foreach ($page->children() as $arrangement) {
    foreach ($arrangement->eventimid()->split() as $eid) {
        $arrangementByEventimId[$eid] = [
            'url'   => $arrangement->url(),
            'title' => $arrangement->title()->value(),
        ];
    }
}

// ── Fetch XML (shared plugin cache, 5 min TTL) ───────────────────────────────
$cache  = kirby()->cache('stm.stm-api');
$xmlStr = $cache->get('eventim-xml');

if ($xmlStr === null) {
    $remote = \Kirby\Http\Remote::get(
        'https://ticket.staatstheater-mainz.de/eventim.webshop/export/export',
        ['timeout' => 10]
    );
    if ($remote->code() === 200) {
        $xmlStr = $remote->content();
        $cache->set('eventim-xml', $xmlStr, 5);
    }
}

// ── Parse all events ─────────────────────────────────────────────────────────
$allEvents = [];

if ($xmlStr) {
    $xml = simplexml_load_string($xmlStr);

    foreach ($xml->veranstaltung ?? [] as $v) {
        $id = (string) ($v->attributes()['id'] ?? '');
        if ($id === '') continue;

        // Date: DD.MM.YYYY → timestamp
        $datum = trim((string) ($v->datum ?? ''));
        [$d, $mo, $y] = explode('.', $datum . '..');
        $ts = mktime(0, 0, 0, (int) $mo, (int) $d, (int) $y);

        if (!$ts || date('Y-m-d', $ts) < $today) continue;

        // Time: HHMM → HH:MM
        $rawTime = str_pad(trim((string) ($v->veranstaltungsbeginn ?? '')), 4, '0', STR_PAD_LEFT);
        $time    = substr($rawTime, 0, 2) . ':' . substr($rawTime, 2);

        // Einlass (optional)
        $rawEinlass = trim((string) ($v->einlass ?? ''));
        $einlass    = '';
        if ($rawEinlass !== '') {
            $p       = str_pad($rawEinlass, 4, '0', STR_PAD_LEFT);
            $einlass = substr($p, 0, 2) . ':' . substr($p, 2);
        }

        // Ticket status
        $kapazitaet   = (int) ($v->kapazitaet ?? 0);
        $freieplaetze = (int) ($v->absolutfreieplaetze ?? 0);
        $apiStatus    = (int) ($v->status ?? 2);

        if ($apiStatus === 0 || $freieplaetze === 0) {
            $ticketStatus = 'sold-out';
        } elseif ($apiStatus === 1 || ($kapazitaet > 0 && $freieplaetze / $kapazitaet < 0.1)) {
            $ticketStatus = 'low';
        } else {
            $ticketStatus = 'available';
        }

        $arrangement = $arrangementByEventimId[$id] ?? null;

        $allEvents[] = [
            'ts'           => $ts,
            'date'         => date('Y-m-d', $ts),
            'dateLabel'    => $formatDate($ts),
            'monthKey'     => date('Y-m', $ts),
            'monthLabel'   => $formatMonth($ts),
            'time'         => $time,
            'einlass'      => $einlass,
            'spielort'     => trim((string) ($v->spielort ?? '')),
            'title'        => trim((string) ($v->titel ?? $id)),
            'untertitel'   => trim((string) ($v->untertitel ?? '')),
            'genre'        => trim((string) ($v->genre ?? '')),
            'ticketlink'   => trim((string) ($v->ticketlink ?? $v->vorverkauf ?? '')),
            'ticketStatus' => $ticketStatus,
            'pageUrl'      => $arrangement['url'] ?? null,
        ];
    }
}

// Sort by date + time
usort($allEvents, fn ($a, $b) => ($a['date'] . $a['time']) <=> ($b['date'] . $b['time']));

// Group by month
$byMonth = [];
foreach ($allEvents as $event) {
    $byMonth[$event['monthKey']][] = $event;
}
?>
<?php snippet('layout', slots: true) ?>
<?php slot() ?>

<main class="spielplan">

  <header class="spielplan__header">
    <h1><?= $page->title()->html() ?></h1>
  </header>

  <?php if (empty($byMonth)): ?>
    <p class="spielplan__empty">Derzeit sind keine Veranstaltungen geplant.</p>
  <?php else: ?>

    <?php foreach ($byMonth as $monthKey => $events): ?>
      <section class="spielplan__month">

        <h2 class="spielplan__month__label"><?= html($events[0]['monthLabel']) ?></h2>

        <ul class="spielplan__events">
          <?php foreach ($events as $e): ?>
            <li class="spielplan__event spielplan__event--<?= $e['ticketStatus'] ?>">

              <div class="spielplan__event__date">
                <span class="spielplan__event__day"><?= html($e['dateLabel']) ?></span>
                <span class="spielplan__event__time"><?= html($e['time']) ?> Uhr</span>
                <?php if ($e['einlass'] !== ''): ?>
                  <span class="spielplan__event__einlass">Einlass <?= html($e['einlass']) ?> Uhr</span>
                <?php endif ?>
              </div>

              <div class="spielplan__event__info">
                <?php if ($e['pageUrl']): ?>
                  <a href="<?= html($e['pageUrl']) ?>" class="spielplan__event__title">
                    <?= html($e['title']) ?>
                  </a>
                <?php else: ?>
                  <span class="spielplan__event__title"><?= html($e['title']) ?></span>
                <?php endif ?>
                <?php if ($e['untertitel'] !== ''): ?>
                  <span class="spielplan__event__subtitle"><?= html($e['untertitel']) ?></span>
                <?php endif ?>
                <?php if ($e['genre'] !== ''): ?>
                  <span class="spielplan__event__genre"><?= html($e['genre']) ?></span>
                <?php endif ?>
                <span class="spielplan__event__venue"><?= html($e['spielort']) ?></span>
              </div>

              <div class="spielplan__event__ticket">
                <?php if ($e['ticketStatus'] === 'sold-out'): ?>
                  <span class="spielplan__badge spielplan__badge--sold-out">Ausverkauft</span>
                <?php elseif ($e['ticketStatus'] === 'low'): ?>
                  <span class="spielplan__badge spielplan__badge--low">Letzte Tickets</span>
                <?php endif ?>

                <?php if ($e['ticketlink'] !== '' && $e['ticketStatus'] !== 'sold-out'): ?>
                  <a
                    href="<?= html($e['ticketlink']) ?>"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="spielplan__ticket-btn"
                  >Tickets</a>
                <?php endif ?>
              </div>

            </li>
          <?php endforeach ?>
        </ul>

      </section>
    <?php endforeach ?>

  <?php endif ?>

</main>

<?php endslot() ?>
<?php endsnippet() ?>
