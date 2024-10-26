

import { createApp } from 'vue';
import './assets/style.scss'
import PrimeVue from 'primevue/config';
import HomeView from "@/views/HomeView.vue";


import Toast from "primevue/toast";

import router from './router';
import store from './store';

const app = createApp({});
app.component(HomeView)
app.use(Toast)
app.use(router)
app.use(store)
app.use(PrimeVue, { ripple: true });

app.mount('#app');
