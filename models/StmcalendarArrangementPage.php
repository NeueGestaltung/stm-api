<?php

use Kirby\Cms\Page;
use Kirby\Cms\Pages;

class StmcalendarArrangementPage extends Page
{
    public function children(): Pages
    {
        if ($this->children instanceof Pages) {
            return $this->children;
        }

        $eventimIds = $this->eventimid()->split();

        if (empty($eventimIds)) {
            return $this->children = new Pages();
        }

        // Re-use the shared cache so no extra upstream request is needed
        $cache  = kirby()->cache('stm.stm-api');
        $xmlStr = $cache->get('eventim-xml');

        if ($xmlStr === null) {
            $remote = \Kirby\Http\Remote::get(
                'https://ticket.staatstheater-mainz.de/eventim.webshop/export/export',
                ['timeout' => 10]
            );

            if ($remote->code() !== 200) {
                return $this->children = new Pages();
            }

            $xmlStr = $remote->content();
            $cache->set('eventim-xml', $xmlStr, 5);
        }

        $xml   = simplexml_load_string($xmlStr);
        $pages = new Pages();

        foreach ($xml->veranstaltung ?? [] as $v) {
            $id = (string) ($v->attributes()['id'] ?? '');

            if ($id === '' || !in_array($id, $eventimIds, true)) {
                continue;
            }

            // Parse date: DD.MM.YYYY → YYYY-MM-DD
            $datum    = trim((string) ($v->datum ?? ''));
            [$d, $mo, $y] = explode('.', $datum . '..');
            $date     = "{$y}-{$mo}-{$d}";

            // Parse begin time: HHMM → HH:MM
            $rawTime = str_pad(trim((string) ($v->veranstaltungsbeginn ?? '')), 4, '0', STR_PAD_LEFT);
            $time    = substr($rawTime, 0, 2) . ':' . substr($rawTime, 2);

            // Parse Einlass time (optional)
            $rawEinlass = trim((string) ($v->einlass ?? ''));
            $einlass    = '';
            if ($rawEinlass !== '') {
                $p       = str_pad($rawEinlass, 4, '0', STR_PAD_LEFT);
                $einlass = substr($p, 0, 2) . ':' . substr($p, 2);
            }

            // Slug: eventimid + date so the same ID on different days gets unique slugs
            $slug = $id . '-' . str_replace('-', '', $date);

            $page = Page::factory([
                'slug'     => $slug,
                'template' => 'stmcalendar_arrangement_event',
                'model'    => 'stmcalendar_arrangement_event',
                'parent'   => $this,
                'num'      => 0,
                'content'  => [
                    'title'        => $date . " - " . trim((string) ($v->titel ?? $id)),
                    'eventimid'    => $id,
                    'date'         => $date,
                    'time'         => $time,
                    'einlass'      => $einlass,
                    'spielort'     => trim((string) ($v->spielort ?? '')),
                    'untertitel'   => trim((string) ($v->untertitel ?? '')),
                    'genre'        => trim((string) ($v->genre ?? '')),
                    'veranstalter' => trim((string) ($v->veranstalter ?? '')),
                    'ticketlink'   => trim((string) ($v->ticketlink ?? $v->vorverkauf ?? '')),
                    'kapazitaet'   => trim((string) ($v->kapazitaet ?? '')),
                    'freieplaetze' => trim((string) ($v->absolutfreieplaetze ?? '')),
                    'apistatus'    => trim((string) ($v->status ?? '')),
                ],
            ]);

            $pages->add($page);
        }

        return $this->children = $pages;
    }
}
