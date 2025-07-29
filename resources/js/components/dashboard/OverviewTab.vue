<template>
    <div class="overview-container">
        <!-- KPI Kartları -->
        <div class="kpi-grid">
            <KpiCard
                icon="pi-chart-bar"
                iconClass="production"
                :value="dashboardData.dailyProduction"
                label="Günlük Üretim (Adet)"
                format="number" />

            <KpiCard
                icon="pi-money-bill"
                iconClass="sales"
                :value="dashboardData.dailySales"
                label="Günlük Satış"
                format="currency" />

            <KpiCard
                icon="pi-users"
                iconClass="workers"
                :value="dashboardData.activeWorkers"
                label="Aktif İşçi"
                format="number" />

            <KpiCard
                icon="pi-cog"
                iconClass="machines"
                :value="`${dashboardData.activeMachines}/${dashboardData.totalMachines}`"
                label="Aktif Makine"
                format="text" />
        </div>

        <!-- Genel Toggle (kaldırıldı) -->

        <!-- Alt Tab'lar -->
        <TabView class="overview-tabs">
            <!-- Haftalık Üretim Tab -->
            <TabPanel>
                <template #header>
                    <div class="tab-header">
                        <span class="tab-title">🏭 Haftalık Üretim Trendi</span>
                        <SelectButton
                            v-model="productionViewMode"
                            :options="miniViewModeOptions"
                            optionLabel="label"
                            optionValue="value"
                            class="mini-toggle" />
                    </div>
                </template>

                <div class="tab-content">
                    <Chart
                        v-if="productionViewMode === 'chart'"
                        type="line"
                        :data="weeklyProductionChart"
                        :options="chartOptions"
                        class="trend-chart" />

                    <DataTable
                        v-else
                        :value="weeklyProductionData"
                        paginator
                        :rows="7"
                        class="trend-table">

                        <Column field="date" header="Tarih" sortable>
                            <template #body="slotProps">
                                <span class="date-cell">{{ slotProps.data.date }}</span>
                            </template>
                        </Column>

                        <Column field="quantity" header="Üretim Miktarı" sortable>
                            <template #body="slotProps">
                                <Badge :value="slotProps.data.quantity.toLocaleString()" severity="success" />
                            </template>
                        </Column>

                        <Column field="efficiency" header="Verimlilik %" sortable>
                            <template #body="slotProps">
                                <div class="efficiency-cell">
                                    <ProgressBar :value="slotProps.data.efficiency" class="efficiency-bar" />
                                    <span class="efficiency-text">{{ slotProps.data.efficiency }}%</span>
                                </div>
                            </template>
                        </Column>
                    </DataTable>
                </div>
            </TabPanel>

            <!-- Haftalık Satış Tab -->
            <TabPanel>
                <template #header>
                    <div class="tab-header">
                        <span class="tab-title">💰 Haftalık Satış Trendi</span>
                        <SelectButton
                            v-model="salesViewMode"
                            :options="miniViewModeOptions"
                            optionLabel="label"
                            optionValue="value"
                            class="mini-toggle" />
                    </div>
                </template>

                <div class="tab-content">
                    <Chart
                        v-if="salesViewMode === 'chart'"
                        type="line"
                        :data="weeklySalesChart"
                        :options="chartOptions"
                        class="trend-chart" />

                    <DataTable
                        v-else
                        :value="weeklySalesData"
                        paginator
                        :rows="7"
                        class="trend-table">

                        <Column field="date" header="Tarih" sortable>
                            <template #body="slotProps">
                                <span class="date-cell">{{ slotProps.data.date }}</span>
                            </template>
                        </Column>

                        <Column field="amount" header="Satış Tutarı" sortable>
                            <template #body="slotProps">
                                <span class="currency-cell">₺{{ slotProps.data.amount.toLocaleString() }}</span>
                            </template>
                        </Column>

                        <Column field="orders" header="Sipariş Sayısı" sortable>
                            <template #body="slotProps">
                                <Badge :value="slotProps.data.orders" severity="info" />
                            </template>
                        </Column>

                        <Column header="Günlük Ortalama">
                            <template #body="slotProps">
                                <span class="average-cell">₺{{ Math.round(slotProps.data.amount / slotProps.data.orders).toLocaleString() }}</span>
                            </template>
                        </Column>
                    </DataTable>
                </div>
            </TabPanel>
        </TabView>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import KpiCard from './shared/KpiCard.vue';
import Chart from 'primevue/chart';
import TabView from 'primevue/tabview';
import TabPanel from 'primevue/tabpanel';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import SelectButton from 'primevue/selectbutton';
import Badge from 'primevue/badge';
import ProgressBar from 'primevue/progressbar';

// Props
const props = defineProps({
    dashboardData: {
        type: Object,
        required: true
    },
    chartOptions: {
        type: Object,
        required: true
    }
});

// View Mode States - Her tab için ayrı
const productionViewMode = ref('chart');
const salesViewMode = ref('chart');

// Mini View Mode Options (küçük ikonlar)
const miniViewModeOptions = ref([
    { label: '📊', value: 'chart' },
    { label: '📋', value: 'table' }
]);

// Chart Data
const weeklyProductionChart = ref();
const weeklySalesChart = ref();

// Table Data
const weeklyProductionData = ref([
    { date: '23.07.2025', quantity: 1150, efficiency: 85 },
    { date: '24.07.2025', quantity: 1320, efficiency: 92 },
    { date: '25.07.2025', quantity: 1247, efficiency: 89 },
    { date: '26.07.2025', quantity: 1410, efficiency: 95 },
    { date: '27.07.2025', quantity: 1180, efficiency: 87 },
    { date: '28.07.2025', quantity: 1350, efficiency: 91 },
    { date: '29.07.2025', quantity: 1247, efficiency: 89 }
]);

const weeklySalesData = ref([
    { date: '23.07.2025', amount: 16200, orders: 8 },
    { date: '24.07.2025', amount: 19800, orders: 12 },
    { date: '25.07.2025', amount: 18450, orders: 10 },
    { date: '26.07.2025', amount: 22100, orders: 15 },
    { date: '27.07.2025', amount: 17650, orders: 9 },
    { date: '28.07.2025', amount: 20300, orders: 13 },
    { date: '29.07.2025', amount: 18450, orders: 11 }
]);

// Chart Functions
const updateWeeklyProductionChart = () => {
    weeklyProductionChart.value = {
        labels: ['23 Tem', '24 Tem', '25 Tem', '26 Tem', '27 Tem', '28 Tem', '29 Tem'],
        datasets: [{
            label: 'Günlük Üretim',
            data: [1150, 1320, 1247, 1410, 1180, 1350, 1247],
            borderColor: '#3B82F6',
            backgroundColor: 'rgba(59, 130, 246, 0.1)',
            tension: 0.4,
            borderWidth: 3,
            pointBackgroundColor: '#3B82F6',
            pointBorderColor: '#FFFFFF',
            pointBorderWidth: 2,
            pointRadius: 6,
            fill: true
        }]
    };
};

const updateWeeklySalesChart = () => {
    weeklySalesChart.value = {
        labels: ['23 Tem', '24 Tem', '25 Tem', '26 Tem', '27 Tem', '28 Tem', '29 Tem'],
        datasets: [{
            label: 'Günlük Satış (₺)',
            data: [16200, 19800, 18450, 22100, 17650, 20300, 18450],
            borderColor: '#10B981',
            backgroundColor: 'rgba(16, 185, 129, 0.1)',
            tension: 0.4,
            borderWidth: 3,
            pointBackgroundColor: '#10B981',
            pointBorderColor: '#FFFFFF',
            pointBorderWidth: 2,
            pointRadius: 6,
            fill: true
        }]
    };
};

// Initialize
onMounted(() => {
    updateWeeklyProductionChart();
    updateWeeklySalesChart();
});

// Expose functions for parent component
defineExpose({
    updateCharts: () => {
        updateWeeklyProductionChart();
        updateWeeklySalesChart();
    }
});
</script>

<style scoped>
.overview-container {
    display: flex;
    flex-direction: column;

}

.kpi-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 15px;
    margin-bottom: 30px;
}

/* Tab Header with Toggle */
.tab-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 15px;
    min-width: 280px;
}

.tab-title {
    font-weight: 600;
    font-size: 0.95rem;
    white-space: nowrap;
}

.mini-toggle {
    flex-shrink: 0;
}

.mini-toggle :deep(.p-button) {
    background: rgba(71, 85, 105, 0.5) !important;
    border: 1px solid rgba(100, 116, 139, 0.3) !important;
    color: #CBD5E1 !important;
    padding: 4px 8px !important;
    font-size: 0.8rem !important;
    min-width: 30px !important;
    height: 24px !important;
}

.mini-toggle :deep(.p-button.p-highlight) {
    background: #3B82F6 !important;
    border-color: #3B82F6 !important;
    color: white !important;
    box-shadow: 0 1px 4px rgba(59, 130, 246, 0.3);
}

.mini-toggle :deep(.p-button:hover) {
    background: rgba(71, 85, 105, 0.7) !important;
}

/* Toggle Section (kaldırıldı) */

/* Tab Styling */
.overview-tabs {
    background: rgba(30, 41, 59, 0.95);
    border: 1px solid rgba(71, 85, 105, 0.3);
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0,0,0,0.25);
}

.overview-tabs :deep(.p-tabview-nav) {
    background: rgba(51, 65, 85, 0.8) !important;
    border-bottom: 2px solid rgba(71, 85, 105, 0.5) !important;
    margin: 0;
}

.overview-tabs :deep(.p-tabview-nav li .p-tabview-nav-link) {
    color: #CBD5E1 !important;
    background: transparent !important;
    border: none !important;
    padding: 12px 20px;
    font-weight: 500;
    transition: all 0.3s ease;
}

.overview-tabs :deep(.p-tabview-nav li.p-highlight .p-tabview-nav-link) {
    color: #F1F5F9 !important;
    background: rgba(59, 130, 246, 0.3) !important;
    border-bottom: 3px solid #3B82F6 !important;
}

.overview-tabs :deep(.p-tabview-nav li:hover .p-tabview-nav-link) {
    background: rgba(71, 85, 105, 0.3) !important;
}

.overview-tabs :deep(.p-tabview-panels) {
    background: transparent !important;
    border: none !important;
    padding: 25px;
}

/* Content Styling */
.tab-content {
    min-height: 400px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.trend-chart {
    width: 100%;
    height: 400px;
}

.trend-table {
    width: 100%;
}

/* Table Cell Styling */
.date-cell {
    font-weight: 600;
    color: #3B82F6;
}

.currency-cell {
    font-weight: 600;
    color: #10B981;
    font-size: 1.1rem;
}

.average-cell {
    font-weight: 500;
    color: #8B5CF6;
}

.efficiency-cell {
    display: flex;
    align-items: center;
    gap: 10px;
}

.efficiency-bar {
    flex: 1;
    height: 8px;
}

.efficiency-text {
    font-weight: 600;
    color: #F59E0B;
    min-width: 40px;
}

/* SelectButton Override */
:deep(.p-selectbutton .p-button) {
    background: rgba(71, 85, 105, 0.5) !important;
    border: 1px solid rgba(100, 116, 139, 0.3) !important;
    color: #CBD5E1 !important;
    padding: 8px 16px;
    font-weight: 500;
}

:deep(.p-selectbutton .p-button.p-highlight) {
    background: #3B82F6 !important;
    border-color: #3B82F6 !important;
    color: white !important;
    box-shadow: 0 2px 8px rgba(59, 130, 246, 0.3);
}

:deep(.p-selectbutton .p-button:hover) {
    background: rgba(71, 85, 105, 0.7) !important;
}

/* DataTable Override */
:deep(.p-datatable) {
    background: rgba(30, 41, 59, 0.95) !important;
    border: 1px solid rgba(71, 85, 105, 0.3) !important;
    border-radius: 10px;
}

:deep(.p-datatable .p-datatable-thead > tr > th) {
    background: rgba(51, 65, 85, 0.8) !important;
    color: #F1F5F9 !important;
    border-bottom: 1px solid rgba(71, 85, 105, 0.3) !important;
    font-weight: 600;
}

:deep(.p-datatable .p-datatable-tbody > tr) {
    background: rgba(30, 41, 59, 0.95) !important;
    color: #E2E8F0 !important;
}

:deep(.p-datatable .p-datatable-tbody > tr:nth-child(even)) {
    background: rgba(51, 65, 85, 0.3) !important;
}

:deep(.p-datatable .p-datatable-tbody > tr:hover) {
    background: rgba(59, 130, 246, 0.2) !important;
}

/* Responsive */
@media (max-width: 1200px) {
    .kpi-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 12px;
    }
}

@media (max-width: 768px) {
    .kpi-grid {
        grid-template-columns: 1fr;
        gap: 10px;
    }

    .tab-header {
        flex-direction: column;
        gap: 8px;
        align-items: stretch;
        min-width: auto;
    }

    .tab-title {
        text-align: center;
        font-size: 0.9rem;
    }

    .mini-toggle {
        align-self: center;
    }

    .overview-tabs :deep(.p-tabview-nav li .p-tabview-nav-link) {
        padding: 10px 12px;
        font-size: 0.85rem;
    }
}
</style>
