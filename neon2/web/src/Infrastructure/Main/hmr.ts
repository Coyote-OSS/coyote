import {createApp} from "vue";
import Hrm from "../Vue/NavigationView/View/Hmr.vue";

const vueApp = createApp(Hrm);
vueApp.mount(document.querySelector('#neonApplication')!);
