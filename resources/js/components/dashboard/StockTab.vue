<template>
    <div class="stock-management mt-4">
        <!-- Stok Durumu -->
        <ChartCard
            title="📊 Mevcut Stok Durumu"
            v-model:viewMode="stockViewMode.current"
            :chartData="currentStockChart"
            :chartOptions="chartOptions"
            :tableData="currentStockData"
            :tableColumns="currentStockColumns"
            chartType="bar" />
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import ChartCard from './shared/ChartCard.vue';

// Props
const props = defineProps({
    chartOptions: {
        type: Object,
        required: true
    },
    stockData: {
        type: Object,
        required: true
    },
    products: {
        type: Array,
        default: () => []
    }
});

// Emits
const emit = defineEmits(['filter']);

// View Mode States
const stockViewMode = ref({
    current: 'chart'
});

// Chart Data
const currentStockChart = ref();

// Table Data
const currentStockData = ref([
    { product_name: 'Öz Ergül Plastik Büyük Boy', product_type: 'Ara kalite', stock_quantity: 99999949, status: 'Normal', last_updated: '2025-07-29' },
    { product_name: 'M&R Orta Boy Kiloluk', product_type: '2.Kalite', stock_quantity: 99999999, status: 'Normal', last_updated: '2025-07-29' },
    { product_name: 'Lüx Öz Ergül Küçük Boy', product_type: '1.Kalite', stock_quantity: 8500, status: 'Düşük', last_updated: '2025-07-29' },
    { product_name: 'New Plast Büyük Boy', product_type: '3.Kalite', stock_quantity: 650, status: 'Kritik', last_updated: '2025-07-29' }
]);

// Table Columns
const currentStockColumns = ref([
    { field: 'product_name', header: 'Ürün Adı', sortable: true },
    { field: 'product_type', header: 'Ürün Tipi', sortable: true, type: 'tag' },
    {
        field: 'stock_quantity',
        header: 'Mevcut Stok',
        sortable: true,
        type: 'badge',
        customSeverity: (value) => value < 1000 ? 'danger' : value < 10000 ? 'warning' : 'success'
    },
    {
        field: 'status',
        header: 'Durum',
        sortable: true,
        type: 'tag',
        severityMap: { 'Kritik': 'danger', 'Düşük': 'warning', 'Normal': 'success' }
    },
    { field: 'last_updated', header: 'Son Güncelleme', sortable: true, type: 'date' }
]);

// Chart Functions
const updateCurrentStockChart = () => {
    const stockDataValues = [99999949, 99999999, 8500, 650];
    const statusColors = ['#10B981', '#10B981', '#F59E0B', '#EF4444']; // Normal, Normal, Düşük, Kritik

    currentStockChart.value = {
        labels: ['Öz Ergül Büyük', 'M&R Orta Boy', 'Lüx Öz Ergül', 'New Plast'],
        datasets: [{
            label: 'Stok Miktarı',
            data: stockDataValues,
            backgroundColor: statusColors.map(color => color + '80'), // 50% opacity
            borderColor: statusColors,
            borderWidth: 2,
            borderRadius: 6,
            borderSkipped: false
        }]
    };
};

// Initialize
onMounted(() => {
    updateCurrentStockChart();
});

const updateAllCharts = () => {
    updateCurrentStockChart();
};

// Expose functions
defineExpose({
    updateCharts: updateAllCharts,
    updateWithFilteredData: (data) => {
        // API'den gelen filtrelenmiş data ile güncelle
        console.log('Updating stock with filtered data:', data);
        updateAllCharts();
    }
});
</script>

<style scoped>
.stock-management {
    display: flex;
    flex-direction: column;
    gap: 25px;
}
</style>
