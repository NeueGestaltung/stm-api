import "./index.css";
import calendar from "./components/blocks/calendar.vue";

panel.plugin("stm/stm-api", {
  blocks: {
    stmcalendar: calendar,
  },
});

