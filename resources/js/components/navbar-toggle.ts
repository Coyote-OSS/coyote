import axios from 'axios';

import clickAway from "../clickAway.js";
import VueIcon from "../components/icon";
import store from "../store/index";
import {createVueApp} from "../vue";

Array
  .from(document.querySelectorAll('.disable-theme-notice'))
  .forEach(button => {
    button.addEventListener('click', function () {
      Array.from(document.querySelectorAll('div.theme-notice'))
        .forEach((div: Element) => {
          (div as HTMLElement).style.display = 'none';
        });
      setViewTheme(false);
      storeThemeSetting('light', false);
    });
  });

function storeThemeSetting(colorScheme: Theme, dark: boolean): void {
  axios.post('/User/Settings/Ajax', {
    colorScheme: colorScheme,
    lastColorScheme: dark ? 'dark' : 'light',
  });
}

function setViewTheme(isDark: boolean): void {
  setBodyTheme(isDark);
  setBootstrapNavigationBarTheme(isDark);
  store.commit('theme/CHANGE_THEME', isDark);
}

function setBodyTheme(isDark: boolean): void {
  document.querySelector('html')!.classList.toggle('theme-dark', isDark);
  document.querySelector('html')!.classList.toggle('theme-light', !isDark);
  document.body.classList.toggle('theme-dark', isDark);
  document.body.classList.toggle('theme-light', !isDark);
}

function setBootstrapNavigationBarTheme(isDark: boolean): void {
  const header: Element = document.getElementsByClassName('navbar')[0];
  if (header) {
    header.classList.toggle('navbar-dark', isDark);
    header.classList.toggle('navbar-light', !isDark);
  }
}

const isDarkTheme = document.body.classList.contains('theme-dark');
const isDarkThemeWip = document.body.classList.contains('theme-dark-wip');

let colorScheme = document.body.dataset.colorScheme as Theme;
if (isDarkThemeWip) {
  colorScheme = 'light';
}

const VueToggleIcon = {
  props: ['icon', 'className'],
  components: {VueIcon},
  data() {
    return {
      icons: {
        'dark-theme': 'themeToggleDark',
        'light-theme': 'themeToggleLight',
        'system-theme': 'themeToggleSystem',
      },
    };
  },
  template: '<vue-icon :name="icons[this.$props.icon]"/>',
};

function systemColorSchemeDark(): boolean {
  return window.matchMedia && window.matchMedia('(prefers-color-scheme:dark)').matches;
}

function currentTheme(): boolean {
  if (colorScheme === 'system') {
    return systemColorSchemeDark();
  }
  return colorScheme === 'dark';
}

let dark = currentTheme();
if (dark !== isDarkTheme) {
  storeThemeSetting(colorScheme, dark);
}

if (!isDarkThemeWip) {
  setViewTheme(dark);
}

type Theme = 'light' | 'dark' | 'system';

createVueApp('NonAlertControls', '#non-alert-controls', {
  components: {
    'vue-toggle-icon': VueToggleIcon,
  },
  data() {
    return {
      toggleEnabled: !isDarkThemeWip,
      open: false,
      theme: colorScheme,
      systemColorSchemeDark: systemColorSchemeDark(),
      items: {
        light: {title: 'Jasny motyw', icon: 'light-theme'},
        dark: {title: 'Ciemny motyw', icon: 'dark-theme'},
        system: {title: 'Systemowy:', icon: 'system-theme'},
      },
    };
  },
  mounted() {
    window.matchMedia('(prefers-color-scheme:dark)')
      .addEventListener('change', (event: MediaQueryListEvent): void => {
        this.$data.systemColorSchemeDark = event.matches;
      });
  },
  directives: {clickAway},
  computed: {
    dark(): boolean {
      if (this.theme === 'system') {
        return this.systemColorSchemeDark;
      }
      return this.theme === 'dark';
    },
    oppositeIcon(): string {
      return this.dark ? 'light-theme' : 'dark-theme';
    },
    systemThemeTitle(): string {
      return this.systemColorSchemeDark ? 'ciemny' : 'jasny';
    },
  },
  watch: {
    dark(): void {
      if (this.toggleEnabled) {
        setViewTheme(this.dark);
      }
    },
  },
  methods: {
    toggleTheme(event: Event, theme: Theme): void {
      event.stopPropagation();
      this.theme = theme;
      storeThemeSetting(theme, this.dark);
    },
    toggleOpen(): void {
      this.open = !this.open;
    },
    close(): void {
      this.open = false;
    },
  },
  template: `
    <div :class="['d-flex', 'align-items-center', 'h-100']" v-click-away="close">
      <span :class="['position-relative', 'px-2', 'py-2', 'btn-toggle-theme', 'neon-navbar-text', {open}]" @click="toggleOpen" v-if="toggleEnabled" style="cursor:pointer;">
        <vue-toggle-icon :icon="oppositeIcon"/>
        <div class="dropdown-menu dropdown-menu-end" style="display:block" v-show="open">
          <span v-for="(item, itemTheme, index) in items"
                :class="['dropdown-item', {active: itemTheme === theme}]"
                @click="event => toggleTheme(event, itemTheme)">
            <vue-toggle-icon :icon="item.icon" className="me-1"/>
            {{ item.title }}
            <span v-if="index === 2" style="opacity:0.75">
              {{ systemThemeTitle }}
            </span>
          </span>
        </div>
      </span>
    </div>
  `,
});
