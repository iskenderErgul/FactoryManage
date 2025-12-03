<script setup>
import { ref, computed, onMounted } from 'vue';
import { useSalesReports } from '@/composables/useSalesReports';
import axios from 'axios';
import Dropdown from 'primevue/dropdown';
import Button from 'primevue/button';
import Card from 'primevue/card';
import { Line } from 'vue-chartjs';
import { Chart as ChartJS, CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend } from 'chart.js';

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend);

const { loading, error, fetchCustomerPaymentReport } = useSalesReports();

const customers = ref([]);
const selectedCustomer = ref(null);
const reportData = ref(null);

const formatCurrency = (value) => {
    return new Intl.NumberFormat('tr-TR', { style: 'currency', currency: 'TRY' }).format(value);
};

const paymentChartData = computed(() => {
    if (!reportData.value?.data?.payment_chart) return null;
    
    const chart = reportData.value.data.payment_chart;
    return {
        labels: chart.map(d => d.month),
        datasets: [
            {
                label: 'Ödemeler',
                data: chart.map(d => d.payments),
                borderColor: '#10B981',
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                tension: 0.4,
            },
            {
                label: 'Borçlar',
                data: chart.map(d => d.debts),
                borderColor: '#EF4444',
                backgroundColor: 'rgba(239, 68, 68, 0.1)',
                tension: 0.4,
            }
        ]
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
        reportData.value = await fetchCustomerPaymentReport(selectedCustomer.value, {});
    } catch (err) {
        console.error('Rapor yüklenirken hata:', err);
    }
};

onMounted(() => {
    loadCustomers();
});
</script>

<template>
    <div class="customer-payment-report">
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
            <!-- Ödeme Özet Kartları -->
            <div class="grid mb-4">
                <div class="col-12 md:col-3">
                    <Card>
                        <template #title>
                            <div class="text-sm text-gray-200">Toplam Ciro</div>
                        </template>
                        <template #content>
                            <div class="text-2xl font-bold text-primary">
                                {{ formatCurrency(reportData.data.payment_summary.total_revenue) }}
                            </div>
                        </template>
                    </Card>
                </div>
                <div class="col-12 md:col-3">
                    <Card>
                        <template #title>
                            <div class="text-sm text-gray-200">Peşin Ödeme</div>
                        </template>
                        <template #content>
                            <div class="text-2xl font-bold text-green-600">
                                {{ formatCurrency(reportData.data.payment_summary.cash_payment) }}
                            </div>
                        </template>
                    </Card>
                </div>
                <div class="col-12 md:col-3">
                    <Card>
                        <template #title>
                            <div class="text-sm text-gray-200">Borçtan Ödenen</div>
                        </template>
                        <template #content>
                            <div class="text-2xl font-bold text-blue-600">
                                {{ formatCurrency(reportData.data.payment_summary.paid_from_debt) }}
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
                                {{ formatCurrency(reportData.data.payment_summary.open_balance) }}
                            </div>
                        </template>
                    </Card>
                </div>
            </div>

            <!-- Ödeme Alışkanlığı -->
            <Card class="mb-4">
                <template #title>Ödeme Alışkanlığı Analizi</template>
                <template #content>
                    <p class="text-lg mb-3">{{ reportData.data.payment_habits.analysis }}</p>
                    <div class="grid">
                        <div class="col-12 md:col-6">
                            <p class="text-gray-200">Peşin Ödeme Sayısı</p>
                            <p class="font-bold text-2xl">{{ reportData.data.payment_habits.cash_count }}</p>
                        </div>
                        <div class="col-12 md:col-6">
                            <p class="text-gray-200">Ödeme Güvenilirliği</p>
                            <p class="font-bold text-2xl">%{{ reportData.data.payment_habits.payment_reliability }}</p>
                        </div>
                    </div>
                </template>
            </Card>

            <!-- Dönemsel Ödeme Grafiği -->
            <Card>
                <template #title>Dönemsel Ödeme Grafiği</template>
                <template #content>
                    <div style="height: 350px;">
                        <Line v-if="paymentChartData" :data="paymentChartData" :options="chartOptions" />
                    </div>
                </template>
            </Card>
        </div>
    </div>
</template>

<style scoped>
.customer-payment-report {
    padding: 1rem;
}

.field label {
    font-weight: 600;
}
</style>
