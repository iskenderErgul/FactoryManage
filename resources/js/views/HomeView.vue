<template>
    <div class="dashboard-container">
        <!-- Admin Dashboard -->
        <div v-if="isAdmin">
            <TabView>
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

        <!-- Worker View -->
        <div v-else-if="isWorker" class="worker-view">
            <div class="company-logo">
                <h1 class="company-name">Özergül Plastik</h1>
                <p class="company-subtitle">Hoş Geldiniz</p>
            </div>
            <div class="worker-shift-info">
                <template v-if="loadingShift">
                    <span>Vardiya bilgisi yükleniyor...</span>
                </template>
                <template v-else-if="currentShift">
                    <div class="shift-card">
                        <h3>Mevcut Vardiyanız</h3>
                        <p><b>{{ currentShift.name }}</b></p>
                        <p>{{ currentShift.start_time }} - {{ currentShift.end_time }}</p>
                    </div>
                </template>
                <template v-else>
                    <span>Bugün için atanmış vardiyanız yok.</span>
                </template>
            </div>
        </div>

        <!-- Loading or Unauthorized -->
        <div v-else class="loading-view">
            <div class="loading-spinner"></div>
            <p>Yükleniyor...</p>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, nextTick, computed } from "vue";
import { useStore } from "vuex";
import TabView from 'primevue/tabview';
import TabPanel from 'primevue/tabpanel';
import axios from "axios";

// Component imports
import OverviewTab from '@/components/dashboard/OverviewTab.vue';
import ProductionTab from '@/components/dashboard/ProductionTab.vue';
import SalesTab from '@/components/dashboard/SalesTab.vue';
import StockTab from '@/components/dashboard/StockTab.vue';

// Store
const store = useStore();

// Computed properties for user role checking
const isAdmin = computed(() => {
    return store.getters.user && store.getters.user.role === 'admin';
});

const isWorker = computed(() => {
    return store.getters.user && store.getters.user.role === 'worker';
});

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

const currentShift = ref(null);
const loadingShift = ref(false);

const fetchCurrentShift = async () => {
    loadingShift.value = true;
    try {
        const response = await axios.get('/api/current-shift');
        if (response.data && response.data.today_shifts && response.data.today_shifts.length > 0) {
            currentShift.value = response.data.today_shifts[0];
        } else {
            currentShift.value = null;
        }
    } catch (error) {
        currentShift.value = null;
    } finally {
        loadingShift.value = false;
    }
};

// Lifecycle
onMounted(async () => {
    await nextTick();

    try {
        // Only initialize dashboard for admin users
        if (isAdmin.value) {
            initializeChartOptions();
            await loadMasterData();
            updateAllTabs();
        }
        if (isWorker.value) {
            await fetchCurrentShift();
        }
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
    const textColorSecondary = '#FFFFFF'; // Açık renk, eksen metni için beyaz
    const surfaceBorder = 'rgba(203, 213, 225, 0.2)';

    chartOptions.value = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                labels: {
                    color: '#FFFFFF'
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

/* Worker View Styles */
.worker-view {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 60vh;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 15px;
    margin: 20px;
    box-shadow: 0 8px 32px rgba(0,0,0,0.3);
}

.company-logo {
    text-align: center;
    color: white;
    padding: 40px;
}

.company-name {
    font-size: 3.5rem;
    font-weight: 700;
    margin-bottom: 20px;
    text-shadow: 0 4px 8px rgba(0,0,0,0.3);
    letter-spacing: 2px;
}

.company-subtitle {
    font-size: 1.5rem;
    opacity: 0.9;
    font-weight: 300;
}

.worker-shift-info {
    margin-top: 30px;
    text-align: center;
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 250px;
}

.shift-card {
    background: rgba(30, 41, 59, 0.75);
    border: 2px solid #3B82F6;
    border-radius: 18px;
    padding: 32px 48px;
    display: flex;
    flex-direction: column;
    align-items: center;
    margin-top: 10px;
    color: #F1F5F9;
    box-shadow: 0 8px 32px rgba(59,130,246,0.18);
    font-size: 1.25rem;
    min-width: 320px;
}

.shift-card h3 {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 18px;
    color: #60A5FA;
    letter-spacing: 1px;
    text-shadow: 0 2px 8px rgba(59,130,246,0.18);
}

.shift-card b {
    font-size: 1.5rem;
    color: #FBBF24;
    margin-bottom: 10px;
    display: block;
}

.shift-card p {
    margin: 0;
    font-size: 1.15rem;
    font-weight: 500;
    letter-spacing: 0.5px;
}

/* Loading View Styles */
.loading-view {
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    min-height: 60vh;
    color: #94A3B8;
}

.loading-spinner {
    width: 50px;
    height: 50px;
    border: 4px solid rgba(59, 130, 246, 0.2);
    border-top: 4px solid #3B82F6;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin-bottom: 20px;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
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
    
    .company-name {
        font-size: 2.5rem;
    }
    
    .company-subtitle {
        font-size: 1.2rem;
    }
}
</style>
