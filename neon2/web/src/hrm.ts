import {createApp} from "vue";
import Hrm from "./Apps/VueApp/Modules/Navigation/View/Hrm.vue";

const vueApp = createApp(Hrm);
vueApp.mount(document.querySelector('#neonApplication')!);
