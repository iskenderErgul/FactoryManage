<script setup>
import { ref, computed, onMounted } from 'vue';
import { useSalesReports } from '@/composables/useSalesReports';
import Card from 'primevue/card';
import { Line, Bar } from 'vue-chartjs';
import { Chart as ChartJS, CategoryScale, LinearScale, PointElement, LineElement, BarElement, Title, Tooltip, Legend } from 'chart.js';

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, BarElement, Title, Tooltip, Legend);

const { loading, error, fetchTrendReport } = useSalesReports();

const reportData = ref(null);

const formatCurrency = (value) => {
    return new Intl.NumberFormat('tr-TR', { style: 'currency', currency: 'TRY' }).format(value);
};

const last12MonthsChartData = computed(() => {
    if (!reportData.value?.data?.last_12_months) return null;
    
    const months = reportData.value.data.last_12_months;
    return {
        labels: months.map(m => m.month),
        datasets: [{
            label: 'Aylık Satış (TL)',
            data: months.map(m => m.total),
            borderColor: '#4F46E5',
            backgroundColor: 'rgba(79, 70, 229, 0.1)',
            tension: 0.4,
        }]
    };
});

const yearOverYearChartData = computed(() => {
    if (!reportData.value?.data?.year_over_year) return null;
    
    const yoy = reportData.value.data.year_over_year;
    const currentMonth = new Date().toLocaleString('tr-TR', { month: 'long' });
    
    return {
        labels: [`${currentMonth} ${new Date().getFullYear() - 1}`, `${currentMonth} ${new Date().getFullYear()}`],
        datasets: [{
            label: 'Satış (TL)',
            data: [yoy.previous_year_month_sales, yoy.current_month_sales],
            backgroundColor: ['#94A3B8', '#4F46E5'],
        }]
    };
});

const lineChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            position: 'top',
        },
    },
};

const barChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            position: 'top',
        },
    },
};

const loadReport = async () => {
    try {
        reportData.value = await fetchTrendReport();
    } catch (err) {
        console.error('Rapor yüklenirken hata:', err);
    }
};

onMounted(() => {
    loadReport();
});
</script>

<template>
    <div class="trend-report">
        <!-- Yükleniyor -->
        <div v-if="loading" class="text-center py-6">
            <i class="pi pi-spin pi-spinner text-4xl text-primary"></i>
            <p class="mt-3">Rapor yükleniyor...</p>
        </div>

        <!-- Hata -->
        <div v-else-if="error" class="p-4 bg-red-100 text-red-800 rounded">
            {{ error }}
        </div>

        <!-- Rapor İçeriği -->
        <div v-else-if="reportData?.data" class="report-content">
            <!-- Yıllık Büyüme Oranı -->
            <div class="grid mb-4">
                <div class="col-12 md:col-4">
                    <Card>
                        <template #title>
                            <div class="text-sm text-gray-200">Bu Yıl Satış</div>
                        </template>
                        <template #content>
                            <div class="text-2xl font-bold text-primary">
                                {{ formatCurrency(reportData.data.yearly_growth.current_year_sales) }}
                            </div>
                        </template>
                    </Card>
                </div>
                <div class="col-12 md:col-4">
                    <Card>
                        <template #title>
                            <div class="text-sm text-gray-200">Geçen Yıl Satış</div>
                        </template>
                        <template #content>
                            <div class="text-2xl font-bold text-gray-200">
                                {{ formatCurrency(reportData.data.yearly_growth.previous_year_sales) }}
                            </div>
                        </template>
                    </Card>
                </div>
                <div class="col-12 md:col-4">
                    <Card>
                        <template #title>
                            <div class="text-sm text-gray-200">Yıllık Büyüme Oranı</div>
                        </template>
                        <template #content>
                            <div 
                                class="text-2xl font-bold"
                                :class="reportData.data.yearly_growth.growth_rate >= 0 ? 'text-green-600' : 'text-red-600'"
                            >
                                {{ reportData.data.yearly_growth.growth_rate >= 0 ? '+' : '' }}{{ reportData.data.yearly_growth.growth_rate }}%
                            </div>
                        </template>
                    </Card>
                </div>
            </div>

            <!-- Trend Analizi -->
            <Card class="mb-4">
                <template #title>📊 Trend Analizi</template>
                <template #content>
                    <div class="p-4 bg-gray-800 rounded border border-gray-700">
                        <p class="text-lg font-semibold mb-2">
                            Trend: <span class="text-primary">{{ reportData.data.trend_analysis.trend }}</span>
                        </p>
                        <p class="text-gray-200">{{ reportData.data.trend_analysis.analysis }}</p>
                    </div>
                </template>
            </Card>

            <!-- Son 12 Ay Satış Grafiği -->
            <Card class="mb-4">
                <template #title>Son 12 Ay Satış Grafiği</template>
                <template #content>
                    <div style="height: 350px;">
                        <Line v-if="last12MonthsChartData" :data="last12MonthsChartData" :options="lineChartOptions" />
                    </div>
                </template>
            </Card>

            <!-- Yıl Karşılaştırması -->
            <Card>
                <template #title>Geçen Yıl Aynı Ay Karşılaştırması</template>
                <template #content>
                    <div class="mb-3">
                        <p class="text-gray-200">Fark: 
                            <span 
                                class="font-bold"
                                :class="reportData.data.year_over_year.difference >= 0 ? 'text-green-600' : 'text-red-600'"
                            >
                                {{ formatCurrency(reportData.data.year_over_year.difference) }}
                            </span>
                        </p>
                        <p class="text-gray-200">Değişim: 
                            <span 
                                class="font-bold"
                                :class="reportData.data.year_over_year.percentage_change >= 0 ? 'text-green-600' : 'text-red-600'"
                            >
                                {{ reportData.data.year_over_year.percentage_change >= 0 ? '+' : '' }}{{ reportData.data.year_over_year.percentage_change }}%
                            </span>
                        </p>
                    </div>
                    <div style="height: 300px;">
                        <Bar v-if="yearOverYearChartData" :data="yearOverYearChartData" :options="barChartOptions" />
                    </div>
                </template>
            </Card>
        </div>
    </div>
</template>

<style scoped>
.trend-report {
    padding: 1rem;
}
</style>
