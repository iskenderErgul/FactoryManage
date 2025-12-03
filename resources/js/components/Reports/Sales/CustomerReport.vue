<script setup>
import { ref, computed, onMounted } from 'vue';
import { useSalesReports } from '@/composables/useSalesReports';
import axios from 'axios';
import Dropdown from 'primevue/dropdown';
import Calendar from 'primevue/calendar';
import Button from 'primevue/button';
import Card from 'primevue/card';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import { Bar } from 'vue-chartjs';
import { Chart as ChartJS, CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend } from 'chart.js';

ChartJS.register(CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend);

const { loading, error, fetchCustomerReport } = useSalesReports();

const customers = ref([]);
const selectedCustomer = ref(null);
const startDate = ref(null);
const endDate = ref(null);
const reportData = ref(null);

const formatCurrency = (value) => {
    return new Intl.NumberFormat('tr-TR', { style: 'currency', currency: 'TRY' }).format(value);
};

const spendingChartData = computed(() => {
    if (!reportData.value?.data?.spending_chart) return null;
    
    const chart = reportData.value.data.spending_chart;
    return {
        labels: chart.map(d => d.month),
        datasets: [{
            label: 'Aylık Harcama (TL)',
            data: chart.map(d => d.total),
            backgroundColor: '#8B5CF6',
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

const loadCustomers = async () => {
    try {
        const response = await axios.get('/api/customers');
        customers.value = response.data.map(c => ({ label: c.name, value: c.id }));
    } catch (err) {
        console.error('Müşteriler yüklenirken hata:', err);
    }
};

const loadReport = async () => {
    if (!selectedCustomer.value) {
        alert('Lütfen bir müşteri seçin');
        return;
    }

    try {
        const filters = {};
        if (startDate.value && endDate.value) {
            filters.startDate = startDate.value.toISOString().split('T')[0];
            filters.endDate = endDate.value.toISOString().split('T')[0];
        }
        reportData.value = await fetchCustomerReport(selectedCustomer.value, filters);
    } catch (err) {
        console.error('Rapor yüklenirken hata:', err);
    }
};

onMounted(() => {
    loadCustomers();
});
</script>

<template>
    <div class="customer-report">
        <!-- Filtreler -->
        <div class="filters mb-4 flex gap-4 align-items-center flex-wrap">
            <div class="field">
                <label for="customer" class="block mb-2">Müşteri</label>
                <Dropdown 
                    v-model="selectedCustomer" 
                    :options="customers" 
                    optionLabel="label" 
                    optionValue="value" 
                    inputId="customer"
                    placeholder="Müşteri seçin"
                    filter
                    style="min-width: 250px;"
                />
            </div>
            <div class="field">
                <label for="startDate" class="block mb-2">Başlangıç Tarihi (Opsiyonel)</label>
                <Calendar v-model="startDate" inputId="startDate" dateFormat="dd/mm/yy" showIcon />
            </div>
            <div class="field">
                <label for="endDate" class="block mb-2">Bitiş Tarihi (Opsiyonel)</label>
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
                <div class="col-12 md:col-4">
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
                <div class="col-12 md:col-4">
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
                <div class="col-12 md:col-4">
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

            <!-- Satın Alma Alışkanlığı -->
            <Card class="mb-4">
                <template #title>Satın Alma Alışkanlığı Analizi</template>
                <template #content>
                    <p class="text-lg">{{ reportData.data.purchase_habits.analysis }}</p>
                    <div class="grid mt-3">
                        <div class="col-12 md:col-4">
                            <p class="text-gray-200">Favori Ödeme Yöntemi</p>
                            <p class="font-bold">{{ reportData.data.purchase_habits.favorite_payment_method || 'Bilinmiyor' }}</p>
                        </div>
                        <div class="col-12 md:col-4">
                            <p class="text-gray-200">Ortalama Alışveriş Aralığı</p>
                            <p class="font-bold">{{ reportData.data.purchase_habits.average_days_between_purchases }} gün</p>
                        </div>
                        <div class="col-12 md:col-4">
                            <p class="text-gray-200">En Çok Alışveriş Yapılan Gün</p>
                            <p class="font-bold">{{ reportData.data.purchase_habits.most_purchased_day_of_week || 'Bilinmiyor' }}</p>
                        </div>
                    </div>
                </template>
            </Card>

            <!-- Grafikler -->
            <div class="grid mb-4">
                <div class="col-12">
                    <Card>
                        <template #title>Aylık Harcama Grafiği</template>
                        <template #content>
                            <div style="height: 300px;">
                                <Bar v-if="spendingChartData" :data="spendingChartData" :options="chartOptions" />
                            </div>
                        </template>
                    </Card>
                </div>
            </div>

            <!-- Satılan Ürünler -->
            <Card>
                <template #title>Satılan Ürünler</template>
                <template #content>
                    <DataTable :value="reportData.data.products" stripedRows>
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
.customer-report {
    padding: 1rem;
}

.field label {
    font-weight: 600;
}
</style>
