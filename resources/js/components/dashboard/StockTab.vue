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
            :tableRows="50"
            chartType="bar" />
    </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
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

// Table Data - Artık props'tan gelen products kullanılacak
const currentStockData = ref([]);

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

// Stok durumu hesaplama fonksiyonu
const calculateStockStatus = (quantity) => {
    if (quantity < 1000) return 'Kritik';
    if (quantity < 10000) return 'Düşük';
    return 'Normal';
};

// Chart Functions
const updateCurrentStockChart = () => {
    if (!currentStockData.value.length) return;

    // Tüm ürünleri chart'ta göster
    const chartProducts = currentStockData.value;
    
    const labels = chartProducts.map(product => {
        // Ürün adını kısalt
        const name = product.product_name;
        if (name.includes('Öz Ergül')) {
            if (name.includes('Büyük')) return 'Öz Ergül Büyük';
            if (name.includes('Orta')) return 'Öz Ergül Orta';
            if (name.includes('Küçük')) return 'Öz Ergül Küçük';
            if (name.includes('Mini')) return 'Öz Ergül Mini';
        }
        if (name.includes('M&R')) {
            if (name.includes('Büyük')) return 'M&R Büyük';
            if (name.includes('Orta')) return 'M&R Orta';
            if (name.includes('Küçük')) return 'M&R Küçük';
            if (name.includes('Mini')) return 'M&R Mini';
        }
        if (name.includes('Lüx')) {
            if (name.includes('Büyük')) return 'Lüx Büyük';
            if (name.includes('Orta')) return 'Lüx Orta';
            if (name.includes('Küçük')) return 'Lüx Küçük';
            if (name.includes('Mini')) return 'Lüx Mini';
        }
        if (name.includes('New Plast')) {
            if (name.includes('Büyük')) return 'New Plast Büyük';
            if (name.includes('Orta')) return 'New Plast Orta';
            if (name.includes('Küçük')) return 'New Plast Küçük';
        }
        if (name.includes('Kelebek')) {
            if (name.includes('Büyük')) return 'Kelebek Büyük';
            if (name.includes('Orta')) return 'Kelebek Orta';
            if (name.includes('Küçük')) return 'Kelebek Küçük';
        }
        return name.substring(0, 15) + '...';
    });

    const data = chartProducts.map(product => product.stock_quantity);

    // Güzel renk paleti
    const backgroundColor = [
        'rgba(249, 115, 22, 0.4)',
        'rgba(6, 182, 212, 0.4)',
        'rgba(107, 114, 128, 0.4)',
        'rgba(139, 92, 246, 0.4)',
        'rgba(34, 197, 94, 0.4)',
        'rgba(251, 146, 60, 0.4)',
        'rgba(236, 72, 153, 0.4)',
        'rgba(16, 185, 129, 0.4)',
        'rgba(96, 165, 250, 0.4)',
        'rgba(52, 211, 153, 0.4)',
        'rgba(250, 204, 21, 0.4)',
        'rgba(192, 38, 211, 0.4)',
        'rgba(59, 130, 246, 0.4)',
        'rgba(239, 68, 68, 0.4)',
        'rgba(156, 163, 175, 0.4)',
        'rgba(217, 70, 239, 0.4)',
        'rgba(24, 78, 119, 0.4)',
        'rgba(245, 158, 11, 0.4)',
        'rgba(168, 85, 247, 0.4)',
        'rgba(51, 65, 85, 0.4)',
        'rgba(34, 197, 94, 0.4)',
        'rgba(251, 146, 60, 0.4)',
        'rgba(236, 72, 153, 0.4)',
        'rgba(16, 185, 129, 0.4)',
        'rgba(96, 165, 250, 0.4)',
        'rgba(52, 211, 153, 0.4)',
        'rgba(250, 204, 21, 0.4)',
        'rgba(192, 38, 211, 0.4)',
        'rgba(59, 130, 246, 0.4)',
        'rgba(239, 68, 68, 0.4)',
        'rgba(156, 163, 175, 0.4)',
        'rgba(217, 70, 239, 0.4)',
        'rgba(24, 78, 119, 0.4)',
        'rgba(245, 158, 11, 0.4)',
        'rgba(168, 85, 247, 0.4)',
        'rgba(51, 65, 85, 0.4)'
    ];

    const borderColor = [
        'rgb(249, 115, 22)',
        'rgb(6, 182, 212)',
        'rgb(107, 114, 128)',
        'rgb(139, 92, 246)',
        'rgb(34, 197, 94)',
        'rgb(251, 146, 60)',
        'rgb(236, 72, 153)',
        'rgb(16, 185, 129)',
        'rgb(96, 165, 250)',
        'rgb(52, 211, 153)',
        'rgb(250, 204, 21)',
        'rgb(192, 38, 211)',
        'rgb(59, 130, 246)',
        'rgb(239, 68, 68)',
        'rgb(156, 163, 175)',
        'rgb(217, 70, 239)',
        'rgb(24, 78, 119)',
        'rgb(245, 158, 11)',
        'rgb(168, 85, 247)',
        'rgb(51, 65, 85)',
        'rgb(34, 197, 94)',
        'rgb(251, 146, 60)',
        'rgb(236, 72, 153)',
        'rgb(16, 185, 129)',
        'rgb(96, 165, 250)',
        'rgb(52, 211, 153)',
        'rgb(250, 204, 21)',
        'rgb(192, 38, 211)',
        'rgb(59, 130, 246)',
        'rgb(239, 68, 68)',
        'rgb(156, 163, 175)',
        'rgb(217, 70, 239)',
        'rgb(24, 78, 119)',
        'rgb(245, 158, 11)',
        'rgb(168, 85, 247)',
        'rgb(51, 65, 85)'
    ];

    currentStockChart.value = {
        labels: labels,
        datasets: [{
            label: 'Stok Miktarı',
            data: data,
            backgroundColor: backgroundColor.slice(0, data.length),
            borderColor: borderColor.slice(0, data.length),
            borderWidth: 1,
            borderRadius: 6,
            borderSkipped: false
        }]
    };
};

// Props'tan gelen products'ı işle
const processProductsData = () => {
    if (!props.products || !props.products.length) {
        return;
    }

    // Sadece stokta olan ürünleri filtrele (stock_quantity > 0)
    const productsInStock = props.products.filter(product => 
        product.stock_quantity && product.stock_quantity > 0
    );

    currentStockData.value = productsInStock.map(product => ({
        product_name: product.product_name,
        product_type: product.product_type,
        stock_quantity: product.stock_quantity,
        status: calculateStockStatus(product.stock_quantity),
        last_updated: product.updated_at || new Date().toISOString().split('T')[0]
    }));

    updateCurrentStockChart();
};

// Props değiştiğinde data'yı güncelle
watch(() => props.products, processProductsData, { immediate: true });

// Initialize
onMounted(() => {
    // Eğer products verisi zaten yüklenmişse hemen işle
    if (props.products && props.products.length > 0) {
        processProductsData();
    }
    // Aksi takdirde watch zaten products değiştiğinde işleyecek
});

const updateAllCharts = () => {
    updateCurrentStockChart();
};

// Expose functions
defineExpose({
    updateCharts: updateAllCharts,
    updateWithFilteredData: (data) => {
        // API'den gelen filtrelenmiş data ile güncelle
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
