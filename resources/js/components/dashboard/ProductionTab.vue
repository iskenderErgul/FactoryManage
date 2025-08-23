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



            <!-- İşçi Genel Üretim Tab (YENİ) -->
            <TabPanel header="📋 İşçi Genel Üretim">
                <div class="tab-content-wrapper">
                    <!-- Periyot Filtreleme -->
                    <div class="period-filter-section mb-4 mt-4" >
                        <div class="period-buttons">
                            <Button
                                v-for="period in periodOptions"
                                :key="period.value"
                                :label="period.label"
                                :icon="period.icon"
                                :severity="selectedWorkerMatrixPeriod === period.value ? 'primary' : 'secondary'"
                                :outlined="selectedWorkerMatrixPeriod !== period.value"
                                @click="changeWorkerMatrixPeriod(period.value)"
                                size="small"
                                class="period-btn" />
                        </div>
                    </div>

                    <!-- Loading -->
                    <div v-if="workerMatrixLoading" class="loading-container">
                        <ProgressSpinner strokeWidth="3" />
                        <span>İşçi üretim matrisi yükleniyor...</span>
                    </div>

                    <!-- No Data State -->
                    <div v-else-if="!workerMatrixData || !workerMatrixData.dates || !workerMatrixData.dates.length" class="no-data-container">
                        <i class="pi pi-table" style="font-size: 3rem; color: #64748b;"></i>
                        <h3>Üretim verisi bulunmuyor</h3>
                        <p>Seçilen periyot için işçi üretim verisi bulunamadı.</p>
                        <Button
                            label="Yeniden Yükle"
                            icon="pi pi-refresh"
                            @click="fetchWorkerMatrix(selectedWorkerMatrixPeriod)"
                            severity="secondary" />
                    </div>

                    <!-- Worker Matrix Table -->
                    <div v-else class="worker-matrix-container">
                        <div class="matrix-header mb-4">
                            <h3>İşçi Üretim Tablosu</h3>
                            <small class="text-muted">
                                <i class="pi pi-calendar"></i>
                                {{ workerMatrixData.dateRange?.start }} - {{ workerMatrixData.dateRange?.end }}
                            </small>
                        </div>

                        <!-- Responsive Table Wrapper -->
                        <div class="table-responsive">
                            <table class="worker-matrix-table">
                                <thead>
                                <tr>
                                    <th class="worker-name-col">İşçi Adı</th>
                                    <th
                                        v-for="date in workerMatrixData.dates"
                                        :key="date.formatted"
                                        class="date-col"
                                        :title="date.full">
                                        {{ formatDateForDisplay(date.formatted) }}
                                    </th>
                                    <th class="total-col">Toplam</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr
                                    v-for="worker in workerMatrixData.workers"
                                    :key="worker.id"
                                    class="worker-row">
                                    <td class="worker-name">
                                        <div class="worker-info">
                                            <i class="pi pi-user"></i>
                                            <span>{{ worker.name }}</span>
                                        </div>
                                    </td>
                                    <td
                                        v-for="date in workerMatrixData.dates"
                                        :key="`${worker.id}-${date.formatted}`"
                                        class="production-cell"
                                        :class="getProductionCellClass(worker.productions[date.formatted])"
                                        @mouseenter="showProductTooltip($event, worker, date.formatted)"
                                        @mouseleave="hideProductTooltip">
                                            <span v-if="worker.productions[date.formatted]">
                                                {{ worker.productions[date.formatted] }}
                                            </span>
                                        <span v-else class="no-production">-</span>
                                    </td>
                                    <td class="total-cell">
                                        <span class="total-badge">{{ worker.total }}</span>
                                    </td>
                                </tr>
                                </tbody>
                                <tfoot>
                                <tr class="totals-row">
                                    <td class="total-label">Günlük Toplam</td>
                                    <td
                                        v-for="date in workerMatrixData.dates"
                                        :key="`total-${date.formatted}`"
                                        class="daily-total">
                                        {{ workerMatrixData.dailyTotals[date.formatted] || 0 }}
                                    </td>
                                    <td class="grand-total">
                                        {{ workerMatrixData.grandTotal }}
                                    </td>
                                </tr>
                                </tfoot>
                            </table>
                        </div>

                        <!-- Summary Stats -->
                        <div class="matrix-summary mt-4">
                            <div class="summary-cards">
                                <div class="summary-card">
                                    <div class="summary-icon">
                                        <i class="pi pi-users"></i>
                                    </div>
                                    <div class="summary-content">
                                        <div class="summary-label">Toplam İşçi</div>
                                        <div class="summary-value">{{ workerMatrixData.workers?.length || 0 }}</div>
                                    </div>
                                </div>
                                <div class="summary-card">
                                    <div class="summary-icon">
                                        <i class="pi pi-calendar"></i>
                                    </div>
                                    <div class="summary-content">
                                        <div class="summary-label">Çalışılan Gün</div>
                                        <div class="summary-value">{{ workerMatrixData.dates?.length-1 || 0 }}</div>
                                    </div>
                                </div>
                                <div class="summary-card">
                                    <div class="summary-icon">
                                        <i class="pi pi-chart-bar"></i>
                                    </div>
                                    <div class="summary-content">
                                        <div class="summary-label">Toplam Üretim( KOLİ )</div>
                                        <div class="summary-value">{{ workerMatrixData.grandTotal || 0 }}</div>
                                    </div>
                                </div>
                                <div class="summary-card">
                                    <div class="summary-icon">
                                        <i class="pi pi-chart-line"></i>
                                    </div>
                                    <div class="summary-content">
                                        <div class="summary-label">Günlük Ortalama ( KOLİ )</div>
                                        <div class="summary-value">{{ Math.round((workerMatrixData.grandTotal || 0) / (workerMatrixData.dates?.length || 1)) }}</div>
                                    </div>
                                </div>
                            </div>
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

        <!-- Product Tooltip -->
        <div
            v-if="productTooltip.visible"
            class="product-tooltip"
            :style="{
                left: productTooltip.x + 'px',
                top: productTooltip.y + 'px'
            }">
            <div class="tooltip-header">
                <strong>{{ productTooltip.workerName }}</strong>
                <span class="tooltip-date">{{ productTooltip.date }}</span>
            </div>
            <div class="tooltip-content">
                <div v-for="product in productTooltip.products" :key="product.product_name" class="product-item">
                    <span class="product-name">{{ product.product_name }}</span>
                    <span class="product-quantity">{{ product.quantity }}</span>
                </div>
            </div>
        </div>
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
const workerMatrixLoading = ref(false); // YENİ

// Dialog State
const showFilterDialog = ref(false);
const showWorkerDetailDialog = ref(false);

// Period States
const selectedPeriod = ref('weekly');
const selectedProductPeriod = ref('monthly');
const selectedWorkerDetailPeriod = ref('monthly');
const selectedWorkerMatrixPeriod = ref('weekly'); // YENİ

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
const workerMatrixData = ref(null); // YENİ

// Worker Detail Data
const selectedWorkerName = ref('');
const selectedWorkerId = ref(null);

// Table Data
const dailyProductionData = ref([]);
const productDistributionData = ref([]);

// Date Range Info
const currentDateRange = ref(null);

// Tooltip için state
const productTooltip = ref({
    visible: false,
    x: 0,
    y: 0,
    products: [],
    workerName: '',
    date: ''
});

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

// YENİ - Production cell class helper
const getProductionCellClass = (value) => {
    if (!value || value === 0) return 'no-production';
    if (value >= 100) return 'high-production';
    if (value >= 50) return 'medium-production';
    return 'low-production';
};

// Tooltip fonksiyonları
const showProductTooltip = (event, worker, date) => {
    if (!worker.productDetails || !worker.productDetails[date] || worker.productDetails[date].length === 0) {
        return;
    }

    const rect = event.target.getBoundingClientRect();
    productTooltip.value = {
        visible: true,
        x: rect.left + rect.width / 2,
        y: rect.top - 10,
        products: worker.productDetails[date],
        workerName: worker.name,
        date: formatDateForDisplay(date)
    };
};

const hideProductTooltip = () => {
    productTooltip.value.visible = false;
};

// Tarih formatı için yardımcı fonksiyon
const formatDateForDisplay = (dateString) => {
    if (!dateString) return '';

    try {
        const date = new Date(dateString);
        return date.toLocaleDateString('tr-TR', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric'
        });
    } catch (error) {
        // Eğer tarih parse edilemezse, orijinal string'i döndür
        return dateString;
    }
};

// API Functions

// Mevcut API fonksiyonları... (dailyProduction, productDistribution, vb.)
const fetchDailyProduction = async (period) => {
    try {
        loading.value = true;
        const response = await axios.get(`/api/dashboard/production/daily?period=${period}`);

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

// YENİ - Worker Matrix API fonksiyonu
const fetchWorkerMatrix = async (period) => {
    try {
        workerMatrixLoading.value = true;
        const response = await axios.get(`/api/dashboard/production/workers/matrix?period=${period}`);
        workerMatrixData.value = response.data;
    } catch (error) {
        console.error('Error fetching worker matrix:', error);
        workerMatrixData.value = null;
        toast.add({
            severity: 'error',
            summary: 'Hata',
            detail: 'İşçi üretim matrisi yüklenirken hata oluştu',
            life: 3000
        });
    } finally {
        workerMatrixLoading.value = false;
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

const changeWorkerDetailPeriod = async (period) => {
    selectedWorkerDetailPeriod.value = period;
    if (selectedWorkerId.value) {
        await fetchWorkerDetailData(selectedWorkerId.value, period);
    }
};

// YENİ - Worker Matrix period change
const changeWorkerMatrixPeriod = async (period) => {
    selectedWorkerMatrixPeriod.value = period;
    await fetchWorkerMatrix(period);
};

const onWorkerChange = async () => {
    if (selectedWorkerId.value && workers.value && workers.value.length > 0) {
        const selectedWorker = workers.value.find(w => w.id === selectedWorkerId.value);
        selectedWorkerName.value = selectedWorker ? selectedWorker.name : 'Bilinmeyen İşçi';
        await fetchWorkerDetailData(selectedWorkerId.value, selectedWorkerDetailPeriod.value);
    } else {
        selectedWorkerName.value = '';
        workerDetailData.value = null;
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
        await Promise.allSettled([
            fetchDailyProduction(selectedPeriod.value),
            fetchWorkerMatrix(selectedWorkerMatrixPeriod.value), // YENİ
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
        await fetchDailyProduction(selectedPeriod.value);
    } else if (activeTabIndex.value === 1) {
        // İşçi Üretimi Tab - no auto refresh
    } else if (activeTabIndex.value === 2) {
        // YENİ - İşçi Genel Üretim Tab
        await fetchWorkerMatrix(selectedWorkerMatrixPeriod.value);
    }
};

// Emits
const emit = defineEmits(['filter', 'dataUpdate', 'tabChange']);

// Expose functions
defineExpose({
    refreshData: async () => {
        await Promise.allSettled([
            fetchDailyProduction(selectedPeriod.value),
            fetchWorkerMatrix(selectedWorkerMatrixPeriod.value) // YENİ
        ]);
    },
    refreshCurrentTab,
    changePeriod,
    changeWorkerMatrixPeriod, // YENİ
    clearFilters,
    getCurrentData: () => ({
        dailyProduction: {
            chart: dailyProductionChart.value,
            table: dailyProductionData.value,
            period: selectedPeriod.value
        },
        workerMatrix: { // YENİ
            data: workerMatrixData.value,
            period: selectedWorkerMatrixPeriod.value
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

.worker-detail-content {
    display: flex;
    flex-direction: column;
    gap: 20px;
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

/* YENİ - Worker Matrix Styles */
.worker-matrix-container {
    background: rgba(248, 250, 252, 0.02);
    border-radius: 12px;
    padding: 20px;
    border: 1px solid rgba(226, 232, 240, 0.1);
}

.matrix-header {
    text-align: center;
    margin-bottom: 20px;
}

.matrix-header h3 {
    margin: 0 0 5px 0;
    color: #F1F5F9;
    font-weight: 600;
}

.matrix-header .text-muted {
    color: #94a3b8;
}

.table-responsive {
    overflow-x: auto;
    margin-bottom: 20px;
    border-radius: 8px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.worker-matrix-table {
    width: 100%;
    border-collapse: collapse;
    background: rgba(255, 255, 255, 0.02);
    font-size: 13px;
}

.worker-matrix-table th,
.worker-matrix-table td {
    padding: 12px 8px;
    text-align: center;
    border: 1px solid rgba(226, 232, 240, 0.2);
    position: relative;
}

.worker-matrix-table thead th {
    background: rgba(59, 130, 246, 0.1);
    color: #3B82F6;
    font-weight: 600;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    position: sticky;
    top: 0;
    z-index: 10;
}

.worker-name-col {
    min-width: 150px;
    text-align: left !important;
    background: rgba(59, 130, 246, 0.15) !important;
}

.date-col {
    min-width: 80px;
    writing-mode: horizontal-tb;
}

.total-col {
    min-width: 80px;
    background: rgba(16, 185, 129, 0.1) !important;
    color: #10B981 !important;
}

.worker-row:nth-child(even) {
    background: rgba(248, 250, 252, 0.02);
}

.worker-row:hover {
    background: rgba(59, 130, 246, 0.05);
}

.worker-name {
    text-align: left !important;
    font-weight: 500;
    background: rgba(248, 250, 252, 0.05);
}

.worker-info {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #F1F5F9;
}

.worker-info i {
    color: #3B82F6;
    font-size: 14px;
}

.production-cell {
    font-weight: 600;
    transition: all 0.2s ease;
}

.production-cell:hover {
    background: rgba(59, 130, 246, 0.1);
    transform: scale(1.05);
}

.production-cell.high-production {
    background: rgba(16, 185, 129, 0.2);
    color: #10B981;
}

.production-cell.medium-production {
    background: rgba(245, 158, 11, 0.2);
    color: #F59E0B;
}

.production-cell.low-production {
    background: rgba(99, 102, 241, 0.2);
    color: #6366F1;
}

.production-cell.no-production {
    background: rgba(107, 114, 128, 0.1);
    color: #6B7280;
}

.no-production {
    font-style: italic;
    opacity: 0.6;
}

.total-cell {
    font-weight: 700;
    background: rgba(16, 185, 129, 0.1);
}

.total-badge {
    background: #10B981;
    color: white;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 600;
}

.totals-row {
    background: rgba(16, 185, 129, 0.1);
    font-weight: 600;
    border-top: 2px solid #10B981;
}

.total-label {
    text-align: left !important;
    color: #10B981;
    font-weight: 700;
}

.daily-total {
    color: #10B981;
    font-weight: 600;
}

.grand-total {
    background: #10B981;
    color: white;
    font-weight: 700;
    font-size: 14px;
}

.matrix-summary {
    margin-top: 20px;
}

.summary-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
}

.summary-card {
    background: rgba(248, 250, 252, 0.05);
    border: 1px solid rgba(226, 232, 240, 0.1);
    border-radius: 8px;
    padding: 16px;
    display: flex;
    align-items: center;
    gap: 12px;
    transition: all 0.3s ease;
}

.summary-card:hover {
    background: rgba(248, 250, 252, 0.08);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.summary-icon {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(59, 130, 246, 0.1);
    color: #3B82F6;
    font-size: 18px;
}

.summary-content {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.summary-label {
    font-size: 12px;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.summary-value {
    font-size: 20px;
    font-weight: 700;
    color: #F1F5F9;
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

    /* Worker Matrix Responsive */
    .worker-matrix-table {
        font-size: 11px;
    }

    .worker-matrix-table th,
    .worker-matrix-table td {
        padding: 8px 6px;
    }

    .worker-name-col {
        min-width: 120px;
    }

    .date-col {
        min-width: 60px;
    }

    .summary-cards {
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 10px;
    }

    .summary-card {
        padding: 12px;
    }

    .summary-icon {
        width: 35px;
        height: 35px;
        font-size: 16px;
    }

    .summary-value {
        font-size: 18px;
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

    .summary-cards {
        grid-template-columns: 1fr;
    }

    .worker-matrix-table {
        font-size: 10px;
    }

    .date-col {
        min-width: 50px;
    }

    .product-tooltip {
        min-width: 150px;
        max-width: 250px;
        padding: 8px;
    }

    .tooltip-header strong {
        font-size: 12px;
    }

    .product-name,
    .product-quantity {
        font-size: 11px;
    }
}

/* Product Tooltip Styles */
.product-tooltip {
    position: fixed;
    background: rgba(15, 23, 42, 0.95);
    border: 1px solid rgba(59, 130, 246, 0.3);
    border-radius: 8px;
    padding: 12px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
    z-index: 9999;
    transform: translateX(-50%) translateY(-100%);
    min-width: 200px;
    max-width: 300px;
    backdrop-filter: blur(8px);
}

.tooltip-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
    padding-bottom: 8px;
    border-bottom: 1px solid rgba(59, 130, 246, 0.2);
}

.tooltip-header strong {
    color: #F1F5F9;
    font-size: 14px;
    font-weight: 600;
}

.tooltip-date {
    color: #94A3B8;
    font-size: 12px;
}

.tooltip-content {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.product-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 4px 0;
    border-bottom: 1px solid rgba(71, 85, 105, 0.2);
}

.product-item:last-child {
    border-bottom: none;
}

.product-name {
    color: #CBD5E1;
    font-size: 13px;
    flex: 1;
    margin-right: 8px;
    word-break: break-word;
}

.product-quantity {
    color: #3B82F6;
    font-weight: 600;
    font-size: 13px;
    background: rgba(59, 130, 246, 0.1);
    padding: 2px 6px;
    border-radius: 4px;
    min-width: 40px;
    text-align: center;
}
</style>
