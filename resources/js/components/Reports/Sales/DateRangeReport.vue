<script setup>
import { ref, computed, onMounted } from 'vue';
import { useSalesReports } from '@/composables/useSalesReports';
import Calendar from 'primevue/calendar';
import Button from 'primevue/button';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Card from 'primevue/card';
import { Line, Pie } from 'vue-chartjs';
import { Chart as ChartJS, CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend, ArcElement } from 'chart.js';

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend, ArcElement);

const { loading, error, fetchDateRangeReport } = useSalesReports();

const startDate = ref(new Date(new Date().getFullYear(), new Date().getMonth(), 1));
const endDate = ref(new Date());
const reportData = ref(null);

const formatCurrency = (value) => {
    return new Intl.NumberFormat('tr-TR', { style: 'currency', currency: 'TRY' }).format(value);
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('tr-TR');
};

const dailyChartData = computed(() => {
    if (!reportData.value?.data?.daily_distribution) return null;
    
    const distribution = reportData.value.data.daily_distribution;
    return {
        labels: distribution.map(d => formatDate(d.date)),
        datasets: [{
            label: 'Günlük Satış (TL)',
            data: distribution.map(d => d.total),
            borderColor: '#4F46E5',
            backgroundColor: 'rgba(79, 70, 229, 0.1)',
            tension: 0.4,
        }]
    };
});

const paymentChartData = computed(() => {
    if (!reportData.value?.data?.payment_distribution) return null;
    
    const distribution = reportData.value.data.payment_distribution;
    const paymentLabels = {
        'pesin': 'Peşin',
        'borc': 'Borç',
        'kismi': 'Kısmi'
    };
    
    return {
        labels: distribution.map(d => paymentLabels[d.payment_type] || d.payment_type),
        datasets: [{
            data: distribution.map(d => d.total),
            backgroundColor: ['#10B981', '#EF4444', '#F59E0B'],
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
        const start = startDate.value.toISOString().split('T')[0];
        const end = endDate.value.toISOString().split('T')[0];
        reportData.value = await fetchDateRangeReport(start, end);
    } catch (err) {
        console.error('Rapor yüklenirken hata:', err);
    }
};

onMounted(() => {
    loadReport();
});
</script>

<template>
    <div class="date-range-report">
        <!-- Filtreler -->
        <div class="filters mb-4 flex gap-4 align-items-center">
            <div class="field">
                <label for="startDate" class="block mb-2">Başlangıç Tarihi</label>
                <Calendar v-model="startDate" inputId="startDate" dateFormat="dd/mm/yy" showIcon />
            </div>
            <div class="field">
                <label for="endDate" class="block mb-2">Bitiş Tarihi</label>
                <Calendar v-model="endDate" inputId="endDate" dateFormat="dd/mm/yy" showIcon />
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
                            <div class="text-sm text-gray-200">Toplam Satış</div>
                        </template>
                        <template #content>
                            <div class="text-2xl font-bold text-primary">
                                {{ formatCurrency(reportData.data.summary.total_sales) }}
                            </div>
                        </template>
                    </Card>
                </div>
                <div class="col-12 md:col-3">
                    <Card>
                        <template #title>
                            <div class="text-sm text-gray-200">Toplam Ürün Adedi</div>
                        </template>
                        <template #content>
                            <div class="text-2xl font-bold text-green-600">
                                {{ reportData.data.summary.total_products }}
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
                <div class="col-12 md:col-3">
                    <Card>
                        <template #title>
                            <div class="text-sm text-gray-200">Ortalama Satış</div>
                        </template>
                        <template #content>
                            <div class="text-2xl font-bold text-orange-600">
                                {{ formatCurrency(reportData.data.summary.average_sale) }}
                            </div>
                        </template>
                    </Card>
                </div>
            </div>

            <!-- Grafikler -->
            <div class="grid mb-4">
                <div class="col-12 md:col-8">
                    <Card>
                        <template #title>Günlük Satış Dağılımı</template>
                        <template #content>
                            <div style="height: 300px;">
                                <Line v-if="dailyChartData" :data="dailyChartData" :options="chartOptions" />
                            </div>
                        </template>
                    </Card>
                </div>
                <div class="col-12 md:col-4">
                    <Card>
                        <template #title>Ödeme Yöntemleri</template>
                        <template #content>
                            <div style="height: 300px;">
                                <Pie v-if="paymentChartData" :data="paymentChartData" :options="chartOptions" />
                            </div>
                        </template>
                    </Card>
                </div>
            </div>

            <!-- En Çok Satılan Ürünler -->
            <Card>
                <template #title>En Çok Satılan Ürünler</template>
                <template #content>
                    <DataTable :value="reportData.data.top_products" stripedRows>
                        <Column field="product_name" header="Ürün Adı"></Column>
                        <Column field="total_quantity" header="Toplam Adet"></Column>
                        <Column field="total_revenue" header="Toplam Ciro">
                            <template #body="slotProps">
                                {{ formatCurrency(slotProps.data.total_revenue) }}
                            </template>
                        </Column>
                    </DataTable>
                </template>
            </Card>
        </div>
    </div>
</template>

<style scoped>
.date-range-report {
    padding: 1rem;
}

.field label {
    font-weight: 600;
}
</style>
