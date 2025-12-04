<script setup>
import { ref, computed, onMounted } from 'vue';
import { useProductionReports } from '@/composables/useProductionReports';
import Calendar from 'primevue/calendar';
import Button from 'primevue/button';
import Card from 'primevue/card';
import { Bar, Line } from 'vue-chartjs';
import { Chart as ChartJS, CategoryScale, LinearScale, BarElement, PointElement, LineElement, Title, Tooltip, Legend } from 'chart.js';

ChartJS.register(CategoryScale, LinearScale, BarElement, PointElement, LineElement, Title, Tooltip, Legend);

const { loading, error, fetchDateRangeReport } = useProductionReports();

const startDate = ref(new Date(new Date().setMonth(new Date().getMonth() - 1)));
const endDate = ref(new Date());
const reportData = ref(null);

const formatNumber = (value) => {
    return new Intl.NumberFormat('tr-TR').format(value);
};

const dailyChartData = computed(() => {
    if (!reportData.value?.data?.daily_distribution) return null;
    
    const data = reportData.value.data.daily_distribution;
    return {
        labels: data.map(d => new Date(d.date).toLocaleDateString('tr-TR', { day: '2-digit', month: 'short' })),
        datasets: [{
            label: 'Günlük Üretim',
            data: data.map(d => d.total),
            backgroundColor: 'rgba(16, 185, 129, 0.6)',
            borderColor: '#10b981',
            borderWidth: 1,
        }]
    };
});

const hourlyChartData = computed(() => {
    if (!reportData.value?.data?.hourly_analysis) return null;
    
    const data = reportData.value.data.hourly_analysis;
    return {
        labels: data.map(d => `${d.hour}:00`),
        datasets: [{
            label: 'Saatlik Üretim',
            data: data.map(d => d.total),
            borderColor: '#8b5cf6',
            backgroundColor: 'rgba(139, 92, 246, 0.1)',
            tension: 0.4,
            fill: true,
        }]
    };
});

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { position: 'top', labels: { color: '#e5e7eb' } },
    },
    scales: {
        x: { ticks: { color: '#9ca3af' }, grid: { color: 'rgba(255,255,255,0.1)' } },
        y: { ticks: { color: '#9ca3af' }, grid: { color: 'rgba(255,255,255,0.1)' } },
    },
};

const loadReport = async () => {
    const start = startDate.value.toISOString().split('T')[0];
    const end = endDate.value.toISOString().split('T')[0];
    reportData.value = await fetchDateRangeReport(start, end);
};

onMounted(loadReport);
</script>

<template>
    <div class="date-range-report">
        <!-- Filtreler -->
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
        <div v-else-if="error" class="p-4 bg-red-900/50 text-red-300 rounded">
            {{ error }}
        </div>

        <!-- Content -->
        <div v-else-if="reportData?.data" class="report-content">
            <!-- Summary Cards -->
            <div class="grid mb-4">
                <div class="col-12 md:col-3">
                    <Card class="summary-card">
                        <template #title>
                            <div class="text-sm text-gray-300">Toplam Üretim</div>
                        </template>
                        <template #content>
                            <div class="text-2xl font-bold text-green-500">
                                {{ formatNumber(reportData.data.summary.total_quantity) }}
                            </div>
                        </template>
                    </Card>
                </div>
                <div class="col-12 md:col-3">
                    <Card class="summary-card">
                        <template #title>
                            <div class="text-sm text-gray-300">Üretim Sayısı</div>
                        </template>
                        <template #content>
                            <div class="text-2xl font-bold text-blue-500">
                                {{ formatNumber(reportData.data.summary.total_count) }}
                            </div>
                        </template>
                    </Card>
                </div>
                <div class="col-12 md:col-3">
                    <Card class="summary-card">
                        <template #title>
                            <div class="text-sm text-gray-300">Günlük Ortalama</div>
                        </template>
                        <template #content>
                            <div class="text-2xl font-bold text-purple-500">
                                {{ formatNumber(reportData.data.summary.daily_average) }}
                            </div>
                        </template>
                    </Card>
                </div>
                <div class="col-12 md:col-3">
                    <Card class="summary-card">
                        <template #title>
                            <div class="text-sm text-gray-300">Aktif İşçi</div>
                        </template>
                        <template #content>
                            <div class="text-2xl font-bold text-orange-500">
                                {{ reportData.data.summary.unique_workers }}
                            </div>
                        </template>
                    </Card>
                </div>
            </div>

            <!-- Charts -->
            <div class="grid mb-4">
                <div class="col-12 lg:col-8">
                    <Card>
                        <template #title>Günlük Üretim Dağılımı</template>
                        <template #content>
                            <div style="height: 300px;">
                                <Bar v-if="dailyChartData" :data="dailyChartData" :options="chartOptions" />
                            </div>
                        </template>
                    </Card>
                </div>
                <div class="col-12 lg:col-4">
                    <Card>
                        <template #title>En Çok Üretilen Ürünler</template>
                        <template #content>
                            <div v-for="(product, index) in reportData.data.top_products" :key="index" 
                                 class="flex justify-between items-center py-2 border-b border-gray-700">
                                <span class="text-gray-300">{{ product.product_name }}</span>
                                <span class="font-bold text-green-400">{{ formatNumber(product.total_quantity) }}</span>
                            </div>
                        </template>
                    </Card>
                </div>
            </div>

            <!-- Hourly Chart -->
            <Card>
                <template #title>Saatlik Üretim Analizi</template>
                <template #content>
                    <div style="height: 250px;">
                        <Line v-if="hourlyChartData" :data="hourlyChartData" :options="chartOptions" />
                    </div>
                </template>
            </Card>
        </div>
    </div>
</template>

<style scoped>
.date-range-report {
    padding: 1rem;
}

.summary-card {
    background: rgba(255, 255, 255, 0.05);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.1);
}
</style>
