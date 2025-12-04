<script setup>
import { ref, computed, onMounted } from 'vue';
import { useProductionReports } from '@/composables/useProductionReports';
import axios from 'axios';
import Calendar from 'primevue/calendar';
import Dropdown from 'primevue/dropdown';
import Button from 'primevue/button';
import Card from 'primevue/card';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import { Bar, Line, Pie } from 'vue-chartjs';
import { Chart as ChartJS, CategoryScale, LinearScale, BarElement, PointElement, LineElement, ArcElement, Title, Tooltip, Legend } from 'chart.js';

ChartJS.register(CategoryScale, LinearScale, BarElement, PointElement, LineElement, ArcElement, Title, Tooltip, Legend);

const { loading, error, fetchWorkerDetailReport } = useProductionReports();

const workers = ref([]);
const selectedWorker = ref(null);
const startDate = ref(new Date(new Date().setMonth(new Date().getMonth() - 1)));
const endDate = ref(new Date());
const reportData = ref(null);

const formatNumber = (value) => new Intl.NumberFormat('tr-TR').format(value);

const productChartData = computed(() => {
    if (!reportData.value?.data?.product_breakdown) return null;
    
    const products = reportData.value.data.product_breakdown.slice(0, 8);
    return {
        labels: products.map(p => p.product_name),
        datasets: [{
            data: products.map(p => p.total_quantity),
            backgroundColor: [
                '#10b981', '#3b82f6', '#8b5cf6', '#f59e0b', 
                '#ef4444', '#06b6d4', '#ec4899', '#84cc16'
            ],
        }]
    };
});

const dailyTrendData = computed(() => {
    if (!reportData.value?.data?.daily_trend) return null;
    
    const trend = reportData.value.data.daily_trend;
    return {
        labels: trend.map(t => new Date(t.date).toLocaleDateString('tr-TR', { day: '2-digit', month: 'short' })),
        datasets: [{
            label: 'Günlük Üretim',
            data: trend.map(t => t.total),
            borderColor: '#10b981',
            backgroundColor: 'rgba(16, 185, 129, 0.1)',
            tension: 0.4,
            fill: true,
        }]
    };
});

const monthlyChartData = computed(() => {
    if (!reportData.value?.data?.monthly_performance) return null;
    
    const monthly = reportData.value.data.monthly_performance;
    return {
        labels: monthly.map(m => m.month_name),
        datasets: [{
            label: 'Aylık Üretim',
            data: monthly.map(m => m.total_quantity),
            backgroundColor: '#3b82f6',
        }]
    };
});

const pieOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { position: 'right', labels: { color: '#e5e7eb' } } },
};

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { position: 'top', labels: { color: '#e5e7eb' } } },
    scales: {
        x: { ticks: { color: '#9ca3af' }, grid: { color: 'rgba(255,255,255,0.1)' } },
        y: { ticks: { color: '#9ca3af' }, grid: { color: 'rgba(255,255,255,0.1)' } },
    },
};

const loadWorkers = async () => {
    try {
        const response = await axios.get('/api/get-all-workers');
        workers.value = response.data.map(w => ({ label: w.name, value: w.id }));
    } catch (err) {
        console.error('İşçiler yüklenemedi:', err);
    }
};

const loadReport = async () => {
    if (!selectedWorker.value) {
        error.value = 'Lütfen bir işçi seçin';
        return;
    }
    
    const start = startDate.value.toISOString().split('T')[0];
    const end = endDate.value.toISOString().split('T')[0];
    reportData.value = await fetchWorkerDetailReport(selectedWorker.value, start, end);
};

onMounted(loadWorkers);
</script>

<template>
    <div class="worker-detail-report">
        <!-- Filters -->
        <div class="filters mb-4 flex gap-4 align-items-center flex-wrap">
            <div class="field">
                <label class="block mb-2 text-gray-300">İşçi Seçin</label>
                <Dropdown v-model="selectedWorker" :options="workers" optionLabel="label" optionValue="value" 
                          placeholder="İşçi seçin" class="w-full md:w-14rem" />
            </div>
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
            <!-- Worker Info -->
            <Card class="mb-4">
                <template #content>
                    <div class="flex justify-between items-center">
                        <div>
                            <h2 class="text-2xl font-bold text-white">{{ reportData.data.worker.name }}</h2>
                            <p class="text-gray-400">{{ reportData.data.worker.email }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm text-gray-400">Dönem</p>
                            <p class="text-white">
                                {{ new Date(reportData.data.period.start_date).toLocaleDateString('tr-TR') }} - 
                                {{ new Date(reportData.data.period.end_date).toLocaleDateString('tr-TR') }}
                            </p>
                        </div>
                    </div>
                </template>
            </Card>

            <!-- Summary Cards -->
            <div class="grid mb-4">
                <div class="col-6 md:col-3">
                    <Card class="summary-card">
                        <template #content>
                            <div class="text-center">
                                <div class="text-sm text-gray-300">Toplam Üretim</div>
                                <div class="text-2xl font-bold text-green-500">
                                    {{ formatNumber(reportData.data.summary.total_quantity) }}
                                </div>
                            </div>
                        </template>
                    </Card>
                </div>
                <div class="col-6 md:col-3">
                    <Card class="summary-card">
                        <template #content>
                            <div class="text-center">
                                <div class="text-sm text-gray-300">Çalışma Günü</div>
                                <div class="text-2xl font-bold text-blue-500">
                                    {{ reportData.data.summary.work_days }}
                                </div>
                            </div>
                        </template>
                    </Card>
                </div>
                <div class="col-6 md:col-3">
                    <Card class="summary-card">
                        <template #content>
                            <div class="text-center">
                                <div class="text-sm text-gray-300">Günlük Ortalama</div>
                                <div class="text-2xl font-bold text-purple-500">
                                    {{ formatNumber(reportData.data.summary.daily_average) }}
                                </div>
                            </div>
                        </template>
                    </Card>
                </div>
                <div class="col-6 md:col-3">
                    <Card class="summary-card">
                        <template #content>
                            <div class="text-center">
                                <div class="text-sm text-gray-300">Üretim Sayısı</div>
                                <div class="text-2xl font-bold text-orange-500">
                                    {{ reportData.data.summary.production_count }}
                                </div>
                            </div>
                        </template>
                    </Card>
                </div>
            </div>

            <!-- Charts -->
            <div class="grid mb-4">
                <div class="col-12 lg:col-5">
                    <Card>
                        <template #title>📦 Ürün Bazlı Dağılım</template>
                        <template #content>
                            <div style="height: 300px;">
                                <Pie v-if="productChartData" :data="productChartData" :options="pieOptions" />
                            </div>
                        </template>
                    </Card>
                </div>
                <div class="col-12 lg:col-7">
                    <Card>
                        <template #title>📈 Günlük Üretim Trendi</template>
                        <template #content>
                            <div style="height: 300px;">
                                <Line v-if="dailyTrendData" :data="dailyTrendData" :options="chartOptions" />
                            </div>
                        </template>
                    </Card>
                </div>
            </div>

            <!-- Monthly Performance -->
            <Card class="mb-4">
                <template #title>📊 Aylık Performans</template>
                <template #content>
                    <div style="height: 250px;">
                        <Bar v-if="monthlyChartData" :data="monthlyChartData" :options="chartOptions" />
                    </div>
                </template>
            </Card>

            <!-- Tables -->
            <div class="grid">
                <div class="col-12 lg:col-6">
                    <Card>
                        <template #title>📦 Ürün Detayları</template>
                        <template #content>
                            <DataTable :value="reportData.data.product_breakdown" :rows="5" paginator>
                                <Column field="product_name" header="Ürün"></Column>
                                <Column field="total_quantity" header="Toplam">
                                    <template #body="{ data }">{{ formatNumber(data.total_quantity) }}</template>
                                </Column>
                                <Column field="production_count" header="Üretim Sayısı"></Column>
                            </DataTable>
                        </template>
                    </Card>
                </div>
                <div class="col-12 lg:col-6">
                    <Card>
                        <template #title>🏭 Makine Bazlı Üretim</template>
                        <template #content>
                            <DataTable :value="reportData.data.machine_breakdown" :rows="5" paginator>
                                <Column field="machine_name" header="Makine"></Column>
                                <Column field="total_quantity" header="Toplam">
                                    <template #body="{ data }">{{ formatNumber(data.total_quantity) }}</template>
                                </Column>
                                <Column field="production_count" header="Üretim Sayısı"></Column>
                            </DataTable>
                        </template>
                    </Card>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.worker-detail-report { padding: 1rem; }
.summary-card {
    background: rgba(255, 255, 255, 0.05);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.1);
}
</style>
