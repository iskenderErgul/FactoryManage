<template>
    <div class="production-analysis">
        <!-- Üretim Verileri - PrimeVue TabView -->
        <TabView v-model:activeIndex="activeTabIndex" class="production-tabs">
            <!-- Günlük Üretim Tab -->
            <TabPanel header="📊 Genel Üretim">
                <div class="tab-content-wrapper mt-3">
                    <!-- Periyot Filtreleme Butonları -->
                    <div class="period-filter-section mb-4">
                        <div class="period-buttons">
                            <Button
                                v-for="period in periodOptions"
                                :key="period.value"
                                :label="period.label"
                                :icon="period.icon"
                                :severity="selectedPeriod === period.value ? 'primary' : 'secondary'"
                                :outlined="selectedPeriod !== period.value"
                                @click="changePeriod(period.value)"
                                size="small"
                                class="period-btn" />
                        </div>

                        <!-- Ek Filtre Butonu -->
                        <div class="additional-filters">
                            <Button
                                label="Gelişmiş Filtre"
                                icon="pi pi-filter"
                                @click="showFilterDialog = true"
                                severity="info"
                                outlined
                                size="small" />

                            <Button
                                v-if="hasActiveFilters"
                                label="Filtreyi Temizle"
                                icon="pi pi-times"
                                @click="clearFilters"
                                severity="danger"
                                text
                                size="small" />
                        </div>
                    </div>

                    <!-- Loading -->
                    <div v-if="loading" class="loading-container">
                        <ProgressSpinner strokeWidth="3" />
                        <span>{{ selectedPeriod === 'daily' ? 'Günlük' : selectedPeriod === 'weekly' ? 'Haftalık' : selectedPeriod === 'monthly' ? 'Aylık' : 'Veriler' }} veriler yükleniyor...</span>
                    </div>

                    <!-- No Data State -->
                    <div v-else-if="!dailyProductionData || !dailyProductionData.length && !loading" class="no-data-container">
                        <i class="pi pi-chart-bar" style="font-size: 3rem; color: #64748b;"></i>
                        <h3>Henüz veri bulunmuyor</h3>
                        <p>Seçilen periyot için üretim verisi bulunamadı.</p>
                        <Button
                            label="Yeniden Yükle"
                            icon="pi pi-refresh"
                            @click="fetchDailyProduction(selectedPeriod)"
                            severity="secondary" />
                    </div>

                    <!-- Chart Card -->
                    <ChartCard
                        v-else
                        title="📊 Üretim Miktarları"
                        v-model:viewMode="productionViewMode.daily"
                        :chartData="dailyProductionChart"
                        :chartOptions="chartOptions"
                        :tableData="dailyProductionData"
                        :tableColumns="dailyProductionColumns"
                        chartType="bar"
                        sortField="production_date"
                        :sortOrder="sortOrderDesc">
                    </ChartCard>

                    <!-- Periyot Bilgisi -->
                    <div v-if="currentDateRange" class="date-range-info">
                        <small class="text-muted">
                            <i class="pi pi-calendar"></i>
                            {{ currentDateRange.start }} - {{ currentDateRange.end }}
                        </small>
                    </div>
                </div>
            </TabPanel>

            <!-- İşçi Üretimi Tab -->
            <TabPanel header="👷 İşçi Üretimi">
                <div class="tab-content-wrapper">
                    <!-- İşçi Seçimi -->
                    <div class="worker-selection-section mb-4">
                        <div class="worker-selector">
                            <label for="worker-select">İşçi Seçin:</label>
                            <div v-if="workersLoading" class="loading-indicator">
                                <ProgressSpinner strokeWidth="2" />
                                <span>İşçiler yükleniyor...</span>
                            </div>
                            <Dropdown
                                v-else
                                id="worker-select"
                                v-model="selectedWorkerId"
                                :options="workers || []"
                                optionLabel="name"
                                optionValue="id"
                                placeholder="İşçi seçin"
                                @change="onWorkerChange"
                                class="w-full"
                                filter
                                showClear
                                :disabled="!workers || workers.length === 0" />
                        </div>

                    <!-- Periyot Filtreleme -->
                        <div class="period-filter-section mb-4" v-if="selectedWorkerId">
                        <div class="period-buttons">
                            <Button
                                v-for="period in periodOptions"
                                :key="period.value"
                                :label="period.label"
                                :icon="period.icon"
                                    :severity="selectedWorkerDetailPeriod === period.value ? 'primary' : 'secondary'"
                                    :outlined="selectedWorkerDetailPeriod !== period.value"
                                    @click="changeWorkerDetailPeriod(period.value)"
                                size="small"
                                class="period-btn" />
                            </div>
                        </div>
                    </div>

                    <!-- İşçi Seçilmedi Durumu -->
                    <div v-if="!selectedWorkerId" class="no-worker-selected">
                        <i class="pi pi-users" style="font-size: 3rem; color: #64748b;"></i>
                        <h3>İşçi Seçin</h3>
                        <p>Detaylı üretim verilerini görmek için bir işçi seçin.</p>
                    </div>

                    <!-- Loading -->
                    <div v-else-if="workerDetailLoading" class="loading-container">
                        <ProgressSpinner strokeWidth="3" />
                        <span>İşçi detay verileri yükleniyor...</span>
                    </div>

                    <!-- No Data State -->
                    <div v-else-if="!workerDetailData || !workerDetailData.productTableData || !workerDetailData.productTableData.length" class="no-data-container">
                        <i class="pi pi-chart-bar" style="font-size: 3rem; color: #64748b;"></i>
                        <h3>Üretim verisi bulunmuyor</h3>
                        <p>Seçilen işçi için üretim verisi bulunamadı.</p>
                        <Button
                            label="Yeniden Yükle"
                            icon="pi pi-refresh"
                            @click="fetchWorkerDetailData(selectedWorkerId, selectedWorkerDetailPeriod)"
                            severity="secondary" />
                    </div>

                    <!-- İşçi Detay Verileri -->
                    <div v-else-if="workerDetailData" class="worker-detail-content">


                                                <!-- Ürün Bazlı Üretim -->
                        <div class="detail-section mb-4">
                            <ChartCard
                                title="Ürün Bazlı Üretim Dağılımı"
                                v-model:viewMode="workerDetailViewMode.product"
                                :chartData="workerDetailData.productChartData"
                                :chartOptions="chartOptions"
                                :tableData="workerDetailData.productTableData"
                                :tableColumns="workerDetailProductColumns"
                                chartType="bar"
                                sortField="total_produced"
                                :sortOrder="sortOrderDesc">
                            </ChartCard>
                        </div>

                        <!-- Günlük Üretim -->
                        <div class="detail-section">
                            <ChartCard
                                title="Günlük Üretim Miktarları"
                                v-model:viewMode="workerDetailViewMode.daily"
                                :chartData="workerDetailData.dailyChartData"
                                :chartOptions="chartOptions"
                                :tableData="workerDetailData.dailyTableData"
                                :tableColumns="workerDetailDailyColumns"
                                chartType="bar"
                                sortField="production_date"
                                :sortOrder="sortOrderDesc">
                            </ChartCard>
                        </div>
                    </div>
                </div>
            </TabPanel>
        </TabView>

        <!-- Gelişmiş Filtre Dialog -->
        <Dialog
            v-model:visible="showFilterDialog"
            modal
            header="Gelişmiş Filtreleme Seçenekleri"
            :style="{ width: '500px' }"
            :closable="true">

            <div class="filter-dialog-content">
                <div class="filter-item">
                    <label>Tarih Aralığı:</label>
                    <Calendar
                        v-model="filterData.dateRange"
                        selectionMode="range"
                        placeholder="Tarih aralığı seçin"
                        dateFormat="dd.mm.yy"
                        showButtonBar
                        class="w-full" />
                </div>

                <div class="filter-item">
                    <label>Makine:</label>
                    <Dropdown
                        v-model="filterData.machine"
                        :options="machines"
                        optionLabel="machine_name"
                        optionValue="id"
                        placeholder="Tüm Makineler"
                        showClear
                        filter
                        class="w-full" />
                </div>

                <div class="filter-item">
                    <label>İşçi:</label>
                    <Dropdown
                        v-model="filterData.worker"
                        :options="workers"
                        optionLabel="name"
                        optionValue="id"
                        placeholder="Tüm İşçiler"
                        showClear
                        filter
                        class="w-full" />
                </div>
            </div>

            <template #footer>
                <div class="dialog-footer">
                    <Button
                        label="Temizle"
                        icon="pi pi-times"
                        @click="clearFilterDialog"
                        severity="secondary"
                        text />
                    <Button
                        label="Filtrele"
                        icon="pi pi-check"
                        @click="applyFilterDialog"
                        severity="success"
                        :loading="filterLoading" />
                </div>
            </template>
        </Dialog>



        <!-- İşçi Detay Dialog -->
        <Dialog
            v-model:visible="showWorkerDetailDialog"
            modal
            :header="`${selectedWorkerName} - Detaylı Üretim Raporu`"
            :style="{ width: '90vw', maxWidth: '1200px' }"
            :closable="true">

            <div class="worker-detail-content">
                <!-- Periyot Seçimi -->
                <div class="period-filter-section mb-4">
                    <div class="period-buttons">
                        <Button
                            v-for="period in periodOptions"
                            :key="period.value"
                            :label="period.label"
                            :icon="period.icon"
                            :severity="selectedWorkerDetailPeriod === period.value ? 'primary' : 'secondary'"
                            :outlined="selectedWorkerDetailPeriod !== period.value"
                            @click="changeWorkerDetailPeriod(period.value)"
                            size="small"
                            class="period-btn" />
                    </div>
                </div>

                <!-- Loading -->
                <div v-if="workerDetailLoading" class="loading-container">
                    <ProgressSpinner strokeWidth="3" />
                    <span>İşçi detay verileri yükleniyor...</span>
                </div>

                <!-- Content -->
                <div v-else-if="workerDetailData" class="worker-detail-data">
                    <!-- Ürün Bazlı Üretim -->
                    <div class="detail-section mb-4">
                        <h4>📦 Ürün Bazlı Üretim</h4>
                        <ChartCard
                            title="Ürün Bazlı Üretim Dağılımı"
                            v-model:viewMode="workerDetailViewMode.product"
                            :chartData="workerDetailData.productChartData"
                            :chartOptions="chartOptions"
                            :tableData="workerDetailData.productTableData"
                            :tableColumns="workerDetailProductColumns"
                            chartType="bar"
                            sortField="total_produced"
                            :sortOrder="sortOrderDesc">
                        </ChartCard>
                    </div>

                    <!-- Günlük Üretim -->
                    <div class="detail-section">
                        <h4>📊 Günlük Üretim Trendi</h4>
                        <ChartCard
                            title="Günlük Üretim Miktarları"
                            v-model:viewMode="workerDetailViewMode.daily"
                            :chartData="workerDetailData.dailyChartData"
                            :chartOptions="chartOptions"
                            :tableData="workerDetailData.dailyTableData"
                            :tableColumns="workerDetailDailyColumns"
                            chartType="bar"
                            sortField="production_date"
                            :sortOrder="sortOrderDesc">
                        </ChartCard>
                    </div>
                </div>
            </div>

            <template #footer>
                <div class="dialog-footer">
                    <Button
                        label="Kapat"
                        icon="pi pi-times"
                        @click="showWorkerDetailDialog = false"
                        severity="secondary" />
                </div>
            </template>
        </Dialog>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useToast } from 'primevue/usetoast';
import axios from 'axios';
import ChartCard from './shared/ChartCard.vue';
import TabView from 'primevue/tabview';
import TabPanel from 'primevue/tabpanel';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import Calendar from 'primevue/calendar';
import Dropdown from 'primevue/dropdown';
import ProgressSpinner from 'primevue/progressspinner';

// Props
const props = defineProps({
    chartOptions: {
        type: Object,
        required: true
    },
    doughnutOptions: {
        type: Object,
        required: true
    }
});

// Composables
const toast = useToast();

// Tab State
const activeTabIndex = ref(0);

// Loading States
const loading = ref(false);
const productLoading = ref(false);
const filterLoading = ref(false);
const workerDetailLoading = ref(false);
const workersLoading = ref(false);

// Dialog State
const showFilterDialog = ref(false);
const showWorkerDetailDialog = ref(false);

// Period States
const selectedPeriod = ref('weekly');
const selectedProductPeriod = ref('monthly');
const selectedWorkerDetailPeriod = ref('monthly');

// Period Options
const periodOptions = ref([
    { label: 'Günlük', value: 'daily', icon: 'pi pi-calendar' },
    { label: 'Haftalık', value: 'weekly', icon: 'pi pi-calendar-plus' },
    { label: '2 Hafta', value: 'biweekly', icon: 'pi pi-calendar-times' },
    { label: '3 Hafta', value: 'triweekly', icon: 'pi pi-calendar' },
    { label: 'Aylık', value: 'monthly', icon: 'pi pi-calendar-minus' }
]);

// Filter Data
const filterData = ref({
    dateRange: null,
    machine: null,
    worker: null,
    product: null
});

// External Data
const machines = ref([]);
const workers = ref([]);
const products = ref([]);

// View Mode States
const productionViewMode = ref({
    daily: 'chart',
    product: 'chart'
});

const workerDetailViewMode = ref({
    product: 'chart',
    daily: 'chart'
});

// Chart Data
const dailyProductionChart = ref(null);
const productDistributionChart = ref(null);
const workerDetailData = ref(null);

// Worker Detail Data
const selectedWorkerName = ref('');
const selectedWorkerId = ref(null);

// Table Data
const dailyProductionData = ref([]);
const productDistributionData = ref([]);

// Date Range Info
const currentDateRange = ref(null);

// Table Columns
const dailyProductionColumns = ref([
    { field: 'production_date', header: 'Tarih', sortable: true, type: 'date' },
    { field: 'total_quantity', header: 'Toplam Üretim', sortable: true, type: 'badge', severity: 'success' },
    { field: 'active_machines', header: 'Aktif Makine', sortable: true },
    { field: 'active_workers', header: 'Aktif İşçi', sortable: true }
]);

const productDistributionColumns = ref([
    { field: 'product_name', header: 'Ürün Adı', sortable: true },
    { field: 'product_type', header: 'Ürün Tipi', sortable: true, type: 'tag' },
    { field: 'total_produced', header: 'Toplam Üretilen', sortable: true, type: 'badge', severity: 'info' },
    { field: 'percentage', header: 'Yüzde %', sortable: true, type: 'progress' }
]);



const workerDetailProductColumns = ref([
    { field: 'product_name', header: 'Ürün Adı', sortable: true },
    { field: 'total_produced', header: 'Toplam Üretilen', sortable: true, type: 'badge', severity: 'info' },
    { field: 'total_shifts', header: 'Toplam Vardiya', sortable: true },
    { field: 'avg_per_shift', header: 'Vardiya Ortalaması', sortable: true, type: 'badge', severity: 'success' }
]);

const workerDetailDailyColumns = ref([
    { field: 'production_date', header: 'Tarih', sortable: true, type: 'date' },
    { field: 'total_produced', header: 'Toplam Üretim', sortable: true, type: 'badge', severity: 'success' },
    { field: 'products_worked', header: 'Çalıştığı Ürün Sayısı', sortable: true },
    { field: 'machines_worked', header: 'Çalıştığı Makine Sayısı', sortable: true }
]);

// Computed
const hasActiveFilters = computed(() => {
    return filterData.value.dateRange || filterData.value.machine || filterData.value.worker;
});



const sortOrderDesc = computed(() => -1);

// API Functions
const fetchDailyProduction = async (period) => {
    try {
        loading.value = true;
        const response = await axios.get(`/api/dashboard/production/daily?period=${period}`);

        // Null check for response data
        if (response.data && response.data.chartData && response.data.tableData) {
            dailyProductionChart.value = response.data.chartData;
            dailyProductionData.value = response.data.tableData || [];
            currentDateRange.value = response.data.dateRange;
        } else {
            dailyProductionChart.value = {
                labels: [],
                datasets: [{
                    label: 'Günlük Üretim',
                    data: [],
                    backgroundColor: 'rgba(59, 130, 246, 0.8)',
                    borderColor: '#3B82F6',
                    borderWidth: 2,
                    borderRadius: 6,
                    borderSkipped: false
                }]
            };
            dailyProductionData.value = [];
            currentDateRange.value = null;
        }
    } catch (error) {
        console.error('Error fetching daily production:', error);
        dailyProductionChart.value = {
            labels: [],
            datasets: [{
                label: 'Günlük Üretim',
                data: [],
                backgroundColor: 'rgba(59, 130, 246, 0.8)',
                borderColor: '#3B82F6',
                borderWidth: 2,
                borderRadius: 6,
                borderSkipped: false
            }]
        };
        dailyProductionData.value = [];
        currentDateRange.value = null;
        toast.add({
            severity: 'error',
            summary: 'Hata',
            detail: 'Günlük üretim verileri yüklenirken hata oluştu',
            life: 3000
        });
    } finally {
        loading.value = false;
    }
};

const fetchProductDistribution = async (period) => {
    try {
        productLoading.value = true;
        const response = await axios.get(`/api/dashboard/production/products?period=${period}`);

        // Null check for response data
        if (response.data && response.data.chartData && response.data.tableData) {
            productDistributionChart.value = response.data.chartData;
            productDistributionData.value = response.data.tableData || [];
        } else {
            productDistributionChart.value = {
                labels: [],
                datasets: [{
                    data: [],
                    backgroundColor: [
                        'rgba(59, 130, 246, 0.8)',
                        'rgba(16, 185, 129, 0.8)',
                        'rgba(245, 158, 11, 0.8)',
                        'rgba(139, 92, 246, 0.8)',
                        'rgba(236, 72, 153, 0.8)',
                        'rgba(239, 68, 68, 0.8)',
                        'rgba(34, 197, 94, 0.8)',
                        'rgba(168, 85, 247, 0.8)',
                    ],
                    borderColor: [
                        '#3B82F6',
                        '#10B981',
                        '#F59E0B',
                        '#8B5CF6',
                        '#EC4899',
                        '#EF4444',
                        '#22C55E',
                        '#A855F7',
                    ],
                    borderWidth: 2
                }]
            };
            productDistributionData.value = [];
        }
    } catch (error) {
        console.error('Error fetching product distribution:', error);
        productDistributionChart.value = {
            labels: [],
            datasets: [{
                data: [],
                backgroundColor: [
                    'rgba(59, 130, 246, 0.8)',
                    'rgba(16, 185, 129, 0.8)',
                    'rgba(245, 158, 11, 0.8)',
                    'rgba(139, 92, 246, 0.8)',
                    'rgba(236, 72, 153, 0.8)',
                    'rgba(239, 68, 68, 0.8)',
                    'rgba(34, 197, 94, 0.8)',
                    'rgba(168, 85, 247, 0.8)',
                ],
                borderColor: [
                    '#3B82F6',
                    '#10B981',
                    '#F59E0B',
                    '#8B5CF6',
                    '#EC4899',
                    '#EF4444',
                    '#22C55E',
                    '#A855F7',
                ],
                borderWidth: 2
            }]
        };
        productDistributionData.value = [];
        toast.add({
            severity: 'error',
            summary: 'Hata',
            detail: 'Ürün dağılım verileri yüklenirken hata oluştu',
            life: 3000
        });
    } finally {
        productLoading.value = false;
    }
};



const fetchWorkerDetailData = async (workerId, period) => {
    try {
        workerDetailLoading.value = true;
        const response = await axios.get(`/api/dashboard/production/workers/detail?worker_id=${workerId}&period=${period}`);
        workerDetailData.value = response.data;
    } catch (error) {
        console.error('Error fetching worker detail data:', error);
        workerDetailData.value = null;
        toast.add({
            severity: 'error',
            summary: 'Hata',
            detail: 'İşçi detay verileri yüklenirken hata oluştu',
            life: 3000
        });
    } finally {
        workerDetailLoading.value = false;
    }
};

const openWorkerDetail = async (workerId, workerName) => {
    selectedWorkerId.value = workerId;
    selectedWorkerName.value = workerName;
    showWorkerDetailDialog.value = true;
    await fetchWorkerDetailData(workerId, selectedWorkerDetailPeriod.value);
};

const onWorkerChange = async () => {
    if (selectedWorkerId.value && workers.value && workers.value.length > 0) {
        // Seçilen işçinin adını bul
        const selectedWorker = workers.value.find(w => w.id === selectedWorkerId.value);
        selectedWorkerName.value = selectedWorker ? selectedWorker.name : 'Bilinmeyen İşçi';

        // İşçi detay verilerini yükle
        await fetchWorkerDetailData(selectedWorkerId.value, selectedWorkerDetailPeriod.value);
    } else {
        selectedWorkerName.value = '';
        workerDetailData.value = null;
    }
};

const fetchMachines = async () => {
    try {
        const response = await axios.get('/api/dashboard/machines');
        machines.value = response.data;
    } catch (error) {
        console.error('Error fetching machines:', error);
        machines.value = [];
    }
};

const fetchWorkers = async () => {
    try {
        workersLoading.value = true;
        const response = await axios.get('/api/dashboard/workers');
        workers.value = response.data;
    } catch (error) {
        console.error('Error fetching workers:', error);
        workers.value = [];
    } finally {
        workersLoading.value = false;
    }
};

const fetchProducts = async () => {
    try {
        const response = await axios.get('/api/dashboard/products');
        products.value = response.data;
    } catch (error) {
        console.error('Error fetching products:', error);
        products.value = [];
    }
};

const applyAdvancedFilter = async () => {
    try {
        filterLoading.value = true;

        const payload = {
            period: selectedPeriod.value
        };

        if (filterData.value.dateRange && filterData.value.dateRange.length === 2) {
            payload.start_date = filterData.value.dateRange[0];
            payload.end_date = filterData.value.dateRange[1];
        }

if (filterData.value.machine) {
    payload.machine_id = filterData.value.machine;
}

if (filterData.value.worker) {
    payload.user_id = filterData.value.worker;
}

        const response = await axios.post('/api/dashboard/production/filtered', payload);

        dailyProductionChart.value = response.data.chartData;
        dailyProductionData.value = response.data.tableData;

toast.add({
    severity: 'success',
    summary: 'Başarılı',
    detail: 'Filtreler uygulandı',
    life: 3000
});

showFilterDialog.value = false;

} catch (error) {
    console.error('Error applying filters:', error);
    toast.add({
        severity: 'error',
        summary: 'Hata',
        detail: 'Filtreler uygulanırken bir hata oluştu',
        life: 3000
    });
} finally {
    filterLoading.value = false;
}
};



// Event Handlers
const changePeriod = async (period) => {
    selectedPeriod.value = period;
    await fetchDailyProduction(period);
};

const changeProductPeriod = async (period) => {
    selectedProductPeriod.value = period;
    await fetchProductDistribution(period);
};



const changeWorkerDetailPeriod = async (period) => {
    selectedWorkerDetailPeriod.value = period;
    if (selectedWorkerId.value) {
        await fetchWorkerDetailData(selectedWorkerId.value, period);
    }
};

const applyFilterDialog = async () => {
    await applyAdvancedFilter();
};

const clearFilterDialog = () => {
    filterData.value = {
        dateRange: null,
        machine: null,
        worker: null,
        product: null
    };
};

const clearFilters = async () => {
    clearFilterDialog();
    await fetchDailyProduction(selectedPeriod.value);

    toast.add({
        severity: 'info',
        summary: 'Temizlendi',
        detail: 'Tüm filtreler kaldırıldı',
        life: 3000
    });
};

// Initialize
onMounted(async () => {
    try {
        // Paralel olarak tüm verileri yükle
        await Promise.allSettled([
            fetchDailyProduction(selectedPeriod.value),
            fetchProductDistribution(selectedProductPeriod.value),
            fetchMachines(),
            fetchWorkers(),
            fetchProducts()
        ]);

        toast.add({
            severity: 'success',
            summary: 'Yüklendi',
            detail: 'Veriler başarıyla yüklendi',
            life: 2000
        });

    } catch (error) {
        console.error('Error during initialization:', error);
        toast.add({
            severity: 'error',
            summary: 'Hata',
            detail: 'Sayfa yüklenirken bir hata oluştu',
            life: 5000
        });
    }
});

// Watch for tab changes to refresh data if needed
const refreshCurrentTab = async () => {
    if (activeTabIndex.value === 0) {
        // Günlük Üretim Tab
        await fetchDailyProduction(selectedPeriod.value);
    } else if (activeTabIndex.value === 1) {
        // Ürün Dağılımı Tab
        await fetchProductDistribution(selectedProductPeriod.value);
    }
};

// Emits
const emit = defineEmits(['filter', 'dataUpdate', 'tabChange']);

// Expose functions
defineExpose({
    refreshData: async () => {
        await Promise.allSettled([
            fetchDailyProduction(selectedPeriod.value),
            fetchProductDistribution(selectedProductPeriod.value)
        ]);
    },
    refreshCurrentTab,
    changePeriod,
    changeProductPeriod,
    clearFilters,
    // Data getters
    getCurrentData: () => ({
        dailyProduction: {
            chart: dailyProductionChart.value,
            table: dailyProductionData.value,
            period: selectedPeriod.value
        },
        productDistribution: {
            chart: productDistributionChart.value,
            table: productDistributionData.value,
            period: selectedProductPeriod.value
        },
        dateRange: currentDateRange.value
    })
});
</script>

<style scoped>
.production-analysis {
    display: flex;
    flex-direction: column;
    gap: 25px;
    width: 100%;
}

.production-tabs {
    margin-top: 20px;
    width: 100%;
}

.production-tabs .p-tabview-panels {
    padding: 0;
}

.tab-content-wrapper {
    width: 100%;
    max-width: 100%;
}

/* Periyot Filtreleme */
.period-filter-section {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 15px;
    padding: 20px;
    background: rgba(248, 250, 252, 0.05);
    border-radius: 12px;
    border: 1px solid rgba(226, 232, 240, 0.1);
}

.period-buttons {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.period-btn {
    min-width: 80px;
    transition: all 0.3s ease;
}

.additional-filters {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

/* Loading */
.loading-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 15px;
    padding: 40px;
    color: #64748b;
}

/* No Data State */
.no-data-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 15px;
    padding: 60px 40px;
    text-align: center;
    color: #64748b;
}

.no-data-container h3 {
    margin: 0;
    color: #475569;
}

.no-data-container p {
    margin: 0;
    color: #64748b;
}

/* Date Range Info */
.date-range-info {
    text-align: center;
    padding: 10px;
    margin-top: 15px;
    background: rgba(248, 250, 252, 0.03);
    border-radius: 8px;
}

.date-range-info .text-muted {
    color: #94a3b8;
}

/* Filter Dialog */
.filter-dialog-content {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.filter-item {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.filter-item label {
    font-weight: 600;
    color: #F1F5F9;
}

.dialog-footer {
    display: flex;
    justify-content: space-between;
    gap: 10px;
}

/* Tab genişlik ayarları */
:deep(.p-tabview) {
    width: 100% !important;
}

:deep(.p-tabview .p-tabview-nav) {
    display: flex !important;
    width: 100% !important;
    background-color: #f8f9fa !important;
    border-bottom: 1px solid #dee2e6 !important;
}

:deep(.p-tabview .p-tabview-nav li) {
    flex: 1 !important;
    min-width: 0 !important;
}

:deep(.p-tabview .p-tabview-nav li .p-tabview-nav-link) {
    width: 100% !important;
    text-align: center !important;
    padding: 16px 20px !important;
    font-size: 14px !important;
    font-weight: 600 !important;
    color: #6c757d !important;
    transition: all 0.3s ease !important;
    white-space: nowrap !important;
    overflow: hidden !important;
    text-overflow: ellipsis !important;
    background-color: transparent !important;
    border: none !important;
}

:deep(.p-tabview .p-tabview-nav li .p-tabview-nav-link:hover) {
    background-color: #e9ecef !important;
    color: #495057 !important;
}

:deep(.p-tabview .p-tabview-nav li.p-highlight .p-tabview-nav-link) {
    background-color: #007bff !important;
    color: white !important;
    border-color: #007bff !important;
}

/* Period Buttons Hover Effects */
.period-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.period-btn.p-button-primary {
    background: linear-gradient(135deg, #3B82F6, #1D4ED8);
    border: none;
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
}

/* Responsive Design */
@media (max-width: 768px) {
    :deep(.p-tabview .p-tabview-nav) {
        flex-direction: column !important;
    }

    :deep(.p-tabview .p-tabview-nav li .p-tabview-nav-link) {
        padding: 12px 15px !important;
        font-size: 13px !important;
    }

    .period-filter-section {
        flex-direction: column;
        align-items: stretch;
        gap: 15px;
    }

    .period-buttons {
        justify-content: center;
    }

    .additional-filters {
        width: 100%;
        justify-content: center;
    }

    .period-btn {
        flex: 1;
        min-width: 70px;
    }

    .worker-selection-section {
        flex-direction: column;
        gap: 15px;
    }
}

@media (max-width: 480px) {
    .period-buttons {
        flex-direction: column;
        width: 100%;
    }

    .period-btn {
        width: 100%;
    }

    .additional-filters {
        flex-direction: column;
    }
}

/* Worker Selection Styles */
.worker-selection-section {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 15px;
    padding: 20px;
    background: rgba(248, 250, 252, 0.05);
    border-radius: 12px;
    border: 1px solid rgba(226, 232, 240, 0.1);
}

.worker-selector {
    display: flex;
    flex-direction: column;
    gap: 8px;
    min-width: 250px;
}

.worker-selector label {
    font-weight: 600;
    color: #F1F5F9;
    margin-bottom: 5px;
}

.loading-indicator {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px;
    background: rgba(248, 250, 252, 0.05);
    border-radius: 8px;
    color: #64748b;
}

.no-worker-selected {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 15px;
    padding: 60px 40px;
    text-align: center;
    color: #64748b;
}

.no-worker-selected h3 {
    margin: 0;
    color: #475569;
}

.no-worker-selected p {
    margin: 0;
    color: #64748b;
}

.worker-info {
    background: rgba(59, 130, 246, 0.1);
    border: 1px solid rgba(59, 130, 246, 0.2);
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 20px;
}

.worker-info h3 {
    margin: 0 0 10px 0;
    color: #3B82F6;
    font-weight: 600;
}

.worker-info p {
    margin: 0;
    color: #64748b;
}

.detail-section {
    margin-bottom: 30px;
}

.detail-section h4 {
    margin: 0 0 15px 0;
    color: #F1F5F9;
    font-weight: 600;
    font-size: 1.1rem;
}
</style>
