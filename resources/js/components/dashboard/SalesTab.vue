<template>
    <div class="sales-analysis">
        <!-- Satış KPI'ları -->
        <div class="sales-kpi-grid">
            <KpiCard
                icon="pi-chart-line"
                iconClass="sales"
                :value="salesData.monthlySales"
                label="Aylık Satış"
                format="currency" />

            <KpiCard
                icon="pi-users"
                iconClass="customers"
                :value="salesData.activeCustomers"
                label="Aktif Müşteri"
                format="number" />

            <KpiCard
                icon="pi-shopping-cart"
                iconClass="orders"
                :value="salesData.totalOrders"
                label="Toplam Sipariş"
                format="number" />
        </div>

        <!-- Satış Grafikleri -->
        <div class="sales-charts">
            <!-- Aylık Satış -->
            <ChartCard
                title="📈 Aylık Satış Performansı"
                v-model:viewMode="salesViewMode.monthly"
                :chartData="monthlySalesChart"
                :chartOptions="chartOptions"
                :tableData="monthlySalesData"
                :tableColumns="monthlySalesColumns"
                chartType="line" />

            <!-- Ödeme Türü -->
            <ChartCard
                title="💳 Ödeme Türü Dağılımı"
                v-model:viewMode="salesViewMode.payment"
                :chartData="paymentTypeChart"
                :chartOptions="doughnutOptions"
                :tableData="paymentTypeData"
                :tableColumns="paymentTypeColumns"
                chartType="doughnut"
                :tableRows="5" />

            <!-- En Çok Satan Ürünler -->
            <ChartCard
                title="🏆 En Çok Satan Ürünler"
                v-model:viewMode="salesViewMode.products"
                :chartData="topProductsChart"
                :chartOptions="chartOptions"
                :tableData="topProductsData"
                :tableColumns="topProductsColumns"
                chartType="bar" />

            <!-- Müşteri Analizi -->
            <ChartCard
                title="👥 Müşteri Satış Analizi"
                v-model:viewMode="salesViewMode.customers"
                :chartData="customerSalesChart"
                :chartOptions="chartOptions"
                :tableData="customerSalesData"
                :tableColumns="customerSalesColumns"
                chartType="horizontalBar" />
        </div>

        <!-- Filtre bölümü (isteğe bağlı) -->
        <FilterSection
            v-if="customers.length > 0"
            :filters="salesFilters"
            :customers="customers"
            :showDateRange="true"
            @filter="handleFilter" />
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import KpiCard from './shared/KpiCard.vue';
import ChartCard from './shared/ChartCard.vue';
import FilterSection from './shared/FilterSection.vue';

// Props
const props = defineProps({
    chartOptions: {
        type: Object,
        required: true
    },
    doughnutOptions: {
        type: Object,
        required: true
    },
    salesData: {
        type: Object,
        required: true
    },
    customers: {
        type: Array,
        default: () => []
    }
});

// Emits
const emit = defineEmits(['filter']);

// View Mode States
const salesViewMode = ref({
    monthly: 'chart',
    payment: 'chart',
    products: 'chart',
    customers: 'chart'
});

// Filters
const salesFilters = ref({
    dateRange: null,
    customer: null
});

// Chart Data
const monthlySalesChart = ref();
const paymentTypeChart = ref();
const topProductsChart = ref();
const customerSalesChart = ref();

// Table Data
const monthlySalesData = ref([
    { month: 'Ocak 2025', total_sales: 125000, order_count: 145, average_order: 862 },
    { month: 'Şubat 2025', total_sales: 138000, order_count: 156, average_order: 885 },
    { month: 'Mart 2025', total_sales: 142000, order_count: 162, average_order: 877 },
    { month: 'Nisan 2025', total_sales: 158000, order_count: 178, average_order: 888 },
    { month: 'Mayıs 2025', total_sales: 165000, order_count: 185, average_order: 892 },
    { month: 'Haziran 2025', total_sales: 172000, order_count: 198, average_order: 869 },
    { month: 'Temmuz 2025', total_sales: 145000, order_count: 165, average_order: 879 }
]);

const paymentTypeData = ref([
    { payment_type: 'pesin', count: 85, total_amount: 125000, percentage: 65 },
    { payment_type: 'borc', count: 32, total_amount: 45000, percentage: 25 },
    { payment_type: 'kismi', count: 18, total_amount: 25000, percentage: 10 }
]);

const topProductsData = ref([
    { product_name: 'Öz Ergül Plastik Büyük Boy', total_sold: 2500, revenue: 45000, avg_price: 18 },
    { product_name: 'M&R Orta Boy Kiloluk', total_sold: 1800, revenue: 28000, avg_price: 15.5 },
    { product_name: 'Lüx Öz Ergül Küçük Boy', total_sold: 1200, revenue: 32000, avg_price: 26.7 },
    { product_name: 'Kelebek Orta Boy', total_sold: 900, revenue: 18000, avg_price: 20 },
    { product_name: 'New Plast Büyük Boy', total_sold: 750, revenue: 15000, avg_price: 14 }
]);

const customerSalesData = ref([
    { customer_name: 'Ahmet Aslan Batman', total_orders: 12, total_amount: 25000, debt: 0 },
    { customer_name: 'Cengiz Aslan Deniz Plastik', total_orders: 8, total_amount: 18500, debt: 2500 },
    { customer_name: 'Donat Ambalaj Necat', total_orders: 15, total_amount: 32000, debt: 0 },
    { customer_name: 'Mayacı Faruk', total_orders: 6, total_amount: 12000, debt: 1500 },
    { customer_name: 'Hasan Abi Mersin', total_orders: 9, total_amount: 19500, debt: 800 },
    { customer_name: 'Tahir Abi Tarsus', total_orders: 11, total_amount: 22000, debt: 0 }
]);

// Table Columns
const monthlySalesColumns = ref([
    { field: 'month', header: 'Ay', sortable: true },
    { field: 'total_sales', header: 'Toplam Satış', sortable: true, type: 'currency' },
    { field: 'order_count', header: 'Sipariş Sayısı', sortable: true },
    { field: 'average_order', header: 'Ortalama Sipariş', sortable: true, type: 'currency' }
]);

const paymentTypeColumns = ref([
    {
        field: 'payment_type',
        header: 'Ödeme Türü',
        sortable: true,
        type: 'tag',
        severityMap: { 'pesin': 'success', 'borc': 'danger', 'kismi': 'warning' }
    },
    { field: 'count', header: 'Sipariş Sayısı', sortable: true },
    { field: 'total_amount', header: 'Toplam Tutar', sortable: true, type: 'currency' },
    { field: 'percentage', header: 'Yüzde %', sortable: true, type: 'progress' }
]);

const topProductsColumns = ref([
    { field: 'product_name', header: 'Ürün Adı', sortable: true },
    { field: 'total_sold', header: 'Toplam Satılan', sortable: true, type: 'badge', severity: 'success' },
    { field: 'revenue', header: 'Hasılat', sortable: true, type: 'currency' },
    { field: 'avg_price', header: 'Ortalama Fiyat', sortable: true, type: 'currency' }
]);

const customerSalesColumns = ref([
    { field: 'customer_name', header: 'Müşteri Adı', sortable: true },
    { field: 'total_orders', header: 'Toplam Sipariş', sortable: true },
    { field: 'total_amount', header: 'Toplam Tutar', sortable: true, type: 'currency' },
    { field: 'debt', header: 'Borç Durumu', sortable: true, type: 'currency' }
]);

// Chart Functions
const updateMonthlySalesChart = () => {
    monthlySalesChart.value = {
        labels: ['Ocak', 'Şubat', 'Mart', 'Nisan', 'Mayıs', 'Haziran', 'Temmuz'],
        datasets: [{
            label: 'Aylık Satış (₺)',
            data: [125000, 138000, 142000, 158000, 165000, 172000, 145000],
            borderColor: '#8B5CF6',
            backgroundColor: 'rgba(139, 92, 246, 0.1)',
            tension: 0.4,
            borderWidth: 3,
            pointBackgroundColor: '#8B5CF6',
            pointBorderColor: '#FFFFFF',
            pointBorderWidth: 2,
            pointRadius: 6,
            fill: true
        }]
    };
};

const updatePaymentTypeChart = () => {
    paymentTypeChart.value = {
        labels: ['Peşin', 'Borç', 'Kısmi'],
        datasets: [{
            data: [125000, 45000, 25000],
            backgroundColor: [
                'rgba(16, 185, 129, 0.8)',
                'rgba(239, 68, 68, 0.8)',
                'rgba(245, 158, 11, 0.8)'
            ],
            borderColor: [
                '#10B981',
                '#EF4444',
                '#F59E0B'
            ],
            borderWidth: 3,
            hoverBorderWidth: 4
        }]
    };
};

const updateTopProductsChart = () => {
    topProductsChart.value = {
        labels: ['Öz Ergül Büyük', 'M&R Orta Boy', 'Lüx Öz Ergül', 'Kelebek Orta', 'New Plast'],
        datasets: [{
            label: 'Satış Miktarı',
            data: [2500, 1800, 1200, 900, 750],
            backgroundColor: [
                'rgba(245, 158, 11, 0.8)',
                'rgba(59, 130, 246, 0.8)',
                'rgba(16, 185, 129, 0.8)',
                'rgba(139, 92, 246, 0.8)',
                'rgba(236, 72, 153, 0.8)'
            ],
            borderColor: [
                '#F59E0B',
                '#3B82F6',
                '#10B981',
                '#8B5CF6',
                '#EC4899'
            ],
            borderWidth: 2,
            borderRadius: 8,
            borderSkipped: false
        }]
    };
};

const updateCustomerSalesChart = () => {
    customerSalesChart.value = {
        labels: ['Ahmet Aslan', 'Cengiz Aslan', 'Donat Ambalaj', 'Mayacı Faruk', 'Hasan Abi', 'Tahir Abi'],
        datasets: [{
            label: 'Toplam Alışveriş (₺)',
            data: [25000, 18500, 32000, 12000, 19500, 22000],
            backgroundColor: 'rgba(236, 72, 153, 0.8)',
            borderColor: '#EC4899',
            borderWidth: 2,
            borderRadius: 6,
            borderSkipped: false
        }]
    };
};

// Event Handlers
const handleFilter = (filters) => {
    console.log('Filtering sales data:', filters);
    emit('filter', filters);
};

// Initialize
onMounted(() => {
    updateAllCharts();
});

const updateAllCharts = () => {
    updateMonthlySalesChart();
    updatePaymentTypeChart();
    updateTopProductsChart();
    updateCustomerSalesChart();
};

// Expose functions
defineExpose({
    updateCharts: updateAllCharts,
    updateWithFilteredData: (data) => {
        // API'den gelen filtrelenmiş data ile güncelle
        console.log('Updating sales with filtered data:', data);

        if (data.monthly) {
            monthlySalesData.value = data.monthly;
        }
        if (data.payment_stats) {
            paymentTypeData.value = [
                { payment_type: 'pesin', count: data.payment_stats.pesin_count, total_amount: data.payment_stats.pesin_amount, percentage: data.payment_stats.pesin_percentage },
                { payment_type: 'borc', count: data.payment_stats.borc_count, total_amount: data.payment_stats.borc_amount, percentage: data.payment_stats.borc_percentage },
                { payment_type: 'kismi', count: data.payment_stats.kismi_count, total_amount: data.payment_stats.kismi_amount, percentage: data.payment_stats.kismi_percentage }
            ];
        }
        if (data.top_products) {
            topProductsData.value = data.top_products;
        }
        if (data.customers) {
            customerSalesData.value = data.customers;
        }

        updateAllCharts();
    }
});
</script>

<style scoped>
.sales-analysis {
    display: flex;
    flex-direction: column;
    gap: 30px;
}

.sales-kpi-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 20px;
    margin-bottom: 10px;
}

.sales-charts {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
    gap: 25px;
    margin-bottom: 20px;
}

@media (max-width: 768px) {
    .sales-charts {
        grid-template-columns: 1fr;
    }

    .sales-kpi-grid {
        grid-template-columns: 1fr;
    }
}

/* Custom styling for sales specific elements */
.sales-analysis :deep(.kpi-icon.sales) {
    color: #8B5CF6 !important;
}

.sales-analysis :deep(.kpi-icon.customers) {
    color: #EC4899 !important;
}

.sales-analysis :deep(.kpi-icon.orders) {
    color: #06B6D4 !important;
}

/* Enhanced chart styling */
.sales-charts :deep(.p-chart) {
    min-height: 350px;
}
</style>
