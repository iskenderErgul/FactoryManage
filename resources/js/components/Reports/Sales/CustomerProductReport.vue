<script setup>
import { ref, computed, onMounted } from 'vue';
import { useSalesReports } from '@/composables/useSalesReports';
import axios from 'axios';
import Dropdown from 'primevue/dropdown';
import Button from 'primevue/button';
import Card from 'primevue/card';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import { Bar } from 'vue-chartjs';
import { Chart as ChartJS, CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend } from 'chart.js';

ChartJS.register(CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend);

const { loading, error, fetchCustomerProductReport } = useSalesReports();

const customers = ref([]);
const selectedCustomer = ref(null);
const reportData = ref(null);

const formatCurrency = (value) => {
    return new Intl.NumberFormat('tr-TR', { style: 'currency', currency: 'TRY' }).format(value);
};

const topProductsChartData = computed(() => {
    if (!reportData.value?.data?.top_products) return null;
    
    const products = reportData.value.data.top_products.slice(0, 10);
    return {
        labels: products.map(p => p.product_name),
        datasets: [{
            label: 'Satın Alınan Adet',
            data: products.map(p => p.total_quantity),
            backgroundColor: '#F59E0B',
        }]
    };
});

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    indexAxis: 'y',
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
        reportData.value = await fetchCustomerProductReport(selectedCustomer.value, {});
    } catch (err) {
        console.error('Rapor yüklenirken hata:', err);
    }
};

onMounted(() => {
    loadCustomers();
});
</script>

<template>
    <div class="customer-product-report">
        <!-- Filtreler -->
        <div class="filters mb-4 flex gap-4 align-items-center">
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
            <!-- En Çok Satın Alınan Ürünler Grafiği -->
            <Card class="mb-4">
                <template #title>En Çok Satın Alınan Ürünler (Top 10)</template>
                <template #content>
                    <div style="height: 400px;">
                        <Bar v-if="topProductsChartData" :data="topProductsChartData" :options="chartOptions" />
                    </div>
                </template>
            </Card>

            <!-- Ürün Bazlı Satış Tablosu -->
            <Card>
                <template #title>Ürün Bazlı Satış Detayı</template>
                <template #content>
                    <DataTable :value="reportData.data.product_sales" stripedRows paginator :rows="10">
                        <Column field="product_name" header="Ürün Adı" sortable></Column>
                        <Column field="total_quantity" header="Toplam Miktar" sortable></Column>
                        <Column field="total_revenue" header="Toplam Ciro" sortable>
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
.customer-product-report {
    padding: 1rem;
}

.field label {
    font-weight: 600;
}
</style>
