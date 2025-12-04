<script setup>
import { ref, computed, onMounted } from 'vue';
import { useProductionReports } from '@/composables/useProductionReports';
import Calendar from 'primevue/calendar';
import Button from 'primevue/button';
import Card from 'primevue/card';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import { Bar, Line } from 'vue-chartjs';
import { Chart as ChartJS, CategoryScale, LinearScale, BarElement, PointElement, LineElement, Title, Tooltip, Legend } from 'chart.js';

ChartJS.register(CategoryScale, LinearScale, BarElement, PointElement, LineElement, Title, Tooltip, Legend);

const { loading, error, fetchWorkerEfficiencyReport } = useProductionReports();

const startDate = ref(new Date(new Date().setMonth(new Date().getMonth() - 1)));
const endDate = ref(new Date());
const reportData = ref(null);

const formatNumber = (value) => new Intl.NumberFormat('tr-TR').format(value);

const workerChartData = computed(() => {
    if (!reportData.value?.data?.top_workers) return null;
    
    const workers = reportData.value.data.top_workers;
    return {
        labels: workers.map(w => w.user_name),
        datasets: [{
            label: 'Toplam Üretim',
            data: workers.map(w => w.total_quantity),
            backgroundColor: [
                '#10b981', '#3b82f6', '#8b5cf6', '#f59e0b', '#ef4444',
                '#06b6d4', '#ec4899', '#84cc16', '#f97316', '#6366f1'
            ],
        }]
    };
});

const trendChartData = computed(() => {
    if (!reportData.value?.data?.worker_trend) return null;
    
    const trend = reportData.value.data.worker_trend;
    return {
        labels: trend.map(t => new Date(t.date).toLocaleDateString('tr-TR', { day: '2-digit', month: 'short' })),
        datasets: [{
            label: 'Günlük Toplam Üretim',
            data: trend.map(t => t.total),
            borderColor: '#10b981',
            backgroundColor: 'rgba(16, 185, 129, 0.1)',
            tension: 0.4,
            fill: true,
        }]
    };
});

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    indexAxis: 'y',
    plugins: { legend: { position: 'top', labels: { color: '#e5e7eb' } } },
    scales: {
        x: { ticks: { color: '#9ca3af' }, grid: { color: 'rgba(255,255,255,0.1)' } },
        y: { ticks: { color: '#9ca3af' }, grid: { color: 'rgba(255,255,255,0.1)' } },
    },
};

const lineChartOptions = { ...chartOptions, indexAxis: 'x' };

const loadReport = async () => {
    const start = startDate.value.toISOString().split('T')[0];
    const end = endDate.value.toISOString().split('T')[0];
    reportData.value = await fetchWorkerEfficiencyReport(start, end);
};

onMounted(loadReport);
</script>

<template>
    <div class="worker-efficiency-report">
        <!-- Filters -->
        <div class="filters mb-4 flex gap-4 align-items-center flex-wrap">
            <div class="field">
                <label class="block mb-2 text-gray-300">Başlangıç Tarihi</label>
                <Calendar v-model="startDate" dateFormat="dd/mm/yy" showIcon />
            </div>
            <div class="field">
                <label class="block mb-2 text-gray-300">Bitiş Tarihi</label>
                <Calendar v-model="endDate" dateFormat="dd/mm/yy" showIcon />
            </div>
            <div class="field">
                <label class="block mb-2">&nbsp;</label>
                <Button label="Raporu Getir" icon="pi pi-search" @click="loadReport" :loading="loading" />
            </div>
        </div>

        <!-- Loading -->
        <div v-if="loading" class="text-center py-6">
            <i class="pi pi-spin pi-spinner text-4xl text-primary"></i>
            <p class="mt-3 text-gray-400">Rapor yükleniyor...</p>
        </div>

        <!-- Error -->
        <div v-else-if="error" class="p-4 bg-red-900/50 text-red-300 rounded">{{ error }}</div>

        <!-- Content -->
        <div v-else-if="reportData?.data" class="report-content">
            <!-- Summary -->
            <div class="grid mb-4">
                <div class="col-12 md:col-4">
                    <Card class="summary-card">
                        <template #title><div class="text-sm text-gray-300">Toplam İşçi</div></template>
                        <template #content>
                            <div class="text-2xl font-bold text-blue-500">{{ reportData.data.total_workers }}</div>
                        </template>
                    </Card>
                </div>
            </div>

            <!-- Charts -->
            <div class="grid mb-4">
                <div class="col-12 lg:col-6">
                    <Card>
                        <template #title>🏆 En Verimli İşçiler (Top 10)</template>
                        <template #content>
                            <div style="height: 400px;">
                                <Bar v-if="workerChartData" :data="workerChartData" :options="chartOptions" />
                            </div>
                        </template>
                    </Card>
                </div>
                <div class="col-12 lg:col-6">
                    <Card>
                        <template #title>📈 Günlük Üretim Trendi</template>
                        <template #content>
                            <div style="height: 400px;">
                                <Line v-if="trendChartData" :data="trendChartData" :options="lineChartOptions" />
                            </div>
                        </template>
                    </Card>
                </div>
            </div>

            <!-- Table -->
            <Card>
                <template #title>👷 Tüm İşçi İstatistikleri</template>
                <template #content>
                    <DataTable :value="reportData.data.worker_stats" stripedRows paginator :rows="10" 
                               :globalFilterFields="['user_name']">
                        <Column field="user_name" header="İşçi Adı" sortable></Column>
                        <Column field="total_quantity" header="Toplam Üretim" sortable>
                            <template #body="{ data }">{{ formatNumber(data.total_quantity) }}</template>
                        </Column>
                        <Column field="production_count" header="Üretim Sayısı" sortable></Column>
                        <Column field="work_days" header="Çalışma Günü" sortable></Column>
                        <Column field="daily_average" header="Günlük Ortalama" sortable>
                            <template #body="{ data }">{{ formatNumber(data.daily_average) }}</template>
                        </Column>
                    </DataTable>
                </template>
            </Card>
        </div>
    </div>
</template>

<style scoped>
.worker-efficiency-report { padding: 1rem; }
.summary-card {
    background: rgba(255, 255, 255, 0.05);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.1);
}
</style>
