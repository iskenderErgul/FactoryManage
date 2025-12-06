<script setup>
import { ref, computed, onMounted } from 'vue';
import { useProductionReports } from '@/composables/useProductionReports';
import Dropdown from 'primevue/dropdown';
import Button from 'primevue/button';
import Card from 'primevue/card';
import { Line, Bar } from 'vue-chartjs';
import { Chart as ChartJS, CategoryScale, LinearScale, PointElement, LineElement, BarElement, Title, Tooltip, Legend } from 'chart.js';

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, BarElement, Title, Tooltip, Legend);

const { loading, error, fetchTrendAnalysisReport } = useProductionReports();

const currentYear = new Date().getFullYear();
const selectedYear = ref(currentYear);
const selectedMonth = ref(null);
const reportData = ref(null);

const years = Array.from({ length: 6 }, (_, i) => ({ label: String(currentYear - i), value: currentYear - i }));
const months = [
    { label: 'Ocak', value: 1 }, { label: 'Şubat', value: 2 }, { label: 'Mart', value: 3 },
    { label: 'Nisan', value: 4 }, { label: 'Mayıs', value: 5 }, { label: 'Haziran', value: 6 },
    { label: 'Temmuz', value: 7 }, { label: 'Ağustos', value: 8 }, { label: 'Eylül', value: 9 },
    { label: 'Ekim', value: 10 }, { label: 'Kasım', value: 11 }, { label: 'Aralık', value: 12 },
];

const formatNumber = (value) => new Intl.NumberFormat('tr-TR').format(value);

const monthlyChartData = computed(() => {
    if (!reportData.value?.data?.monthly_data) return null;
    
    const data = reportData.value.data.monthly_data;
    return {
        labels: data.map(d => d.month_name),
        datasets: [{
            label: 'Aylık Üretim',
            data: data.map(d => d.total_quantity),
            borderColor: '#10b981',
            backgroundColor: 'rgba(16, 185, 129, 0.1)',
            tension: 0.4,
            fill: true,
        }]
    };
});

const yearCompareData = computed(() => {
    if (!reportData.value?.data?.yearly_growth) return null;
    
    const growth = reportData.value.data.yearly_growth;
    return {
        labels: [String(growth.current_year - 1), String(growth.current_year)],
        datasets: [{
            label: 'Yıllık Toplam Üretim',
            data: [growth.previous_year_total, growth.current_year_total],
            backgroundColor: ['#6366f1', '#10b981'],
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

const loadReport = async () => {
    const response = await fetchTrendAnalysisReport(selectedYear.value, selectedMonth.value);
    reportData.value = response.data || response;
};

onMounted(loadReport);
</script>

<template>
    <div class="trend-analysis-report">
        <!-- Filters -->
        <div class="filters mb-4 flex gap-4 align-items-center flex-wrap">
            <div class="field">
                <label class="block mb-2 text-gray-300">Yıl</label>
                <Dropdown v-model="selectedYear" :options="years" optionLabel="label" optionValue="value" />
            </div>
            <div class="field">
                <label class="block mb-2 text-gray-300">Ay (Opsiyonel)</label>
                <Dropdown v-model="selectedMonth" :options="months" optionLabel="label" optionValue="value" 
                          placeholder="Seçiniz" showClear />
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
            <!-- Yearly Growth Cards -->
            <div class="grid mb-4" v-if="reportData.yearly_growth">
                <div class="col-12 md:col-4">
                    <Card class="summary-card">
                        <template #title><div class="text-sm text-gray-300">Bu Yıl Toplam</div></template>
                        <template #content>
                            <div class="text-2xl font-bold text-green-500">
                                {{ formatNumber(reportData.yearly_growth.current_year_total) }}
                            </div>
                        </template>
                    </Card>
                </div>
                <div class="col-12 md:col-4">
                    <Card class="summary-card">
                        <template #title><div class="text-sm text-gray-300">Geçen Yıl Toplam</div></template>
                        <template #content>
                            <div class="text-2xl font-bold text-gray-300">
                                {{ formatNumber(reportData.yearly_growth.previous_year_total) }}
                            </div>
                        </template>
                    </Card>
                </div>
                <div class="col-12 md:col-4">
                    <Card class="summary-card">
                        <template #title><div class="text-sm text-gray-300">Yıllık Büyüme</div></template>
                        <template #content>
                            <div class="text-2xl font-bold" 
                                 :class="reportData.yearly_growth.growth_rate >= 0 ? 'text-green-500' : 'text-red-500'">
                                {{ reportData.yearly_growth.growth_rate >= 0 ? '+' : '' }}{{ reportData.yearly_growth.growth_rate }}%
                            </div>
                        </template>
                    </Card>
                </div>
            </div>

            <!-- Trend Analysis -->
            <Card class="mb-4" v-if="reportData.trend_analysis">
                <template #title>📊 Trend Analizi</template>
                <template #content>
                    <div class="p-4 bg-gray-800 rounded border border-gray-700">
                        <p class="text-lg font-semibold mb-2">
                            Trend: <span class="text-green-400">{{ reportData.trend_analysis.trend }}</span>
                        </p>
                        <p class="text-gray-300">{{ reportData.trend_analysis.analysis }}</p>
                    </div>
                </template>
            </Card>

            <!-- Charts -->
            <div class="grid mb-4">
                <div class="col-12 lg:col-8">
                    <Card>
                        <template #title>📈 Aylık Üretim Trendi</template>
                        <template #content>
                            <div style="height: 350px;">
                                <Line v-if="monthlyChartData" :data="monthlyChartData" :options="chartOptions" />
                            </div>
                        </template>
                    </Card>
                </div>
                <div class="col-12 lg:col-4">
                    <Card>
                        <template #title>📊 Yıl Karşılaştırması</template>
                        <template #content>
                            <div style="height: 350px;">
                                <Bar v-if="yearCompareData" :data="yearCompareData" :options="chartOptions" />
                            </div>
                        </template>
                    </Card>
                </div>
            </div>

            <!-- Year Over Year -->
            <Card v-if="reportData.year_over_year">
                <template #title>Geçen Yıl Aynı Ay Karşılaştırması</template>
                <template #content>
                    <div class="mb-3">
                        <p class="text-gray-300">Fark: 
                            <span class="font-bold" :class="reportData.year_over_year.difference >= 0 ? 'text-green-500' : 'text-red-500'">
                                {{ formatNumber(reportData.year_over_year.difference) }}
                            </span>
                        </p>
                        <p class="text-gray-300">Değişim: 
                            <span class="font-bold" :class="reportData.year_over_year.percentage_change >= 0 ? 'text-green-500' : 'text-red-500'">
                                {{ reportData.year_over_year.percentage_change >= 0 ? '+' : '' }}{{ reportData.year_over_year.percentage_change }}%
                            </span>
                        </p>
                    </div>
                </template>
            </Card>
        </div>
    </div>
</template>

<style scoped>
.trend-analysis-report { padding: 1rem; }
.summary-card {
    background: rgba(255, 255, 255, 0.05);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.1);
}
</style>

