<template>
  <div class="stm-cal">

    <!-- Header -->
    <div class="stm-cal__header">
      <k-button icon="angle-left" @click="prevMonth" />
      <k-headline size="medium">{{ monthLabel }}</k-headline>
      <k-button icon="angle-right" @click="nextMonth" />
    </div>

    <!-- Loading / Error -->
    <div v-if="loading" class="stm-cal__loading">Veranstaltungen werden geladen …</div>
    <div v-else-if="error" class="stm-cal__error">{{ error }}</div>

    <!-- Weekday labels -->
    <div class="stm-cal__weekdays">
      <span v-for="d in weekdays" :key="d">{{ d }}</span>
    </div>

    <!-- Day grid -->
    <div class="stm-cal__grid">

      <!-- Empty offset cells -->
      <div
        v-for="n in startOffset"
        :key="'e' + n"
        class="stm-cal__day stm-cal__day--empty"
      />

      <!-- Day cells -->
      <div
        v-for="day in daysInMonth"
        :key="day"
        class="stm-cal__day"
        :class="{ 'stm-cal__day--today': isToday(day) }"
      >
        <span class="stm-cal__day__number">{{ day }}</span>

        <div class="stm-cal__events">
          <div
            v-for="event in eventsForDay(day)"
            :key="event.id"
            class="stm-cal__event"
            :style="{ borderLeftColor: event.color }"
          >
            <span class="stm-cal__event__time">{{ event.time }}</span>
            <span class="stm-cal__event__title">{{ event.title }}</span>
            <span
              v-if="event.ticketStatus === 'sold-out'"
              class="stm-cal__event__status stm-cal__event__status--sold-out"
            >Ausverkauft</span>
            <span
              v-else-if="event.ticketStatus === 'low'"
              class="stm-cal__event__status stm-cal__event__status--low"
            >Letzte Tickets</span>
          </div>
        </div>
      </div>

    </div>
  </div>
</template>

<script>
const VENUE_COLORS = {
  'Großes Haus':  '#3b82f6',
  'Kleines Haus': '#8b5cf6',
  'U 17':         '#10b981',
  'Orchestersaal':'#f59e0b',
};

export default {
  data() {
    const now = new Date();
    return {
      year:     now.getFullYear(),
      month:    now.getMonth(),
      weekdays: ['Mo', 'Di', 'Mi', 'Do', 'Fr', 'Sa', 'So'],
      events:   [],
      loading:  false,
      error:    null,
    };
  },
  mounted() {
    this.fetchEvents();
  },
  computed: {
    monthLabel() {
      return new Intl.DateTimeFormat('de-DE', { month: 'long', year: 'numeric' })
        .format(new Date(this.year, this.month, 1));
    },
    daysInMonth() {
      return new Date(this.year, this.month + 1, 0).getDate();
    },
    startOffset() {
      const day = new Date(this.year, this.month, 1).getDay();
      return (day + 6) % 7; // Monday = 0
    },
  },
  methods: {
    async fetchEvents() {
      this.loading = true;
      this.error   = null;
      try {
        const base = window.location.pathname.replace(/\/panel.*$/, '');
        const res  = await fetch(`${base}/stm/eventim-export`);


        const text = await res.text();
        const xml  = new DOMParser().parseFromString(text, 'text/xml');

        this.events = Array.from(xml.querySelectorAll('veranstaltung')).map(v => {
          const text  = sel => v.querySelector(sel)?.textContent?.trim() ?? '';
          const datum = text('datum');                          // DD.MM.YYYY
          const [d, mo, y] = datum.split('.');
          const date  = `${y}-${mo}-${d}`;

          const raw  = text('veranstaltungsbeginn').padStart(4, '0');
          const time = `${raw.slice(0, 2)}:${raw.slice(2)}`;

          const spielort     = text('spielort');
          const kapazitaet   = parseInt(text('kapazitaet')  || '0', 10);
          const freieplaetze = parseInt(text('absolutfreieplaetze') || '0', 10);
          const apiStatus    = parseInt(text('status') || '2', 10);

          let ticketStatus;
          if (apiStatus === 0 || freieplaetze === 0) {
            ticketStatus = 'sold-out';
          } else if (apiStatus === 1 || (kapazitaet > 0 && freieplaetze / kapazitaet < 0.1)) {
            ticketStatus = 'low';
          } else {
            ticketStatus = 'available';
          }

          return {
            id:           v.getAttribute('id'),
            title:        text('titel'),
            date,
            time,
            spielort,
            color:        VENUE_COLORS[spielort] ?? '#6b7280',
            ticketStatus,
            freieplaetze,
            kapazitaet,
          };
        });
      } catch {
        this.error = 'Veranstaltungen konnten nicht geladen werden.';
      } finally {
        this.loading = false;
      }
    },
    pad(y, m, d) {
      return `${y}-${String(m + 1).padStart(2, '0')}-${String(d).padStart(2, '0')}`;
    },
    isToday(day) {
      const now = new Date();
      return day === now.getDate() && this.month === now.getMonth() && this.year === now.getFullYear();
    },
    eventsForDay(day) {
      const key = this.pad(this.year, this.month, day);
      return this.events
        .filter(e => e.date === key)
        .sort((a, b) => a.time.localeCompare(b.time));
    },
    prevMonth() {
      if (this.month === 0) { this.month = 11; this.year--; }
      else { this.month--; }
    },
    nextMonth() {
      if (this.month === 11) { this.month = 0; this.year++; }
      else { this.month++; }
    },
  },
};
</script>

<style scoped>
.stm-cal__loading,
.stm-cal__error {
  padding: .5rem 0;
  font-size: .85rem;
  color: #6b7280;
}
.stm-cal__error { color: #ef4444; }

.stm-cal__event__status {
  display: inline-block;
  margin-top: 2px;
  padding: 1px 5px;
  border-radius: 3px;
  font-size: .7rem;
  font-weight: 600;
  line-height: 1.4;
}
.stm-cal__event__status--sold-out {
  background: #fee2e2;
  color: #b91c1c;
}
.stm-cal__event__status--low {
  background: #fef3c7;
  color: #b45309;
}
</style>

