<script setup>
import { ref } from 'vue';
import TabView from 'primevue/tabview';
import TabPanel from 'primevue/tabpanel';
import DateRangeReport from './Sales/DateRangeReport.vue';
import MonthlyReport from './Sales/MonthlyReport.vue';
import CustomerReport from './Sales/CustomerReport.vue';
import CustomerProductReport from './Sales/CustomerProductReport.vue';
import CustomerPaymentReport from './Sales/CustomerPaymentReport.vue';
import TrendReport from './Sales/TrendReport.vue';

const activeTab = ref(0);
</script>

<template>
    <div class="sales-reports">
        <div class="page-header mb-4">
            <h1 class="text-2xl font-bold text-white">💰 Satış Raporları</h1>
            <p class="text-gray-400 mt-1">Satış performansını analiz edin ve raporlayın</p>
        </div>

        <TabView v-model:activeIndex="activeTab" class="sales-tabs">
            <TabPanel header="📅 Tarih Aralıklı">
                <DateRangeReport />
            </TabPanel>

            <TabPanel header="📆 Aylık Rapor">
                <MonthlyReport />
            </TabPanel>

            <TabPanel header="👤 Müşteri Bazlı">
                <CustomerReport />
            </TabPanel>

            <TabPanel header="📦 Müşteri Ürün">
                <CustomerProductReport />
            </TabPanel>

            <TabPanel header="💳 Müşteri Ödeme">
                <CustomerPaymentReport />
            </TabPanel>

            <TabPanel header="📈 Trend Analizi">
                <TrendReport />
            </TabPanel>
        </TabView>
    </div>
</template>

<style scoped>
.sales-reports {
    padding: 1.5rem;
}

.page-header {
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    padding-bottom: 1rem;
}

:deep(.sales-tabs .p-tabview-nav) {
    background: transparent;
    border: none;
    gap: 0.5rem;
}

:deep(.sales-tabs .p-tabview-nav-link) {
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 8px;
    padding: 0.75rem 1.25rem;
    color: #e5e7eb;
    transition: all 0.3s ease;
}

:deep(.sales-tabs .p-tabview-nav-link:hover) {
    background: rgba(255, 255, 255, 0.1);
}

:deep(.sales-tabs .p-highlight .p-tabview-nav-link) {
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    border-color: transparent;
    color: white;
}

:deep(.sales-tabs .p-tabview-panels) {
    background: transparent;
    padding: 1.5rem 0;
}
</style>
