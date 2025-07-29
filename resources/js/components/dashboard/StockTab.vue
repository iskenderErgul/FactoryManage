<template>
    <div class="stock-management">
        <!-- Stok Özeti -->
        <div class="stock-summary">
            <Card class="alert-card critical">
                <template #content>
                    <div class="alert-content">
                        <i class="pi pi-exclamation-triangle"></i>
                        <div>
                            <h4>Kritik Stok Uyarısı</h4>
                            <p>{{ stockData.criticalStock }} ürün kritik seviyede</p>
                        </div>
                    </div>
                </template>
            </Card>

            <Card class="alert-card warning">
                <template #content>
                    <div class="alert-content">
                        <i class="pi pi-info-circle"></i>
                        <div>
                            <h4>Düşük Stok</h4>
                            <p>{{ stockData.lowStock }} ürün yeniden sipariş seviyesinde</p>
                        </div>
                    </div>
                </template>
            </Card>
        </div>

        <!-- Stok Durumu -->
        <ChartCard
            title="📊 Mevcut Stok Durumu"
            v-model:viewMode="stockViewMode.current"
            :chartData="currentStockChart"
            :chartOptions="chartOptions"
            :tableData="currentStockData"
            :tableColumns="currentStockColumns"
            chartType="bar" />

        <!-- Stok Hareketleri -->
        <ChartCard
            title="📈 Stok Hareketleri (Son 30 Gün)"
            v-model:viewMode="stockViewMode.movements"
            :chartData="stockMovementsChart"
            :chartOptions="chartOptions"
            :tableData="stockMovementsData"
            :tableColumns="stockMovementsColumns"
            chartType="line"
            sortField="movement_date"
            :sortOrder="-1" />
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import Card from 'primevue/card';
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
    current: 'chart',
    movements: 'chart'
});

// Chart Data
const currentStockChart = ref();
const stockMovementsChart = ref();

// Table Data
const currentStockData = ref([
    { product_name: 'Öz Ergül Plastik Büyük Boy', product_type: 'Ara kalite', stock_quantity: 99999949, status: 'Normal', last_updated: '2025-07-29' },
    { product_name: 'M&R Orta Boy Kiloluk', product_type: '2.Kalite', stock_quantity: 99999999, status: 'Normal', last_updated: '2025-07-29' },
    { product_name: 'Lüx Öz Ergül Küçük Boy', product_type: '1.Kalite', stock_quantity: 8500, status: 'Düşük', last_updated: '2025-07-29' },
    { product_name: 'New Plast Büyük Boy', product_type: '3.Kalite', stock_quantity: 650, status: 'Kritik', last_updated: '2025-07-29' }
]);

const stockMovementsData = ref([
    { movement_date: '29.07.2025', product_name: 'Öz Ergül Plastik Büyük Boy', movement_type: 'çıkış', quantity: 500, related_process: 'Satış' },
    { movement_date: '29.07.2025', product_name: 'M&R Orta Boy', movement_type: 'giriş', quantity: 1000, related_process: 'Üretim' },
    { movement_date: '28.07.2025', product_name: 'Lüx Öz Ergül', movement_type: 'çıkış', quantity: 300, related_process: 'Satış' },
    { movement_date: '28.07.2025', product_name: 'Kelebek Orta Boy', movement_type: 'giriş', quantity: 800, related_process: 'Üretim' }
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

const stockMovementsColumns = ref([
    { field: 'movement_date', header: 'Tarih', sortable: true, type: 'date' },
    { field: 'product_name', header: 'Ürün', sortable: true },
    {
        field: 'movement_type',
        header: 'Hareket Tipi',
        sortable: true,
        type: 'tag',
        severityMap: { 'giriş': 'success', 'çıkış': 'info' }
    },
    {
        field: 'quantity',
        header: 'Miktar',
        sortable: true,
        type: 'badge',
        customSeverity: (value, row) => row.movement_type === 'giriş' ? 'success' : 'info'
    },
    { field: 'related_process', header: 'İlgili İşlem', sortable: true }
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

const updateStockMovementsChart = () => {
    stockMovementsChart.value = {
        labels: ['25 Tem', '26 Tem', '27 Tem', '28 Tem', '29 Tem'],
        datasets: [{
            label: 'Giriş',
            data: [800, 1200, 600, 1000, 900],
            borderColor: '#10B981',
            backgroundColor: 'rgba(16, 185, 129, 0.1)',
            tension: 0.4,
            borderWidth: 3,
            pointBackgroundColor: '#10B981',
            pointBorderColor: '#FFFFFF',
            pointBorderWidth: 2,
            pointRadius: 6
        }, {
            label: 'Çıkış',
            data: [500, 700, 300, 600, 400],
            borderColor: '#EF4444',
            backgroundColor: 'rgba(239, 68, 68, 0.1)',
            tension: 0.4,
            borderWidth: 3,
            pointBackgroundColor: '#EF4444',
            pointBorderColor: '#FFFFFF',
            pointBorderWidth: 2,
            pointRadius: 6
        }]
    };
};

// Initialize
onMounted(() => {
    updateAllCharts();
});

const updateAllCharts = () => {
    updateCurrentStockChart();
    updateStockMovementsChart();
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

.stock-summary {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
}

.alert-card.critical {
    border-left: 5px solid #EF4444;
    background: rgba(239, 68, 68, 0.1) !important;
}

.alert-card.warning {
    border-left: 5px solid #F59E0B;
    background: rgba(245, 158, 11, 0.1) !important;
}

.alert-content {
    display: flex;
    align-items: center;
    gap: 15px;
}

.alert-content i {
    font-size: 2rem;
    color: #EF4444;
}

.alert-card.warning .alert-content i {
    color: #F59E0B;
}

.alert-content h4 {
    margin: 0 0 5px 0;
    color: #F1F5F9 !important;
}

.alert-content p {
    margin: 0;
    color: #94A3B8 !important;
}

@media (max-width: 768px) {
    .stock-summary {
        grid-template-columns: 1fr;
    }
}
</style>
