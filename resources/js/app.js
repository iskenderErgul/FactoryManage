

import { createApp } from 'vue';
import './assets/style.scss'
import PrimeVue from 'primevue/config';
import HomeView from "@/views/HomeView.vue";

import ToastService from 'primevue/toastservice';

import router from './router';
import store from './store';

const app = createApp({});
app.component(HomeView)
app.use(ToastService)
app.use(router)
app.use(store)
app.use(PrimeVue, { ripple: true , locale: {
        firstDayOfWeek: 1,
        dayNames: ["Pazar", "Pazartesi", "Salı", "Çarşamba", "Perşembe", "Cuma", "Cumartesi"],
        dayNamesShort: ["Paz", "Pzt", "Sal", "Çar", "Per", "Cum", "Cmt"],
        dayNamesMin: ["Pz", "Pt", "Sa", "Ça", "Pe", "Cu", "Ct"],
        monthNames: [
            "Ocak", "Şubat", "Mart", "Nisan", "Mayıs", "Haziran",
            "Temmuz", "Ağustos", "Eylül", "Ekim", "Kasım", "Aralık"
        ],
        monthNamesShort: [
            "Oca", "Şub", "Mar", "Nis", "May", "Haz",
            "Tem", "Ağu", "Eyl", "Eki", "Kas", "Ara"
        ],
        today: "Bugün",
        clear: "Temizle",
        dateFormat: "dd/mm/yy",
        weekHeader: "Hf"
    } });

app.mount('#app');
