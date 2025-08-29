<template>
    <div class="costs-analysis">
        <!-- Periyot Seçimi -->
        <div class="period-selector mb-4">
            <h3 class="mb-3">📊 Dönemsel Maliyet Analizi</h3>
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
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="loading-container">
            <ProgressSpinner />
            <p>Maliyet verileri yükleniyor...</p>
        </div>

        <!-- Maliyet Verileri -->
        <div v-else class="costs-content">
            <!-- Özet Kartları -->
            <div class="summary-cards mb-4">
                <div class="summary-card">
                    <div class="card-header">
                        <i class="pi pi-calculator"></i>
                        <span>Toplam Maliyet</span>
                    </div>
                    <div class="card-value">₺{{ formatCurrency(grandTotal) }}</div>
                    <div class="card-period">{{ currentDateRange }}</div>
                </div>

                <div v-for="source in sourceTotals" :key="source.source" class="summary-card">
                    <div class="card-header">
                        <i :class="getSourceIcon(source.source)"></i>
                        <span>{{ source.source }}</span>
                    </div>
                    <div class="card-value">₺{{ formatCurrency(source.total) }}</div>
                    <div class="card-percentage">
                        %{{ calculatePercentage(source.total, grandTotal) }}
                    </div>
                </div>
            </div>

            <!-- Detaylı Maliyet Tablosu -->
            <div class="costs-table-section">
                <Card>
                    <template #title>
                        <div class="table-header">
                            <h4>📋 Detaylı Maliyet Kalemleri</h4>
                            <Button 
                                icon="pi pi-download" 
                                label="Excel'e Aktar" 
                                severity="success" 
                                size="small"
                                @click="exportToExcel" />
                        </div>
                    </template>
                    <template #content>
                        <DataTable 
                            :value="costItems" 
                            :paginator="true" 
                            :rows="10"
                            :rowsPerPageOptions="[10, 20, 50]"
                            responsiveLayout="scroll"
                            :globalFilterFields="['category', 'source']"
                            filterDisplay="row"
                            v-model:filters="filters"
                            sortMode="multiple"
                            class="p-datatable-sm">
                            
                            <template #header>
                                <div class="table-header-actions">
                                    <span class="p-input-icon-left">
                                        <i class="pi pi-search" />
                                        <InputText 
                                            v-model="filters['global'].value" 
                                            placeholder="Ara..." 
                                            class="w-full" />
                                    </span>
                                </div>
                            </template>

                            <Column field="category" header="Maliyet Kalemi" :sortable="true" style="min-width: 300px">
                                <template #body="slotProps">
                                    <div class="category-cell">
                                        <Tag :severity="getSourceSeverity(slotProps.data.source)" 
                                             :value="slotProps.data.source" 
                                             class="mr-2" />
                                        <span>{{ slotProps.data.category }}</span>
                                    </div>
                                </template>
                            </Column>

                            <Column field="amount" header="Tutar" :sortable="true" style="min-width: 150px">
                                <template #body="slotProps">
                                    <span class="amount-value">₺{{ formatCurrency(slotProps.data.amount) }}</span>
                                </template>
                            </Column>

                            <Column field="percentage" header="Oran" :sortable="true" style="min-width: 100px">
                                <template #body="slotProps">
                                    <div class="percentage-cell">
                                        <ProgressBar 
                                            :value="calculatePercentage(slotProps.data.amount, grandTotal)" 
                                            :showValue="false"
                                            style="height: 6px; flex: 1;" />
                                        <span class="percentage-value ml-2">
                                            %{{ calculatePercentage(slotProps.data.amount, grandTotal) }}
                                        </span>
                                    </div>
                                </template>
                            </Column>

                            <template #footer>
                                <div class="table-footer">
                                    <div class="footer-info">
                                        Toplam {{ costItems.length }} maliyet kalemi
                                    </div>
                                    <div class="footer-total">
                                        <strong>Genel Toplam:</strong> 
                                        <span class="total-amount">₺{{ formatCurrency(grandTotal) }}</span>
                                    </div>
                                </div>
                            </template>

                            <template #empty>
                                <div class="empty-state">
                                    <i class="pi pi-inbox" style="font-size: 3rem; color: #6c757d;"></i>
                                    <p>Bu dönem için maliyet verisi bulunamadı.</p>
                                </div>
                            </template>
                        </DataTable>
                    </template>
                </Card>
            </div>

            <!-- Kaynak Bazlı Özet -->
            <div class="source-summary mt-4">
                <Card>
                    <template #title>
                        <h4>📊 Kaynak Bazlı Dağılım</h4>
                    </template>
                    <template #content>
                        <div class="source-distribution">
                            <div v-for="source in sourceTotals" :key="source.source" class="source-item">
                                <div class="source-header">
                                    <span class="source-name">{{ source.source }}</span>
                                    <span class="source-amount">₺{{ formatCurrency(source.total) }}</span>
                                </div>
                                <ProgressBar 
                                    :value="calculatePercentage(source.total, grandTotal)"
                                    :showValue="true"
                                    style="height: 20px;" />
                            </div>
                        </div>
                    </template>
                </Card>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useToast } from 'primevue/usetoast';
import axios from 'axios';
import { FilterMatchMode } from 'primevue/api';

// PrimeVue Components
import Button from 'primevue/button';
import Card from 'primevue/card';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import InputText from 'primevue/inputtext';
import ProgressSpinner from 'primevue/progressspinner';
import ProgressBar from 'primevue/progressbar';
import Tag from 'primevue/tag';

// Composables
const toast = useToast();

// State
const loading = ref(false);
const selectedPeriod = ref('1month');
const costItems = ref([]);
const sourceTotals = ref([]);
const grandTotal = ref(0);
const dateRange = ref({ start: '', end: '' });

// Period Options
const periodOptions = ref([
    { label: 'Son 1 Ay', value: '1month', icon: 'pi pi-calendar' },
    { label: 'Son 2 Ay', value: '2months', icon: 'pi pi-calendar-plus' },
    { label: 'Son 3 Ay', value: '3months', icon: 'pi pi-calendar-times' },
    { label: 'Son 6 Ay', value: '6months', icon: 'pi pi-calendar' },
    { label: 'Son 1 Yıl', value: '1year', icon: 'pi pi-calendar-minus' }
]);

// Filters
const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS }
});

// Computed
const currentDateRange = computed(() => {
    if (dateRange.value.start && dateRange.value.end) {
        return `${dateRange.value.start} - ${dateRange.value.end}`;
    }
    return '';
});

// Methods
const fetchCostsData = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/api/costs/periodic/report', {
            params: { period: selectedPeriod.value }
        });

        if (response.data) {
            costItems.value = response.data.costs || [];
            sourceTotals.value = response.data.sourceTotals || [];
            grandTotal.value = response.data.grandTotal || 0;
            dateRange.value = response.data.dateRange || { start: '', end: '' };
        }
    } catch (error) {
        console.error('Maliyet verileri yüklenirken hata:', error);
        toast.add({
            severity: 'error',
            summary: 'Hata',
            detail: 'Maliyet verileri yüklenirken bir hata oluştu.',
            life: 3000
        });
    } finally {
        loading.value = false;
    }
};

const changePeriod = (period) => {
    selectedPeriod.value = period;
    fetchCostsData();
};

const formatCurrency = (value) => {
    if (!value) return '0';
    return new Intl.NumberFormat('tr-TR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(value);
};

const calculatePercentage = (value, total) => {
    if (!total || total === 0) return 0;
    return ((value / total) * 100).toFixed(1);
};

const getSourceIcon = (source) => {
    if (source === 'Genel Giderler') return 'pi pi-wallet';
    if (source === 'Tedarikler') return 'pi pi-truck';
    return 'pi pi-tag';
};

const getSourceSeverity = (source) => {
    if (source === 'Genel Giderler') return 'warning';
    if (source === 'Tedarikler') return 'info';
    return 'secondary';
};

const exportToExcel = async () => {
    try {
        const response = await axios.get('/api/costs-export', {
            params: {
                period: selectedPeriod.value,
                start_date: dateRange.value.start,
                end_date: dateRange.value.end
            },
            responseType: 'blob'
        });

        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', `maliyet_raporu_${selectedPeriod.value}.xlsx`);
        document.body.appendChild(link);
        link.click();
        link.remove();

        toast.add({
            severity: 'success',
            summary: 'Başarılı',
            detail: 'Excel dosyası indirildi.',
            life: 3000
        });
    } catch (error) {
        console.error('Excel export hatası:', error);
        toast.add({
            severity: 'error',
            summary: 'Hata',
            detail: 'Excel dosyası oluşturulurken bir hata oluştu.',
            life: 3000
        });
    }
};

// Lifecycle
onMounted(() => {
    fetchCostsData();
});
</script>

<style scoped>
.costs-analysis {
    display: flex;
    flex-direction: column;
    gap: 25px;
    width: 100%;
    padding: 0;
}

.period-selector {
    background: rgba(248, 250, 252, 0.05);
    padding: 1.5rem;
    border-radius: 12px;
    border: 1px solid rgba(226, 232, 240, 0.1);
    backdrop-filter: blur(10px);
}

.period-selector h3 {
    color: var(--text-color);
    margin: 0 0 1rem 0;
    font-size: 1.25rem;
    font-weight: 600;
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

.loading-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 15px;
    padding: 60px 40px;
    color: var(--text-color-secondary);
}

.summary-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 20px;
}

.summary-card {
    background: var(--surface-card);
    padding: 1.5rem;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    border-left: 4px solid var(--primary-color);
    transition: all 0.3s ease;
}

.summary-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
}

.card-header {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: var(--text-color-secondary);
    font-size: 0.9rem;
    margin-bottom: 0.75rem;
}

.card-header i {
    font-size: 1.1rem;
    color: var(--primary-color);
}

.card-value {
    font-size: 1.8rem;
    font-weight: 700;
    color: var(--text-color);
    line-height: 1.2;
}

.card-period {
    font-size: 0.85rem;
    color: var(--text-color-secondary);
    margin-top: 0.5rem;
}

.card-percentage {
    font-size: 1rem;
    color: var(--green-500);
    margin-top: 0.25rem;
    font-weight: 600;
}

.costs-table-section {
    width: 100%;
}

.table-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0;
}

.table-header h4 {
    margin: 0;
    color: var(--text-color);
    font-size: 1.1rem;
    font-weight: 600;
}

.table-header-actions {
    display: flex;
    gap: 1rem;
    align-items: center;
    width: 300px;
}

.category-cell {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.amount-value {
    font-weight: 600;
    color: var(--text-color);
    font-size: 0.95rem;
}

.percentage-cell {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.percentage-value {
    font-size: 0.9rem;
    color: var(--text-color-secondary);
    min-width: 45px;
    font-weight: 500;
}

.table-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 1rem;
    border-top: 1px solid var(--surface-border);
    margin-top: 1rem;
}

.footer-info {
    color: var(--text-color-secondary);
    font-size: 0.9rem;
}

.total-amount {
    font-size: 1.3rem;
    font-weight: 700;
    color: var(--primary-color);
    margin-left: 0.5rem;
}

.empty-state {
    text-align: center;
    padding: 4rem 2rem;
    color: var(--text-color-secondary);
}

.empty-state i {
    color: var(--text-color-secondary);
    opacity: 0.6;
}

.empty-state p {
    margin-top: 1rem;
    font-size: 1rem;
}

.source-summary {
    margin-top: 0;
}

.source-distribution {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.source-item {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.source-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.source-name {
    font-weight: 600;
    color: var(--text-color);
    font-size: 1rem;
}

.source-amount {
    font-weight: 700;
    color: var(--primary-color);
    font-size: 1.1rem;
}

/* Dark Mode Specific */
:global(.p-dark) .period-selector {
    background: rgba(30, 41, 59, 0.4);
    border-color: rgba(71, 85, 105, 0.3);
}

:global(.p-dark) .summary-card {
    background: var(--surface-card);
    border-left-color: var(--primary-color);
}

:global(.p-dark) .summary-card:hover {
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.3);
}

:global(.p-dark) .table-footer {
    border-top-color: var(--surface-border);
}

/* DataTable Customizations */
:global(.costs-analysis .p-datatable) {
    border-radius: 12px;
    overflow: hidden;
}

:global(.costs-analysis .p-datatable .p-datatable-header) {
    background: var(--surface-section);
    border-bottom: 1px solid var(--surface-border);
    padding: 1rem 1.5rem;
}

:global(.costs-analysis .p-datatable .p-datatable-tbody > tr) {
    transition: all 0.2s ease;
}

:global(.costs-analysis .p-datatable .p-datatable-tbody > tr:hover) {
    background: var(--surface-hover) !important;
}

:global(.costs-analysis .p-datatable .p-column-title) {
    font-weight: 600;
    color: var(--text-color);
}

:global(.costs-analysis .p-paginator) {
    background: var(--surface-section);
    border-top: 1px solid var(--surface-border);
    padding: 1rem;
}

/* Card Customizations */
:global(.costs-analysis .p-card) {
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
}

:global(.costs-analysis .p-card:hover) {
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
}

:global(.costs-analysis .p-card .p-card-title) {
    color: var(--text-color);
    font-weight: 600;
    font-size: 1.1rem;
}

:global(.costs-analysis .p-card .p-card-content) {
    padding: 0;
}

/* Progress Bar Customizations */
:global(.costs-analysis .p-progressbar) {
    border-radius: 6px;
    overflow: hidden;
}

:global(.costs-analysis .p-progressbar .p-progressbar-value) {
    background: linear-gradient(90deg, var(--primary-color), var(--primary-color-light));
    transition: all 0.3s ease;
}

/* Tag Customizations */
:global(.costs-analysis .p-tag) {
    font-size: 0.75rem;
    font-weight: 500;
    padding: 0.25rem 0.5rem;
    border-radius: 6px;
}

/* Responsive Design */
@media (max-width: 1200px) {
    .summary-cards {
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    }
}

@media (max-width: 768px) {
    .costs-analysis {
        gap: 20px;
    }
    
    .summary-cards {
        grid-template-columns: 1fr;
        gap: 15px;
    }
    
    .period-buttons {
        flex-direction: column;
        gap: 8px;
    }
    
    .period-btn {
        width: 100%;
        justify-content: center;
    }
    
    .table-header {
        flex-direction: column;
        gap: 1rem;
        align-items: stretch;
    }
    
    .table-header-actions {
        width: 100%;
    }
    
    .summary-card {
        padding: 1.25rem;
    }
    
    .card-value {
        font-size: 1.6rem;
    }
    
    .source-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }
}

@media (max-width: 480px) {
    .period-selector {
        padding: 1rem;
    }
    
    .summary-card {
        padding: 1rem;
    }
    
    .card-value {
        font-size: 1.4rem;
    }
    
    .period-selector h3 {
        font-size: 1.1rem;
    }
}

/* Animation for loading and transitions */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.summary-cards {
    animation: fadeIn 0.6s ease-out;
}

.costs-table-section {
    animation: fadeIn 0.8s ease-out;
}

.source-summary {
    animation: fadeIn 1s ease-out;
}
</style>
