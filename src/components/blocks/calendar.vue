<template>
  <div class="stm-cal">

    <!-- Header -->
    <div class="stm-cal__header">
      <k-button icon="angle-left" @click="prevMonth" />
      <k-headline size="medium">{{ monthLabel }}</k-headline>
      <k-button icon="angle-right" @click="nextMonth" />
    </div>

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
          </div>
        </div>
      </div>

    </div>
  </div>
</template>

<script>
export default {
  data() {
    const now = new Date();
    const y = now.getFullYear();
    const m = now.getMonth();
    const pad = (y, m, d) => `${y}-${String(m + 1).padStart(2, '0')}-${String(d).padStart(2, '0')}`;
    return {
      year: y,
      month: m,
      weekdays: ['Mo', 'Di', 'Mi', 'Do', 'Fr', 'Sa', 'So'],
      events: [
        { id: 1,  date: pad(y, m, 3),  time: '09:00', title: 'Team Standup',       color: '#3b82f6' },
        { id: 2,  date: pad(y, m, 3),  time: '14:00', title: 'Design Review',      color: '#8b5cf6' },
        { id: 3,  date: pad(y, m, 3),  time: '17:30', title: 'Client Call',        color: '#f59e0b' },
        { id: 4,  date: pad(y, m, 7),  time: '10:00', title: 'Sprint Planning',    color: '#3b82f6' },
        { id: 5,  date: pad(y, m, 10), time: '08:30', title: 'Workshop',           color: '#10b981' },
        { id: 6,  date: pad(y, m, 10), time: '13:00', title: 'Lunch & Learn',      color: '#f59e0b' },
        { id: 7,  date: pad(y, m, 15), time: '11:00', title: 'Quarterly Review',   color: '#ef4444' },
        { id: 8,  date: pad(y, m, 18), time: '09:00', title: 'Team Standup',       color: '#3b82f6' },
        { id: 9,  date: pad(y, m, 18), time: '15:00', title: 'Product Demo',       color: '#8b5cf6' },
        { id: 10, date: pad(y, m, 22), time: '10:30', title: 'UX Research',        color: '#10b981' },
        { id: 11, date: pad(y, m, 25), time: '09:00', title: 'Team Standup',       color: '#3b82f6' },
        { id: 12, date: pad(y, m, 25), time: '16:00', title: 'Release Planning',   color: '#ef4444' },
        { id: 13, date: pad(y, m, 28), time: '14:00', title: 'Retrospective',      color: '#f59e0b' },
      ],
    };
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

