import {createPinia} from "pinia";
import {createApp} from "vue";
import Hrm from "../Vue/Hmr.vue";

const vueApp = createApp(Hrm);
const pinia = createPinia();
vueApp.use(pinia);
vueApp.mount(document.querySelector('#neonApplication')!);
