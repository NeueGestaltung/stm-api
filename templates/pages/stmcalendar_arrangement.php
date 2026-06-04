
<?php
$deMonths = [
    1 => 'Januar', 'Februar', 'März', 'April', 'Mai', 'Juni',
    'Juli', 'August', 'September', 'Oktober', 'November', 'Dezember',
];

$upcomingEvents = $page->children()
    ->sortBy('date', 'asc')
    ->filter(fn ($e) => ($ts = $e->date()->toDate()) && date('Y-m-d', $ts) >= date('Y-m-d'));
?>
<?php snippet('layout', slots: true) ?>
<?php slot() ?>

<article class="arrangement">

  <header class="arrangement__header">
    <h1 class="arrangement__title"><?= $page->title()->html() ?></h1>
    <?php if ($page->untertitel()->isNotEmpty()): ?>
      <p class="arrangement__subtitle"><?= $page->untertitel()->html() ?></p>
    <?php endif ?>
    <?php if ($page->genre()->isNotEmpty()): ?>
      <p class="arrangement__genre"><?= $page->genre()->html() ?></p>
    <?php endif ?>
  </header>

  <div class="arrangement__columns">

    <!-- ── Left column: editorial content ─────────────────────── -->
    <div class="arrangement__main">

      <?php if ($page->beschreibung()->isNotEmpty()): ?>
        <section class="arrangement__section">
          <h2>Beschreibung</h2>
          <?= $page->beschreibung()->kt() ?>
        </section>
      <?php endif ?>

      <?php if ($page->besetzung()->isNotEmpty()): ?>
        <section class="arrangement__section">
          <h2>Besetzung</h2>
          <dl class="arrangement__cast">
            <?php foreach ($page->besetzung()->toStructure() as $entry): ?>
              <div class="arrangement__cast__row">
                <dt><?= $entry->rolle()->html() ?></dt>
                <dd><?= $entry->darsteller()->html() ?></dd>
              </div>
            <?php endforeach ?>
          </dl>
        </section>
      <?php endif ?>

      <?php if ($page->credits()->isNotEmpty()): ?>
        <section class="arrangement__section">
          <h2>Credits</h2>
          <?= $page->credits()->kt() ?>
        </section>
      <?php endif ?>

    </div>

    <!-- ── Right column: upcoming dates ───────────────────────── -->
    <aside class="arrangement__sidebar">

      <h2>Termine</h2>

      <?php if ($upcomingEvents->isEmpty()): ?>
        <p class="arrangement__no-dates">Derzeit keine Termine geplant.</p>
      <?php else: ?>
        <ul class="arrangement__dates">
          <?php foreach ($upcomingEvents as $event): ?>
            <?php
              $kapazitaet   = (int) $event->kapazitaet()->value();
              $freieplaetze = (int) $event->freieplaetze()->value();
              $apiStatus    = (int) $event->apistatus()->value();

              if ($apiStatus === 0 || $freieplaetze === 0) {
                  $ticketStatus = 'sold-out';
              } elseif ($apiStatus === 1 || ($kapazitaet > 0 && $freieplaetze / $kapazitaet < 0.1)) {
                  $ticketStatus = 'low';
              } else {
                  $ticketStatus = 'available';
              }
            ?>
            <li class="arrangement__date arrangement__date--<?= $ticketStatus ?>">
              <?php $ts = $event->date()->toDate(); ?>
              <div class="arrangement__date__info">
                <span class="arrangement__date__day">
                  <?= $ts ? date('j', $ts) . '. ' . $deMonths[(int)date('n', $ts)] . ' ' . date('Y', $ts) : '' ?>
                </span>
                <span class="arrangement__date__time">
                  <?= $event->time()->html() ?> Uhr
                </span>
                <?php if ($event->einlass()->isNotEmpty()): ?>
                  <span class="arrangement__date__einlass">
                    Einlass: <?= $event->einlass()->html() ?> Uhr
                  </span>
                <?php endif ?>
                <span class="arrangement__date__venue">
                  <?= $event->spielort()->html() ?>
                </span>
              </div>

              <div class="arrangement__date__ticket">
                <?php if ($ticketStatus === 'sold-out'): ?>
                  <span class="arrangement__badge arrangement__badge--sold-out">Ausverkauft</span>
                <?php elseif ($ticketStatus === 'low'): ?>
                  <span class="arrangement__badge arrangement__badge--low">Letzte Tickets</span>
                <?php endif ?>

                <?php if ($event->ticketlink()->isNotEmpty() && $ticketStatus !== 'sold-out'): ?>
                  <a
                    href="<?= $event->ticketlink()->html() ?>"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="arrangement__ticket-btn"
                  >Tickets</a>
                <?php endif ?>
              </div>
            </li>
          <?php endforeach ?>
        </ul>
      <?php endif ?>

    </aside>

  </div>

</article>

<?php endslot() ?>
<?php endsnippet() ?>


<article class="arrangement">

  <header class="arrangement__header">
    <h1 class="arrangement__title"><?= $page->title()->html() ?></h1>
    <?php if ($page->untertitel()->isNotEmpty()): ?>
      <p class="arrangement__subtitle"><?= $page->untertitel()->html() ?></p>
    <?php endif ?>
    <?php if ($page->genre()->isNotEmpty()): ?>
      <p class="arrangement__genre"><?= $page->genre()->html() ?></p>
    <?php endif ?>
  </header>

  <div class="arrangement__columns">

    <!-- ── Left column: editorial content ─────────────────────── -->
    <div class="arrangement__main">

      <?php if ($page->beschreibung()->isNotEmpty()): ?>
        <section class="arrangement__section">
          <h2>Beschreibung</h2>
          <?= $page->beschreibung()->kt() ?>
        </section>
      <?php endif ?>

      <?php if ($page->besetzung()->isNotEmpty()): ?>
        <section class="arrangement__section">
          <h2>Besetzung</h2>
          <dl class="arrangement__cast">
            <?php foreach ($page->besetzung()->toStructure() as $entry): ?>
              <div class="arrangement__cast__row">
                <dt><?= $entry->rolle()->html() ?></dt>
                <dd><?= $entry->darsteller()->html() ?></dd>
              </div>
            <?php endforeach ?>
          </dl>
        </section>
      <?php endif ?>

      <?php if ($page->credits()->isNotEmpty()): ?>
        <section class="arrangement__section">
          <h2>Credits</h2>
          <?= $page->credits()->kt() ?>
        </section>
      <?php endif ?>

    </div>

    <!-- ── Right column: upcoming dates ───────────────────────── -->
    <aside class="arrangement__sidebar">

      <h2>Termine</h2>

      <?php if ($upcomingEvents->isEmpty()): ?>
        <p class="arrangement__no-dates">Derzeit keine Termine geplant.</p>
      <?php else: ?>
        <ul class="arrangement__dates">
          <?php foreach ($upcomingEvents as $event): ?>
            <?php
              $kapazitaet   = (int) $event->kapazitaet()->value();
              $freieplaetze = (int) $event->freieplaetze()->value();
              $apiStatus    = (int) $event->apistatus()->value();

              if ($apiStatus === 0 || $freieplaetze === 0) {
                  $ticketStatus = 'sold-out';
              } elseif ($apiStatus === 1 || ($kapazitaet > 0 && $freieplaetze / $kapazitaet < 0.1)) {
                  $ticketStatus = 'low';
              } else {
                  $ticketStatus = 'available';
              }
            ?>
            <li class="arrangement__date arrangement__date--<?= $ticketStatus ?>">
              <?php $ts = $event->date()->toDate(); ?>
              <div class="arrangement__date__info">
                <span class="arrangement__date__day">
                  <?= $ts ? date('j', $ts) . '. ' . $deMonths[(int)date('n', $ts)] . ' ' . date('Y', $ts) : '' ?>
                </span>
                <span class="arrangement__date__time">
                  <?= $event->time()->html() ?> Uhr
                </span>
                <?php if ($event->einlass()->isNotEmpty()): ?>
                  <span class="arrangement__date__einlass">
                    Einlass: <?= $event->einlass()->html() ?> Uhr
                  </span>
                <?php endif ?>
                <span class="arrangement__date__venue">
                  <?= $event->spielort()->html() ?>
                </span>
              </div>

              <div class="arrangement__date__ticket">
                <?php if ($ticketStatus === 'sold-out'): ?>
                  <span class="arrangement__badge arrangement__badge--sold-out">Ausverkauft</span>
                <?php elseif ($ticketStatus === 'low'): ?>
                  <span class="arrangement__badge arrangement__badge--low">Letzte Tickets</span>
                <?php endif ?>

                <?php if ($event->ticketlink()->isNotEmpty() && $ticketStatus !== 'sold-out'): ?>
                  <a
                    href="<?= $event->ticketlink()->html() ?>"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="arrangement__ticket-btn"
                  >Tickets</a>
                <?php endif ?>
              </div>
            </li>
          <?php endforeach ?>
        </ul>
      <?php endif ?>

    </aside>

  </div>

</article>

</body>
</html>
