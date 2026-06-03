(function() {
  "use strict";
  function normalizeComponent(scriptExports, render, staticRenderFns, functionalTemplate, injectStyles, scopeId, moduleIdentifier, shadowMode) {
    var options = typeof scriptExports === "function" ? scriptExports.options : scriptExports;
    if (render) {
      options.render = render;
      options.staticRenderFns = staticRenderFns;
      options._compiled = true;
    }
    return {
      exports: scriptExports,
      options
    };
  }
  const _sfc_main = {
    data() {
      const now = /* @__PURE__ */ new Date();
      const y = now.getFullYear();
      const m = now.getMonth();
      const pad = (y2, m2, d) => `${y2}-${String(m2 + 1).padStart(2, "0")}-${String(d).padStart(2, "0")}`;
      return {
        year: y,
        month: m,
        weekdays: ["Mo", "Di", "Mi", "Do", "Fr", "Sa", "So"],
        events: [
          { id: 1, date: pad(y, m, 3), time: "09:00", title: "Team Standup", color: "#3b82f6" },
          { id: 2, date: pad(y, m, 3), time: "14:00", title: "Design Review", color: "#8b5cf6" },
          { id: 3, date: pad(y, m, 3), time: "17:30", title: "Client Call", color: "#f59e0b" },
          { id: 4, date: pad(y, m, 7), time: "10:00", title: "Sprint Planning", color: "#3b82f6" },
          { id: 5, date: pad(y, m, 10), time: "08:30", title: "Workshop", color: "#10b981" },
          { id: 6, date: pad(y, m, 10), time: "13:00", title: "Lunch & Learn", color: "#f59e0b" },
          { id: 7, date: pad(y, m, 15), time: "11:00", title: "Quarterly Review", color: "#ef4444" },
          { id: 8, date: pad(y, m, 18), time: "09:00", title: "Team Standup", color: "#3b82f6" },
          { id: 9, date: pad(y, m, 18), time: "15:00", title: "Product Demo", color: "#8b5cf6" },
          { id: 10, date: pad(y, m, 22), time: "10:30", title: "UX Research", color: "#10b981" },
          { id: 11, date: pad(y, m, 25), time: "09:00", title: "Team Standup", color: "#3b82f6" },
          { id: 12, date: pad(y, m, 25), time: "16:00", title: "Release Planning", color: "#ef4444" },
          { id: 13, date: pad(y, m, 28), time: "14:00", title: "Retrospective", color: "#f59e0b" }
        ]
      };
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
      }
    }
  };
  var _sfc_render = function render() {
    var _vm = this, _c = _vm._self._c;
    return _c("div", { staticClass: "stm-cal" }, [_c("div", { staticClass: "stm-cal__header" }, [_c("k-button", { attrs: { "icon": "angle-left" }, on: { "click": _vm.prevMonth } }), _c("k-headline", { attrs: { "size": "medium" } }, [_vm._v(_vm._s(_vm.monthLabel))]), _c("k-button", { attrs: { "icon": "angle-right" }, on: { "click": _vm.nextMonth } })], 1), _c("div", { staticClass: "stm-cal__weekdays" }, _vm._l(_vm.weekdays, function(d) {
      return _c("span", { key: d }, [_vm._v(_vm._s(d))]);
    }), 0), _c("div", { staticClass: "stm-cal__grid" }, [_vm._l(_vm.startOffset, function(n) {
      return _c("div", { key: "e" + n, staticClass: "stm-cal__day stm-cal__day--empty" });
    }), _vm._l(_vm.daysInMonth, function(day) {
      return _c("div", { key: day, staticClass: "stm-cal__day", class: { "stm-cal__day--today": _vm.isToday(day) } }, [_c("span", { staticClass: "stm-cal__day__number" }, [_vm._v(_vm._s(day))]), _c("div", { staticClass: "stm-cal__events" }, _vm._l(_vm.eventsForDay(day), function(event) {
        return _c("div", { key: event.id, staticClass: "stm-cal__event", style: { borderLeftColor: event.color } }, [_c("span", { staticClass: "stm-cal__event__time" }, [_vm._v(_vm._s(event.time))]), _c("span", { staticClass: "stm-cal__event__title" }, [_vm._v(_vm._s(event.title))])]);
      }), 0)]);
    })], 2)]);
  };
  var _sfc_staticRenderFns = [];
  _sfc_render._withStripped = true;
  var __component__ = /* @__PURE__ */ normalizeComponent(
    _sfc_main,
    _sfc_render,
    _sfc_staticRenderFns
  );
  __component__.options.__file = "/Users/fabian/Sites/stm-headless/site/plugins/stm-api/src/components/blocks/calendar.vue";
  const calendar = __component__.exports;
  panel.plugin("stm/stm-api", {
    blocks: {
      stmcalendar: calendar
    }
  });
})();
