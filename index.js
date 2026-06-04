(function() {
  "use strict";
  function normalizeComponent(scriptExports, render, staticRenderFns, functionalTemplate, injectStyles, scopeId, moduleIdentifier, shadowMode) {
    var options = typeof scriptExports === "function" ? scriptExports.options : scriptExports;
    if (render) {
      options.render = render;
      options.staticRenderFns = staticRenderFns;
      options._compiled = true;
    }
    {
      options._scopeId = "data-v-" + scopeId;
    }
    return {
      exports: scriptExports,
      options
    };
  }
  const VENUE_COLORS = {
    "Großes Haus": "#3b82f6",
    "Kleines Haus": "#8b5cf6",
    "U 17": "#10b981",
    "Orchestersaal": "#f59e0b"
  };
  function generateId() {
    if (typeof crypto !== "undefined" && crypto.randomUUID) {
      return crypto.randomUUID();
    }
    return "xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx".replace(/[xy]/g, (c) => {
      const r = Math.random() * 16 | 0;
      return (c === "x" ? r : r & 3 | 8).toString(16);
    });
  }
  const _sfc_main = {
    data() {
      const now = /* @__PURE__ */ new Date();
      return {
        year: now.getFullYear(),
        month: now.getMonth(),
        weekdays: ["Mo", "Di", "Mi", "Do", "Fr", "Sa", "So"],
        events: [],
        loading: false,
        error: null,
        selectedEvent: null
      };
    },
    mounted() {
      this.fetchEvents();
    },
    computed: {
      monthLabel() {
        return new Intl.DateTimeFormat("de-DE", { month: "long", year: "numeric" }).format(new Date(this.year, this.month, 1));
      },
      daysInMonth() {
        return new Date(this.year, this.month + 1, 0).getDate();
      },
      startOffset() {
        const day = new Date(this.year, this.month, 1).getDay();
        return (day + 6) % 7;
      }
    },
    methods: {
      async fetchEvents() {
        this.loading = true;
        this.error = null;
        try {
          const base = window.location.pathname.replace(/\/panel.*$/, "");
          const res = await fetch(`${base}/stm/eventim-export`);
          const text = await res.text();
          const xml = new DOMParser().parseFromString(text, "text/xml");
          this.events = Array.from(xml.querySelectorAll("veranstaltung")).map((v) => {
            const text2 = (sel) => {
              var _a, _b;
              return ((_b = (_a = v.querySelector(sel)) == null ? void 0 : _a.textContent) == null ? void 0 : _b.trim()) ?? "";
            };
            const datum = text2("datum");
            const [d, mo, y] = datum.split(".");
            const date = `${y}-${mo}-${d}`;
            const raw = text2("veranstaltungsbeginn").padStart(4, "0");
            const time = `${raw.slice(0, 2)}:${raw.slice(2)}`;
            const spielort = text2("spielort");
            const kapazitaet = parseInt(text2("kapazitaet") || "0", 10);
            const freieplaetze = parseInt(text2("absolutfreieplaetze") || "0", 10);
            const apiStatus = parseInt(text2("status") || "2", 10);
            let ticketStatus;
            if (apiStatus === 0 || freieplaetze === 0) {
              ticketStatus = "sold-out";
            } else if (apiStatus === 1 || kapazitaet > 0 && freieplaetze / kapazitaet < 0.1) {
              ticketStatus = "low";
            } else {
              ticketStatus = "available";
            }
            return {
              id: v.getAttribute("id") || generateId(),
              title: text2("titel"),
              date,
              time,
              einlass: (() => {
                const raw2 = text2("einlass");
                if (!raw2) return null;
                const p = raw2.padStart(4, "0");
                return `${p.slice(0, 2)}:${p.slice(2)}`;
              })(),
              untertitel: text2("untertitel") || null,
              genre: text2("genre") || null,
              veranstalter: text2("veranstalter") || null,
              ticketLink: text2("ticketlink") || text2("vorverkauf") || null,
              spielort,
              color: VENUE_COLORS[spielort] ?? "#6b7280",
              ticketStatus,
              freieplaetze,
              kapazitaet
            };
          });
        } catch {
          this.error = "Veranstaltungen konnten nicht geladen werden.";
        } finally {
          this.loading = false;
        }
      },
      pad(y, m, d) {
        return `${y}-${String(m + 1).padStart(2, "0")}-${String(d).padStart(2, "0")}`;
      },
      isToday(day) {
        const now = /* @__PURE__ */ new Date();
        return day === now.getDate() && this.month === now.getMonth() && this.year === now.getFullYear();
      },
      eventsForDay(day) {
        const key = this.pad(this.year, this.month, day);
        return this.events.filter((e) => e.date === key).sort((a, b) => a.time.localeCompare(b.time));
      },
      prevMonth() {
        if (this.month === 0) {
          this.month = 11;
          this.year--;
        } else {
          this.month--;
        }
      },
      nextMonth() {
        if (this.month === 11) {
          this.month = 0;
          this.year++;
        } else {
          this.month++;
        }
      },
      openEventDialog(event) {
        console.log("open!");
        this.selectedEvent = event;
        this.$refs.eventDialog.open();
      },
      formatDate(isoDate) {
        if (!isoDate) return "";
        const [y, m, d] = isoDate.split("-");
        return `${d}.${m}.${y}`;
      }
    }
  };
  var _sfc_render = function render() {
    var _vm = this, _c = _vm._self._c;
    return _c("div", { staticClass: "stm-cal" }, [_c("div", { staticClass: "stm-cal__header" }, [_c("k-button", { attrs: { "icon": "angle-left" }, on: { "click": _vm.prevMonth } }), _c("k-headline", { attrs: { "size": "medium" } }, [_vm._v(_vm._s(_vm.monthLabel))]), _c("k-button", { attrs: { "icon": "angle-right" }, on: { "click": _vm.nextMonth } })], 1), _vm.loading ? _c("div", { staticClass: "stm-cal__loading" }, [_vm._v("Veranstaltungen werden geladen …")]) : _vm.error ? _c("div", { staticClass: "stm-cal__error" }, [_vm._v(_vm._s(_vm.error))]) : _vm._e(), _c("div", { staticClass: "stm-cal__weekdays" }, _vm._l(_vm.weekdays, function(d) {
      return _c("span", { key: d }, [_vm._v(_vm._s(d))]);
    }), 0), _c("div", { staticClass: "stm-cal__grid" }, [_vm._l(_vm.startOffset, function(n) {
      return _c("div", { key: "e" + n, staticClass: "stm-cal__day stm-cal__day--empty" });
    }), _vm._l(_vm.daysInMonth, function(day) {
      return _c("div", { key: day, staticClass: "stm-cal__day", class: { "stm-cal__day--today": _vm.isToday(day) } }, [_c("span", { staticClass: "stm-cal__day__number" }, [_vm._v(_vm._s(day))]), _c("div", { staticClass: "stm-cal__events" }, _vm._l(_vm.eventsForDay(day), function(event) {
        return _c("div", { key: event.id, staticClass: "stm-cal__event", style: { borderLeftColor: event.color }, on: { "click": function($event) {
          return _vm.openEventDialog(event);
        } } }, [_c("span", { staticClass: "stm-cal__event__time" }, [_vm._v(_vm._s(event.time))]), _c("span", { staticClass: "stm-cal__event__title" }, [_vm._v(_vm._s(event.title))]), event.ticketStatus === "sold-out" ? _c("span", { staticClass: "stm-cal__event__status stm-cal__event__status--sold-out" }, [_vm._v("Ausverkauft")]) : event.ticketStatus === "low" ? _c("span", { staticClass: "stm-cal__event__status stm-cal__event__status--low" }, [_vm._v("Letzte Tickets")]) : _vm._e()]);
      }), 0)]);
    })], 2), _c("k-dialog", { ref: "eventDialog", attrs: { "cancel-button": { label: "Schließen" }, "submit-button": false, "size": "medium" } }, [_vm.selectedEvent ? [_c("k-headline", { staticClass: "stm-cal__dialog__headline" }, [_vm._v(_vm._s(_vm.selectedEvent.title))]), _c("k-text", { staticClass: "stm-cal__dialog__body" }, [_c("dl", { staticClass: "stm-cal__dialog__dl" }, [_c("div", { staticClass: "stm-cal__dialog__row" }, [_c("dt", [_vm._v("ID")]), _c("dd", [_c("code", [_vm._v(_vm._s(_vm.selectedEvent.id))])])]), _c("div", { staticClass: "stm-cal__dialog__row" }, [_c("dt", [_vm._v("Datum")]), _c("dd", [_vm._v(_vm._s(_vm.formatDate(_vm.selectedEvent.date)))])]), _c("div", { staticClass: "stm-cal__dialog__row" }, [_c("dt", [_vm._v("Beginn")]), _c("dd", [_vm._v(_vm._s(_vm.selectedEvent.time) + " Uhr")])]), _vm.selectedEvent.einlass ? _c("div", { staticClass: "stm-cal__dialog__row" }, [_c("dt", [_vm._v("Einlass")]), _c("dd", [_vm._v(_vm._s(_vm.selectedEvent.einlass) + " Uhr")])]) : _vm._e(), _c("div", { staticClass: "stm-cal__dialog__row" }, [_c("dt", [_vm._v("Spielort")]), _c("dd", [_vm._v(_vm._s(_vm.selectedEvent.spielort))])]), _vm.selectedEvent.untertitel ? _c("div", { staticClass: "stm-cal__dialog__row" }, [_c("dt", [_vm._v("Untertitel")]), _c("dd", [_vm._v(_vm._s(_vm.selectedEvent.untertitel))])]) : _vm._e(), _vm.selectedEvent.genre ? _c("div", { staticClass: "stm-cal__dialog__row" }, [_c("dt", [_vm._v("Genre")]), _c("dd", [_vm._v(_vm._s(_vm.selectedEvent.genre))])]) : _vm._e(), _vm.selectedEvent.veranstalter ? _c("div", { staticClass: "stm-cal__dialog__row" }, [_c("dt", [_vm._v("Veranstalter")]), _c("dd", [_vm._v(_vm._s(_vm.selectedEvent.veranstalter))])]) : _vm._e(), _c("div", { staticClass: "stm-cal__dialog__row" }, [_c("dt", [_vm._v("Verfügbarkeit")]), _c("dd", [_vm.selectedEvent.ticketStatus === "sold-out" ? _c("span", { staticClass: "stm-cal__event__status stm-cal__event__status--sold-out" }, [_vm._v("Ausverkauft")]) : _vm.selectedEvent.ticketStatus === "low" ? _c("span", { staticClass: "stm-cal__event__status stm-cal__event__status--low" }, [_vm._v("Letzte Tickets")]) : _c("span", [_vm._v(" " + _vm._s(_vm.selectedEvent.freieplaetze) + " / " + _vm._s(_vm.selectedEvent.kapazitaet) + " Plätze frei ")])])]), _vm.selectedEvent.ticketLink ? _c("div", { staticClass: "stm-cal__dialog__row" }, [_c("dt", [_vm._v("Tickets")]), _c("dd", [_c("a", { attrs: { "href": _vm.selectedEvent.ticketLink, "target": "_blank", "rel": "noopener" } }, [_vm._v("Zum Ticketshop")])])]) : _vm._e()])])] : _vm._e()], 2)], 1);
  };
  var _sfc_staticRenderFns = [];
  _sfc_render._withStripped = true;
  var __component__ = /* @__PURE__ */ normalizeComponent(
    _sfc_main,
    _sfc_render,
    _sfc_staticRenderFns,
    false,
    null,
    "4a680808"
  );
  __component__.options.__file = "/Users/fabian/Sites/stm-headless/site/plugins/stm-api/src/components/blocks/calendar.vue";
  const calendar = __component__.exports;
  panel.plugin("stm/stm-api", {
    blocks: {
      stmcalendar: calendar
    }
  });
})();
