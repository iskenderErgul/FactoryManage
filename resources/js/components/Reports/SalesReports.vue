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
    <div class="sales-reports-container">
        <div class="card">
            <TabView v-model:activeIndex="activeTab">
                <TabPanel header="📅 Tarih Aralıklı Rapor">
                    <DateRangeReport />
                </TabPanel>

                <TabPanel header="📆 Aylık Rapor">
                    <MonthlyReport />
                </TabPanel>

                <TabPanel header="👤 Müşteri Bazlı Rapor">
                    <CustomerReport />
                </TabPanel>

                <TabPanel header="📦 Müşteri Ürün Raporu">
                    <CustomerProductReport />
                </TabPanel>

                <TabPanel header="💳 Müşteri Ödeme Raporu">
                    <CustomerPaymentReport />
                </TabPanel>

                <TabPanel header="📈 Trend Analizi">
                    <TrendReport />
                </TabPanel>
            </TabView>
        </div>
    </div>
</template>

<style scoped>
.sales-reports-container {
    width: 100%;
    background: transparent;
}

.card {
    background: transparent;
    border-radius: 0;
    padding: 0;
    box-shadow: none;
}

/* PrimeVue Override - Koyu tema ve Geniş Tablar için */
:deep(.p-tabview .p-tabview-nav) {
    background: rgba(30, 41, 59, 0.95) !important;
    border-bottom: 1px solid rgba(71, 85, 105, 0.3) !important;
    display: flex !important;
    width: 100% !important;
    border-radius: 12px 12px 0 0;
}

:deep(.p-tabview .p-tabview-nav li) {
    flex: 1 !important;
    min-width: 0 !important;
}

:deep(.p-tabview .p-tabview-nav li .p-tabview-nav-link) {
    color: #94A3B8 !important;
    background: transparent !important;
    border: none !important;
    width: 100% !important;
    text-align: center !important;
    padding: 16px 20px !important;
    font-size: 14px !important;
    font-weight: 600 !important;
    white-space: nowrap !important;
    overflow: hidden !important;
    text-overflow: ellipsis !important;
    transition: all 0.2s ease;
}

:deep(.p-tabview .p-tabview-nav li:not(.p-highlight):hover .p-tabview-nav-link) {
    color: #E2E8F0 !important;
    background: rgba(30, 41, 59, 0.8) !important;
}

:deep(.p-tabview .p-tabview-nav li.p-highlight .p-tabview-nav-link) {
    color: #F1F5F9 !important;
    background: rgba(59, 130, 246, 0.2) !important;
    border-bottom: 2px solid #3B82F6 !important;
}

:deep(.p-tabview .p-tabview-panels) {
    background: rgba(15, 23, 42, 0.6) !important;
    border: 1px solid rgba(71, 85, 105, 0.3) !important;
    border-top: none !important;
    padding: 2rem !important;
    border-radius: 0 0 12px 12px;
    backdrop-filter: blur(10px);
}
</style>
