<template>
    <div class="production-analysis">
        <!-- Filtre Alanı -->
        <FilterSection
            :filters="productionFilters"
            :machines="machines"
            :workers="workers"
            @filter="handleFilter" />

        <!-- Üretim Verileri -->
        <div class="production-charts">
            <!-- Günlük Üretim -->
            <ChartCard
                title="📊 Günlük Üretim Miktarları"
                v-model:viewMode="productionViewMode.daily"
                :chartData="dailyProductionChart"
                :chartOptions="chartOptions"
                :tableData="dailyProductionData"
                :tableColumns="dailyProductionColumns"
                chartType="bar"
                sortField="production_date"
                :sortOrder="-1" />

            <!-- Makine Performansı -->
            <ChartCard
                title="⚙️ Makine Bazlı Üretim Performansı"
                v-model:viewMode="productionViewMode.machine"
                :chartData="machineProductionChart"
                :chartOptions="doughnutOptions"
                :tableData="machineProductionData"
                :tableColumns="machineProductionColumns"
                chartType="doughnut" />

            <!-- İşçi Performansı -->
            <ChartCard
                title="👥 İşçi Performans Sıralaması"
                v-model:viewMode="productionViewMode.worker"
                :chartData="workerPerformanceChart"
                :chartOptions="chartOptions"
                :tableData="workerPerformanceData"
                :tableColumns="workerPerformanceColumns"
                chartType="horizontalBar" />

            <!-- Ürün Dağılımı -->
            <ChartCard
                title="📦 Ürün Bazlı Üretim Dağılımı"
                v-model:viewMode="productionViewMode.product"
                :chartData="productDistributionChart"
                :chartOptions="doughnutOptions"
                :tableData="productDistributionData"
                :tableColumns="productDistributionColumns"
                chartType="pie" />
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
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
    machines: {
        type: Array,
        default: () => []
    },
    workers: {
        type: Array,
        default: () => []
    }
});

// View Mode States
const productionViewMode = ref({
    daily: 'chart',
    machine: 'chart',
    worker: 'chart',
    product: 'chart'
});

// Filters
const productionFilters = ref({
    dateRange: null,
    machine: null,
    worker: null
});

// Chart Data
const dailyProductionChart = ref();
const machineProductionChart = ref();
const workerPerformanceChart = ref();
const productDistributionChart = ref();

// Table Data
const dailyProductionData = ref([
    { production_date: '25.07.2025', total_quantity: 1100, active_machines: 8, active_workers: 12 },
    { production_date: '26.07.2025', total_quantity: 1250, active_machines: 9, active_workers: 14 },
    { production_date: '27.07.2025', total_quantity: 1180, active_machines: 7, active_workers: 11 },
    { production_date: '28.07.2025', total_quantity: 1350, active_machines: 10, active_workers: 15 },
    { production_date: '29.07.2025', total_quantity: 1247, active_machines: 8, active_workers: 12 }
]);

const machineProductionData = ref([
    { machine_name: 'Makine A', total_production: 4500, efficiency: 92, status: 'Aktif' },
    { machine_name: 'Makine B', total_production: 3800, efficiency: 87, status: 'Aktif' },
    { machine_name: 'Makine C', total_production: 2100, efficiency: 78, status: 'Bakım' },
    { machine_name: 'Makine D', total_production: 4200, efficiency: 95, status: 'Aktif' }
]);

const workerPerformanceData = ref([
    { worker_name: 'Ahmet Yılmaz', total_production: 1250, daily_average: 42, efficiency_score: 5, shift: 'Gündüz' },
    { worker_name: 'Mehmet Özkan', total_production: 1180, daily_average: 39, efficiency_score: 4, shift: 'Gece' },
    { worker_name: 'Ali Kaya', total_production: 1350, daily_average: 45, efficiency_score: 5, shift: 'Gündüz' },
    { worker_name: 'Fatma Demir', total_production: 1120, daily_average: 37, efficiency_score: 4, shift: 'Akşam' }
]);

const productDistributionData = ref([
    { product_name: 'Öz Ergül Plastik Büyük Boy', product_type: 'Ara kalite', total_produced: 2500, percentage: 35 },
    { product_name: 'M&R Orta Boy Kiloluk', product_type: '2.Kalite', total_produced: 1800, percentage: 25 },
    { product_name: 'Lüx Öz Ergül Küçük Boy', product_type: '1.Kalite', total_produced: 1200, percentage: 17 },
    { product_name: 'New Plast Büyük Boy', product_type: '3.Kalite', total_produced: 900, percentage: 13 },
    { product_name: 'Kelebek Orta Boy', product_type: 'Ara kalite', total_produced: 700, percentage: 10 }
]);

// Table Columns
const dailyProductionColumns = ref([
    { field: 'production_date', header: 'Tarih', sortable: true, type: 'date' },
    { field: 'total_quantity', header: 'Toplam Üretim', sortable: true, type: 'badge', severity: 'success' },
    { field: 'active_machines', header: 'Aktif Makine', sortable: true },
    { field: 'active_workers', header: 'Aktif İşçi', sortable: true }
]);

const machineProductionColumns = ref([
    { field: 'machine_name', header: 'Makine Adı', sortable: true },
    { field: 'total_production', header: 'Toplam Üretim', sortable: true, type: 'badge', severity: 'info' },
    { field: 'efficiency', header: 'Verimlilik %', sortable: true, type: 'progress' },
    {
        field: 'status',
        header: 'Durum',
        sortable: true,
        type: 'tag',
        severityMap: { 'Aktif': 'success', 'Bakım': 'danger', 'Pasif': 'secondary' }
    }
]);

const workerPerformanceColumns = ref([
    { field: 'worker_name', header: 'İşçi Adı', sortable: true },
    { field: 'total_production', header: 'Toplam Üretim', sortable: true, type: 'badge', severity: 'success' },
    { field: 'daily_average', header: 'Günlük Ortalama', sortable: true },
    { field: 'efficiency_score', header: 'Verimlilik Puanı', sortable: true, type: 'rating' },
    { field: 'shift', header: 'Vardiya', sortable: true }
]);

const productDistributionColumns = ref([
    { field: 'product_name', header: 'Ürün Adı', sortable: true },
    { field: 'product_type', header: 'Ürün Tipi', sortable: true, type: 'tag' },
    { field: 'total_produced', header: 'Toplam Üretilen', sortable: true, type: 'badge', severity: 'info' },
    { field: 'percentage', header: 'Yüzde %', sortable: true, type: 'progress' }
]);

// Chart Functions
const updateDailyProductionChart = () => {
    dailyProductionChart.value = {
        labels: ['25 Tem', '26 Tem', '27 Tem', '28 Tem', '29 Tem'],
        datasets: [{
            label: 'Günlük Üretim',
            data: [1100, 1250, 1180, 1350, 1247],
            backgroundColor: 'rgba(59, 130, 246, 0.8)',
            borderColor: '#3B82F6',
            borderWidth: 2,
            borderRadius: 6,
            borderSkipped: false
        }]
    };
};

const updateMachineProductionChart = () => {
    machineProductionChart.value = {
        labels: ['Makine A', 'Makine B', 'Makine C', 'Makine D'],
        datasets: [{
            data: [4500, 3800, 2100, 4200],
            backgroundColor: [
                'rgba(59, 130, 246, 0.8)',
                'rgba(16, 185, 129, 0.8)',
                'rgba(245, 158, 11, 0.8)',
                'rgba(139, 92, 246, 0.8)'
            ],
            borderColor: [
                '#3B82F6',
                '#10B981',
                '#F59E0B',
                '#8B5CF6'
            ],
            borderWidth: 2
        }]
    };
};

const updateWorkerPerformanceChart = () => {
    workerPerformanceChart.value = {
        labels: ['Ahmet Yılmaz', 'Mehmet Özkan', 'Ali Kaya', 'Fatma Demir'],
        datasets: [{
            label: 'Toplam Üretim',
            data: [1250, 1180, 1350, 1120],
            backgroundColor: 'rgba(16, 185, 129, 0.8)',
            borderColor: '#10B981',
            borderWidth: 2,
            borderRadius: 6,
            borderSkipped: false
        }]
    };
};

const updateProductDistributionChart = () => {
    productDistributionChart.value = {
        labels: ['Öz Ergül Büyük Boy', 'M&R Orta Boy', 'Lüx Öz Ergül Küçük', 'New Plast Büyük', 'Kelebek Orta'],
        datasets: [{
            data: [2500, 1800, 1200, 900, 700],
            backgroundColor: [
                'rgba(59, 130, 246, 0.8)',
                'rgba(16, 185, 129, 0.8)',
                'rgba(245, 158, 11, 0.8)',
                'rgba(139, 92, 246, 0.8)',
                'rgba(236, 72, 153, 0.8)'
            ],
            borderColor: [
                '#3B82F6',
                '#10B981',
                '#F59E0B',
                '#8B5CF6',
                '#EC4899'
            ],
            borderWidth: 2
        }]
    };
};

// Event Handlers
const handleFilter = (filters) => {
    console.log('Filtering production data:', filters);
    // API call with filters - parent component'ten gelecek
    emit('filter', filters);
};

// Initialize
onMounted(() => {
    updateAllCharts();
});

const updateAllCharts = () => {
    updateDailyProductionChart();
    updateMachineProductionChart();
    updateWorkerPerformanceChart();
    updateProductDistributionChart();
};

// Emits
const emit = defineEmits(['filter']);

// Expose functions
defineExpose({
    updateCharts: updateAllCharts
});
</script>

<style scoped>
.production-analysis {
    display: flex;
    flex-direction: column;
    gap: 25px;
}

.production-charts {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
    gap: 25px;
}

@media (max-width: 768px) {
    .production-charts {
        grid-template-columns: 1fr;
    }
}
</style>
