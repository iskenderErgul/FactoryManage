<script setup>
import { ref, computed, onMounted } from 'vue';
import { useSalesReports } from '@/composables/useSalesReports';
import Dropdown from 'primevue/dropdown';
import Button from 'primevue/button';
import Card from 'primevue/card';
import { Line } from 'vue-chartjs';
import { Chart as ChartJS, CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend } from 'chart.js';

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend);

const { loading, error, fetchMonthlyReport } = useSalesReports();

const currentYear = new Date().getFullYear();
const currentMonth = new Date().getMonth() + 1;

const selectedYear = ref(currentYear);
const selectedMonth = ref(currentMonth);
const reportData = ref(null);

const years = ref([]);
for (let i = currentYear; i >= currentYear - 5; i--) {
    years.value.push({ label: i.toString(), value: i });
}

const months = ref([
    { label: 'Ocak', value: 1 },
    { label: 'Şubat', value: 2 },
    { label: 'Mart', value: 3 },
    { label: 'Nisan', value: 4 },
    { label: 'Mayıs', value: 5 },
    { label: 'Haziran', value: 6 },
    { label: 'Temmuz', value: 7 },
    { label: 'Ağustos', value: 8 },
    { label: 'Eylül', value: 9 },
    { label: 'Ekim', value: 10 },
    { label: 'Kasım', value: 11 },
    { label: 'Aralık', value: 12 },
]);

const formatCurrency = (value) => {
    return new Intl.NumberFormat('tr-TR', { style: 'currency', currency: 'TRY' }).format(value);
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('tr-TR');
};

const trendChartData = computed(() => {
    if (!reportData.value?.data?.trend) return null;
    
    const trend = reportData.value.data.trend;
    return {
        labels: trend.map(d => formatDate(d.date)),
        datasets: [{
            label: 'Günlük Satış (TL)',
            data: trend.map(d => d.total),
            borderColor: '#10B981',
            backgroundColor: 'rgba(16, 185, 129, 0.1)',
            tension: 0.4,
        }]
    };
});

const chartOptions = {
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
        reportData.value = await fetchMonthlyReport(selectedYear.value, selectedMonth.value);
    } catch (err) {
        console.error('Rapor yüklenirken hata:', err);
    }
};

onMounted(() => {
    loadReport();
});
</script>

<template>
    <div class="monthly-report">
        <!-- Filtreler -->
        <div class="filters mb-4 flex gap-4 align-items-center">
            <div class="field">
                <label for="year" class="block mb-2">Yıl</label>
                <Dropdown v-model="selectedYear" :options="years" optionLabel="label" optionValue="value" inputId="year" />
            </div>
            <div class="field">
                <label for="month" class="block mb-2">Ay</label>
                <Dropdown v-model="selectedMonth" :options="months" optionLabel="label" optionValue="value" inputId="month" />
            </div>
            <div class="field">
                <label class="block mb-2">&nbsp;</label>
                <Button label="Raporu Getir" icon="pi pi-search" @click="loadReport" :loading="loading" />
            </div>
        </div>

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
            <!-- Özet Kartlar -->
            <div class="grid mb-4">
                <div class="col-12 md:col-3">
                    <Card>
                        <template #title>
                            <div class="text-sm text-gray-200">Aylık Ciro</div>
                        </template>
                        <template #content>
                            <div class="text-2xl font-bold text-primary">
                                {{ formatCurrency(reportData.data.summary.total_revenue) }}
                            </div>
                        </template>
                    </Card>
                </div>
                <div class="col-12 md:col-3">
                    <Card>
                        <template #title>
                            <div class="text-sm text-gray-200">Toplam Tahsilat</div>
                        </template>
                        <template #content>
                            <div class="text-2xl font-bold text-green-600">
                                {{ formatCurrency(reportData.data.summary.total_paid) }}
                            </div>
                        </template>
                    </Card>
                </div>
                <div class="col-12 md:col-3">
                    <Card>
                        <template #title>
                            <div class="text-sm text-gray-200">Açık Bakiye</div>
                        </template>
                        <template #content>
                            <div class="text-2xl font-bold text-orange-600">
                                {{ formatCurrency(reportData.data.summary.open_balance) }}
                            </div>
                        </template>
                    </Card>
                </div>
                <div class="col-12 md:col-3">
                    <Card>
                        <template #title>
                            <div class="text-sm text-gray-200">Toplam İşlem</div>
                        </template>
                        <template #content>
                            <div class="text-2xl font-bold text-blue-600">
                                {{ reportData.data.summary.total_transactions }}
                            </div>
                        </template>
                    </Card>
                </div>
            </div>

            <!-- En Çok Satış Yapılan Müşteri ve Ürün -->
            <div class="grid mb-4">
                <div class="col-12 md:col-6">
                    <Card>
                        <template #title>En Çok Satış Yapılan Müşteri</template>
                        <template #content>
                            <div v-if="reportData.data.top_customer">
                                <p class="text-xl font-bold">{{ reportData.data.top_customer.customer?.name || 'Bilinmeyen' }}</p>
                                <p class="text-gray-200">Toplam: {{ formatCurrency(reportData.data.top_customer.total) }}</p>
                                <p class="text-gray-200">İşlem Sayısı: {{ reportData.data.top_customer.count }}</p>
                            </div>
                            <div v-else class="text-gray-400">Veri bulunamadı</div>
                        </template>
                    </Card>
                </div>
                <div class="col-12 md:col-6">
                    <Card>
                        <template #title>En Çok Satılan Ürün</template>
                        <template #content>
                            <div v-if="reportData.data.top_product">
                                <p class="text-xl font-bold">{{ reportData.data.top_product.product_name }}</p>
                                <p class="text-gray-200">Toplam Adet: {{ reportData.data.top_product.total_quantity }}</p>
                                <p class="text-gray-200">Ciro: {{ formatCurrency(reportData.data.top_product.total_revenue) }}</p>
                            </div>
                            <div v-else class="text-gray-400">Veri bulunamadı</div>
                        </template>
                    </Card>
                </div>
            </div>

            <!-- Ay İçi Trend Grafiği -->
            <Card>
                <template #title>Ay İçi Satış Trendi</template>
                <template #content>
                    <div style="height: 350px;">
                        <Line v-if="trendChartData" :data="trendChartData" :options="chartOptions" />
                    </div>
                </template>
            </Card>
        </div>
    </div>
</template>

<style scoped>
.monthly-report {
    padding: 1rem;
}

.field label {
    font-weight: 600;
}
</style>
