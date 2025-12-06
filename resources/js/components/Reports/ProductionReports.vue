<script setup>
import { ref } from 'vue';
import TabView from 'primevue/tabview';
import TabPanel from 'primevue/tabpanel';

import DateRangeReport from './Production/DateRangeReport.vue';
import WorkerEfficiencyReport from './Production/WorkerEfficiencyReport.vue';
import WorkerDetailReport from './Production/WorkerDetailReport.vue';
import ProductAnalysisReport from './Production/ProductAnalysisReport.vue';
import TrendAnalysisReport from './Production/TrendAnalysisReport.vue';
import RealtimeDashboard from './Production/RealtimeDashboard.vue';
import ExecutiveSummary from './Production/ExecutiveSummary.vue';

const activeIndex = ref(0);
</script>

<template>
    <div class="production-reports">
        <div class="page-header mb-4">
            <h1 class="text-2xl font-bold text-white">🏭 Üretim Raporları</h1>
            <p class="text-gray-400 mt-1">Üretim performansını analiz edin ve raporlayın</p>
        </div>

        <TabView v-model:activeIndex="activeIndex" class="production-tabs">
            <TabPanel header="📊 Tarih Aralıklı">
                <DateRangeReport />
            </TabPanel>

            <TabPanel header="👷 İşçi Verimliliği">
                <WorkerEfficiencyReport />
            </TabPanel>

            <TabPanel header="👤 İşçi Detay">
                <WorkerDetailReport />
            </TabPanel>

            <TabPanel header="📦 Ürün Analizi">
                <ProductAnalysisReport />
            </TabPanel>

<!--            <TabPanel header="📈 Trend Analizi">-->
<!--                <TrendAnalysisReport />-->
<!--            </TabPanel>-->

<!--            <TabPanel header="⚡ Gerçek Zamanlı">-->
<!--                <RealtimeDashboard />-->
<!--            </TabPanel>-->

            <TabPanel header="📋 Özet Rapor">
                <ExecutiveSummary />
            </TabPanel>
        </TabView>
    </div>
</template>

<style scoped>
.production-reports {
    padding: 1.5rem;
}

.page-header {
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    padding-bottom: 1rem;
}

:deep(.production-tabs .p-tabview-nav) {
    background: transparent;
    border: none;
    gap: 0.5rem;
}

:deep(.production-tabs .p-tabview-nav-link) {
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 8px;
    padding: 0.75rem 1.25rem;
    color: #e5e7eb;
    transition: all 0.3s ease;
}

:deep(.production-tabs .p-tabview-nav-link:hover) {
    background: rgba(255, 255, 255, 0.1);
}

:deep(.production-tabs .p-highlight .p-tabview-nav-link) {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    border-color: transparent;
    color: white;
}

:deep(.production-tabs .p-tabview-panels) {
    background: transparent;
    padding: 1.5rem 0;
}
</style>
