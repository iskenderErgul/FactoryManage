<template>
    <div class="dashboard-container">
        <!-- Tab Panel -->
        <TabView>
            <!-- Genel Özet Tab -->
            <TabPanel header="📊 Genel Özet">
                <OverviewTab
                    ref="overviewTabRef"
                    :dashboardData="dashboardData"
                    :chartOptions="chartOptions"
                    class="mt-4"
                />
            </TabPanel>

            <!-- Üretim Analizi Tab -->
            <TabPanel header="🏭 Üretim Analizi">
                <ProductionTab
                    ref="productionTabRef"
                    :chartOptions="chartOptions"
                    :doughnutOptions="doughnutOptions"
                    :machines="machines"
                    :workers="workers"
                    @filter="handleProductionFilter" />
            </TabPanel>

            <!-- Stok Yönetimi Tab -->
            <TabPanel header="📦 Stok Yönetimi">
                <StockTab
                    ref="stockTabRef"
                    :chartOptions="chartOptions"
                    :stockData="stockData"
                    :products="products"
                    @filter="handleStockFilter" />
            </TabPanel>
        </TabView>
    </div>
</template>

<script setup>
import { ref, onMounted, nextTick } from "vue";
import TabView from 'primevue/tabview';
import TabPanel from 'primevue/tabpanel';
import axios from "axios";

// Component imports
import OverviewTab from '@/components/dashboard/OverviewTab.vue';
import ProductionTab from '@/components/dashboard/ProductionTab.vue';
import SalesTab from '@/components/dashboard/SalesTab.vue';
import StockTab from '@/components/dashboard/StockTab.vue';

// Component refs
const overviewTabRef = ref(null);
const productionTabRef = ref(null);
const salesTabRef = ref(null);
const stockTabRef = ref(null);

// Data References
const dashboardData = ref({
    dailyProduction: 1247,
    dailySales: 18450,
    activeWorkers: 12,
    activeMachines: 8,
    totalMachines: 10
});

const salesData = ref({
    monthlySales: 145000,
    activeCustomers: 45,
    totalOrders: 128
});

const stockData = ref({
    criticalStock: 3,
    lowStock: 7
});

// Master Data
const machines = ref([
    { id: 1, machine_name: 'Makine A' },
    { id: 2, machine_name: 'Makine B' },
    { id: 3, machine_name: 'Makine C' }
]);

const workers = ref([
    { id: 1, name: 'Ahmet Yılmaz' },
    { id: 2, name: 'Mehmet Özkan' },
    { id: 3, name: 'Ali Kaya' }
]);

const customers = ref([]);
const products = ref([]);

// Chart Options
const chartOptions = ref();
const doughnutOptions = ref();

// Lifecycle
onMounted(async () => {
    await nextTick();

    try {
        initializeChartOptions();
        await loadMasterData();
        updateAllTabs();
        console.log('Dashboard yüklendi');
    } catch (error) {
        console.error('Dashboard yüklenirken hata:', error);
    }
});

// API Functions
const loadMasterData = async () => {
    try {
        // Paralel olarak master dataları yükle
        const [productsRes, machinesRes, workersRes, customersRes] = await Promise.all([
            axios.get('/api/products').catch(() => ({ data: [] })),
            axios.get('/api/machines').catch(() => ({ data: machines.value })),
            axios.get('/api/users?role=worker').catch(() => ({ data: workers.value })),
            axios.get('/api/customers').catch(() => ({ data: [] }))
        ]);

        products.value = productsRes.data;
        machines.value = machinesRes.data.length > 0 ? machinesRes.data : machines.value;
        workers.value = workersRes.data.length > 0 ? workersRes.data : workers.value;
        customers.value = customersRes.data;

        console.log('Master data loaded');
    } catch (error) {
        console.warn('Master data loading error:', error);
    }
};

const loadDashboardData = async () => {
    try {
        // Dashboard KPI verilerini yükle
        const response = await axios.get('/api/dashboard/summary');
        dashboardData.value = { ...dashboardData.value, ...response.data };
    } catch (error) {
        console.warn('Dashboard data loading error:', error);
    }
};

// Chart Options Initialization
const initializeChartOptions = () => {
    const textColor = '#F1F5F9';
    const textColorSecondary = '#CBD5E1';
    const surfaceBorder = 'rgba(203, 213, 225, 0.2)';

    chartOptions.value = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                labels: {
                    color: textColor,
                    font: {
                        size: 13,
                        weight: '500'
                    }
                }
            },
            tooltip: {
                backgroundColor: 'rgba(15, 23, 42, 0.95)',
                titleColor: '#F1F5F9',
                bodyColor: '#CBD5E1',
                borderColor: 'rgba(59, 130, 246, 0.3)',
                borderWidth: 1,
                cornerRadius: 8
            }
        },
        scales: {
            x: {
                ticks: {
                    color: textColorSecondary,
                    font: {
                        size: 12,
                        weight: '500'
                    }
                },
                grid: {
                    color: surfaceBorder,
                    drawBorder: false,
                    lineWidth: 1
                },
                border: {
                    color: surfaceBorder
                }
            },
            y: {
                beginAtZero: true,
                ticks: {
                    color: textColorSecondary,
                    font: {
                        size: 12,
                        weight: '500'
                    }
                },
                grid: {
                    color: surfaceBorder,
                    drawBorder: false,
                    lineWidth: 1
                },
                border: {
                    color: surfaceBorder
                }
            }
        },
        interaction: {
            intersect: false,
            mode: 'index'
        }
    };

    doughnutOptions.value = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                labels: {
                    color: textColor,
                    font: {
                        size: 13,
                        weight: '500'
                    },
                    padding: 20,
                    usePointStyle: true,
                    pointStyle: 'circle'
                },
                position: 'bottom'
            },
            tooltip: {
                backgroundColor: 'rgba(15, 23, 42, 0.95)',
                titleColor: '#F1F5F9',
                bodyColor: '#CBD5E1',
                borderColor: 'rgba(59, 130, 246, 0.3)',
                borderWidth: 1,
                cornerRadius: 8
            }
        },
        cutout: '50%'
    };
};

// Event Handlers
const handleProductionFilter = async (filters) => {
    try {
        console.log('Production filter:', filters);
        const response = await axios.get('/api/productions/filtered', { params: filters });
        // Update production data in ProductionTab
        if (productionTabRef.value) {
            productionTabRef.value.updateWithFilteredData(response.data);
        }
    } catch (error) {
        console.error('Production filter error:', error);
    }
};

const handleSalesFilter = async (filters) => {
    try {
        console.log('Sales filter:', filters);
        const response = await axios.get('/api/sales/filtered', { params: filters });
        // Update sales data in SalesTab
        if (salesTabRef.value) {
            salesTabRef.value.updateWithFilteredData(response.data);
        }
    } catch (error) {
        console.error('Sales filter error:', error);
    }
};

const handleStockFilter = async (filters) => {
    try {
        console.log('Stock filter:', filters);
        const response = await axios.get('/api/stock/filtered', { params: filters });
        // Update stock data in StockTab
        if (stockTabRef.value) {
            stockTabRef.value.updateWithFilteredData(response.data);
        }
    } catch (error) {
        console.error('Stock filter error:', error);
    }
};

// Update all tabs
const updateAllTabs = () => {
    if (overviewTabRef.value) overviewTabRef.value.updateCharts();
    if (productionTabRef.value) productionTabRef.value.updateCharts();
    if (salesTabRef.value) salesTabRef.value.updateCharts();
    if (stockTabRef.value) stockTabRef.value.updateCharts();
};

// Utility Functions
const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('tr-TR');
};
</script>

<style scoped>
.dashboard-container {
    width: 100%;
    background: transparent;
}

.dashboard-header {
    text-align: center;
    margin-bottom: 30px;
    padding: 30px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 15px;
    box-shadow: 0 8px 32px rgba(0,0,0,0.3);
}

.dashboard-title {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 10px;
    text-shadow: 0 2px 4px rgba(0,0,0,0.3);
}

.dashboard-subtitle {
    font-size: 1.1rem;
    opacity: 0.9;
}

/* PrimeVue Override - Koyu tema için */
:deep(.p-tabview .p-tabview-nav) {
    background: rgba(30, 41, 59, 0.95) !important;
    border-bottom: 1px solid rgba(71, 85, 105, 0.3) !important;
    display: flex !important;
    width: 100% !important;
}

:deep(.p-tabview .p-tabview-nav li) {
    flex: 1 !important;
    min-width: 0 !important;
}

:deep(.p-tabview .p-tabview-nav li .p-tabview-nav-link) {
    color: #94A3B8 !important;
    background: transparent !important;
    border: none !important;
    width: 100% !important;
    text-align: center !important;
    padding: 16px 20px !important;
    font-size: 14px !important;
    font-weight: 600 !important;
    white-space: nowrap !important;
    overflow: hidden !important;
    text-overflow: ellipsis !important;
}

:deep(.p-tabview .p-tabview-nav li.p-highlight .p-tabview-nav-link) {
    color: #F1F5F9 !important;
    background: rgba(59, 130, 246, 0.2) !important;
    border-bottom: 2px solid #3B82F6 !important;
}

:deep(.p-tabview .p-tabview-panels) {
    background: transparent !important;
    border: none !important;
    padding : 0 !important;
}

@media (max-width: 768px) {
    .dashboard-title {
        font-size: 2rem;
    }
}
</style>
