<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useProductionReports } from '@/composables/useProductionReports';
import Card from 'primevue/card';
import Button from 'primevue/button';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import { Bar } from 'vue-chartjs';
import { Chart as ChartJS, CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend } from 'chart.js';

ChartJS.register(CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend);

const { loading, error, fetchRealtimeDashboard } = useProductionReports();

const reportData = ref(null);
const lastUpdated = ref(null);
let refreshInterval = null;

const formatNumber = (value) => new Intl.NumberFormat('tr-TR').format(value);

const formatTime = (date) => {
    return new Date(date).toLocaleTimeString('tr-TR', { hour: '2-digit', minute: '2-digit' });
};

const hourlyChartData = computed(() => {
    if (!reportData.value?.hourly_production) return null;
    
    const data = reportData.value.hourly_production;
    return {
        labels: data.map(d => `${d.hour}:00`),
        datasets: [{
            label: 'Saatlik Üretim',
            data: data.map(d => d.total),
            backgroundColor: '#10b981',
        }]
    };
});

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { position: 'top', labels: { color: '#e5e7eb' } } },
    scales: {
        x: { ticks: { color: '#9ca3af' }, grid: { color: 'rgba(255,255,255,0.1)' } },
        y: { ticks: { color: '#9ca3af' }, grid: { color: 'rgba(255,255,255,0.1)' } },
    },
};

const loadDashboard = async () => {
    const response = await fetchRealtimeDashboard();
    // Backend response.data içinde veriyi döndürüyor
    reportData.value = response.data || response;
    lastUpdated.value = new Date();
};

const startAutoRefresh = () => {
    refreshInterval = setInterval(loadDashboard, 30000); // 30 saniyede bir
};

const stopAutoRefresh = () => {
    if (refreshInterval) {
        clearInterval(refreshInterval);
        refreshInterval = null;
    }
};

onMounted(() => {
    loadDashboard();
    startAutoRefresh();
});

onUnmounted(stopAutoRefresh);
</script>

<template>
    <div class="realtime-dashboard">
        <!-- Header -->
        <div class="flex justify-between items-center mb-4">
            <div>
                <h2 class="text-xl font-bold text-gray-200">⚡ Gerçek Zamanlı Dashboard</h2>
                <p class="text-sm text-gray-400" v-if="lastUpdated">
                    Son güncelleme: {{ formatTime(lastUpdated) }}
                </p>
            </div>
            <Button icon="pi pi-refresh" @click="loadDashboard" :loading="loading" 
                    text rounded class="refresh-btn" />
        </div>

        <!-- Loading -->
        <div v-if="loading && !reportData" class="text-center py-6">
            <i class="pi pi-spin pi-spinner text-4xl text-primary"></i>
            <p class="mt-3 text-gray-400">Dashboard yükleniyor...</p>
        </div>

        <!-- Error -->
        <div v-else-if="error" class="p-4 bg-red-900/50 text-red-300 rounded">{{ error }}</div>

        <!-- Content -->
        <div v-else-if="reportData" class="dashboard-content">
            <!-- Today Summary -->
            <div class="grid mb-4">
                <div class="col-6 md:col-3">
                    <Card class="stat-card">
                        <template #content>
                            <div class="text-center">
                                <i class="pi pi-box text-3xl text-green-500 mb-2"></i>
                                <div class="text-3xl font-bold text-white">
                                    {{ formatNumber(reportData.today_summary?.total_quantity || 0) }}
                                </div>
                                <div class="text-sm text-gray-300">Bugün Üretim</div>
                            </div>
                        </template>
                    </Card>
                </div>
                <div class="col-6 md:col-3">
                    <Card class="stat-card">
                        <template #content>
                            <div class="text-center">
                                <i class="pi pi-list text-3xl text-blue-500 mb-2"></i>
                                <div class="text-3xl font-bold text-white">
                                    {{ reportData.today_summary?.production_count || 0 }}
                                </div>
                                <div class="text-sm text-gray-300">Üretim Kayıdı</div>
                            </div>
                        </template>
                    </Card>
                </div>
                <div class="col-6 md:col-3">
                    <Card class="stat-card">
                        <template #content>
                            <div class="text-center">
                                <i class="pi pi-users text-3xl text-purple-500 mb-2"></i>
                                <div class="text-3xl font-bold text-white">
                                    {{ reportData.today_summary?.active_workers || 0 }}
                                </div>
                                <div class="text-sm text-gray-300">Aktif İşçi</div>
                            </div>
                        </template>
                    </Card>
                </div>
                <div class="col-6 md:col-3">
                    <Card class="stat-card">
                        <template #content>
                            <div class="text-center">
                                <i class="pi pi-clock text-3xl text-orange-500 mb-2"></i>
                                <div class="text-3xl font-bold text-white">
                                    {{ formatNumber(reportData.today_summary?.hourly_average || 0) }}
                                </div>
                                <div class="text-sm text-gray-300">Saatlik Ortalama</div>
                            </div>
                        </template>
                    </Card>
                </div>
            </div>

            <!-- Comparison -->
            <Card class="mb-4" v-if="reportData.comparison">
                <template #content>
                    <div class="flex justify-between items-center">
                        <div>
                            <span class="text-gray-300">Dün:</span>
                            <span class="font-bold text-white ml-2">
                                {{ formatNumber(reportData.comparison.yesterday_total) }}
                            </span>
                        </div>
                        <div class="flex items-center gap-2">
                            <i :class="['pi', reportData.comparison.trend === 'up' ? 'pi-arrow-up text-green-500' : 'pi-arrow-down text-red-500']"></i>
                            <span :class="['font-bold text-xl', reportData.comparison.change_percentage >= 0 ? 'text-green-500' : 'text-red-500']">
                                {{ reportData.comparison.change_percentage >= 0 ? '+' : '' }}{{ reportData.comparison.change_percentage }}%
                            </span>
                        </div>
                    </div>
                </template>
            </Card>

            <!-- Charts and Table -->
            <div class="grid">
                <div class="col-12 lg:col-7">
                    <Card>
                        <template #title>📊 Bugünün Saatlik Üretimi</template>
                        <template #content>
                            <div style="height: 300px;">
                                <Bar v-if="hourlyChartData" :data="hourlyChartData" :options="chartOptions" />
                            </div>
                        </template>
                    </Card>
                </div>
                <div class="col-12 lg:col-5">
                    <Card>
                        <template #title>🕐 Son Üretimler</template>
                        <template #content>
                            <DataTable :value="reportData.recent_productions" :rows="8" scrollable scrollHeight="300px">
                                <Column field="product_name" header="Ürün"></Column>
                                <Column field="quantity" header="Miktar">
                                    <template #body="{ data }">{{ formatNumber(data.quantity) }}</template>
                                </Column>
                                <Column field="user_name" header="İşçi"></Column>
                            </DataTable>
                        </template>
                    </Card>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.realtime-dashboard { padding: 1rem; }
.stat-card {
    background: rgba(255, 255, 255, 0.05);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.1);
}

:deep(.refresh-btn) {
    background: rgba(255, 255, 255, 0.05) !important;
    border: 1px solid rgba(255, 255, 255, 0.1) !important;
    color: #e5e7eb !important;
}

:deep(.refresh-btn:hover) {
    background: rgba(255, 255, 255, 0.1) !important;
    border-color: rgba(16, 185, 129, 0.5) !important;
}
</style>
