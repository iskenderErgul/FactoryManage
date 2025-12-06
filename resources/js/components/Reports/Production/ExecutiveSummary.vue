<script setup>
import { ref, onMounted } from 'vue';
import { useProductionReports } from '@/composables/useProductionReports';
import Calendar from 'primevue/calendar';
import Button from 'primevue/button';
import Card from 'primevue/card';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';

const { loading, error, fetchExecutiveSummary } = useProductionReports();

const startDate = ref(new Date(new Date().setMonth(new Date().getMonth() - 1)));
const endDate = ref(new Date());
const reportData = ref(null);

const formatNumber = (value) => new Intl.NumberFormat('tr-TR').format(value);

const getChangeColor = (change) => {
    if (change === null) return 'text-gray-400';
    return change >= 0 ? 'text-green-500' : 'text-red-500';
};

const loadReport = async () => {
    const start = startDate.value.toISOString().split('T')[0];
    const end = endDate.value.toISOString().split('T')[0];
    const response = await fetchExecutiveSummary(start, end);
    reportData.value = response.data || response;
};

onMounted(loadReport);
</script>

<template>
    <div class="executive-summary">
        <!-- Filters -->
        <div class="filters mb-4 flex gap-4 align-items-center flex-wrap">
            <div class="field">
                <label class="block mb-2 text-gray-300">Başlangıç Tarihi</label>
                <Calendar v-model="startDate" dateFormat="dd/mm/yy" showIcon />
            </div>
            <div class="field">
                <label class="block mb-2 text-gray-300">Bitiş Tarihi</label>
                <Calendar v-model="endDate" dateFormat="dd/mm/yy" showIcon />
            </div>
            <div class="field">
                <label class="block mb-2">&nbsp;</label>
                <Button label="Raporu Getir" icon="pi pi-search" @click="loadReport" :loading="loading" />
            </div>
        </div>

        <!-- Loading -->
        <div v-if="loading" class="text-center py-6">
            <i class="pi pi-spin pi-spinner text-4xl text-primary"></i>
            <p class="mt-3 text-gray-400">Özet rapor yükleniyor...</p>
        </div>

        <!-- Error -->
        <div v-else-if="error" class="p-4 bg-red-900/50 text-red-300 rounded">{{ error }}</div>

        <!-- Content -->
        <div v-else-if="reportData" class="report-content">
            <!-- Period Info -->
            <Card class="mb-4">
                <template #content>
                    <div class="text-center">
                        <h2 class="text-xl font-bold text-white mb-2">📋 Üretim Özet Raporu</h2>
                        <p class="text-gray-300">
                            {{ reportData.period?.current?.start_date }} - {{ reportData.period?.current?.end_date }}
                            <span class="text-gray-500 ml-2">({{ reportData.period?.current?.days || 0 }} gün)</span>
                        </p>
                    </div>
                </template>
            </Card>

            <!-- Current Period Stats -->
            <div class="grid mb-4">
                <div class="col-6 md:col-3">
                    <Card class="kpi-card">
                        <template #content>
                            <div class="text-center">
                                <div class="text-sm text-gray-300 mb-1">Toplam Üretim</div>
                                <div class="text-2xl font-bold text-white">
                                    {{ formatNumber(reportData.current_period?.total_quantity || 0) }}
                                </div>
                                <div v-if="reportData.comparison?.quantity" 
                                     :class="['text-sm', getChangeColor(reportData.comparison.quantity.rate)]">
                                    {{ reportData.comparison.quantity.rate >= 0 ? '+' : '' }}{{ reportData.comparison.quantity.rate }}%
                                </div>
                            </div>
                        </template>
                    </Card>
                </div>
                <div class="col-6 md:col-3">
                    <Card class="kpi-card">
                        <template #content>
                            <div class="text-center">
                                <div class="text-sm text-gray-300 mb-1">Üretim Sayısı</div>
                                <div class="text-2xl font-bold text-white">
                                    {{ formatNumber(reportData.current_period?.total_count || 0) }}
                                </div>
                                <div v-if="reportData.comparison?.count" 
                                     :class="['text-sm', getChangeColor(reportData.comparison.count.rate)]">
                                    {{ reportData.comparison.count.rate >= 0 ? '+' : '' }}{{ reportData.comparison.count.rate }}%
                                </div>
                            </div>
                        </template>
                    </Card>
                </div>
                <div class="col-6 md:col-3">
                    <Card class="kpi-card">
                        <template #content>
                            <div class="text-center">
                                <div class="text-sm text-gray-300 mb-1">Aktif İşçi</div>
                                <div class="text-2xl font-bold text-white">
                                    {{ reportData.current_period?.unique_workers || 0 }}
                                </div>
                            </div>
                        </template>
                    </Card>
                </div>
                <div class="col-6 md:col-3">
                    <Card class="kpi-card">
                        <template #content>
                            <div class="text-center">
                                <div class="text-sm text-gray-300 mb-1">Günlük Ortalama</div>
                                <div class="text-2xl font-bold text-white">
                                    {{ formatNumber(reportData.key_metrics?.daily_average || 0) }}
                                </div>
                            </div>
                        </template>
                    </Card>
                </div>
            </div>

            <!-- Top Products & Workers -->
            <div class="grid mb-4">
                <div class="col-12 md:col-6">
                    <Card>
                        <template #title>🏆 En Çok Üretilen Ürünler</template>
                        <template #content>
                            <DataTable :value="reportData.top_performers?.top_products || []" :rows="5">
                                <Column field="product_name" header="Ürün"></Column>
                                <Column field="total_quantity" header="Üretim">
                                    <template #body="{ data }">
                                        <span class="font-bold text-green-500">{{ formatNumber(data.total_quantity) }}</span>
                                    </template>
                                </Column>
                            </DataTable>
                        </template>
                    </Card>
                </div>
                <div class="col-12 md:col-6">
                    <Card>
                        <template #title>👷 En Verimli İşçiler</template>
                        <template #content>
                            <DataTable :value="reportData.top_performers?.top_workers || []" :rows="5">
                                <Column field="user_name" header="İşçi"></Column>
                                <Column field="total_quantity" header="Üretim">
                                    <template #body="{ data }">
                                        <span class="font-bold text-blue-500">{{ formatNumber(data.total_quantity) }}</span>
                                    </template>
                                </Column>
                            </DataTable>
                        </template>
                    </Card>
                </div>
            </div>

            <!-- Previous Period Comparison -->
            <Card v-if="reportData.previous_period">
                <template #title>📊 Önceki Dönem Karşılaştırması</template>
                <template #content>
                    <div class="grid">
                        <div class="col-12 md:col-4">
                            <div class="p-3 bg-gray-800 rounded">
                                <div class="text-sm text-gray-400">Önceki Dönem</div>
                                <div class="text-lg text-gray-200">
                                    {{ reportData.period?.previous?.start_date }} - {{ reportData.period?.previous?.end_date }}
                                </div>
                            </div>
                        </div>
                        <div class="col-12 md:col-4">
                            <div class="p-3 bg-gray-800 rounded">
                                <div class="text-sm text-gray-400">Önceki Dönem Üretimi</div>
                                <div class="text-lg text-gray-200">
                                    {{ formatNumber(reportData.previous_period.total_quantity) }}
                                </div>
                            </div>
                        </div>
                        <div class="col-12 md:col-4">
                            <div class="p-3 bg-gray-800 rounded">
                                <div class="text-sm text-gray-400">Değişim</div>
                                <div class="text-lg font-bold" 
                                     :class="getChangeColor(reportData.comparison?.quantity?.rate)">
                                    {{ reportData.comparison?.quantity?.rate >= 0 ? '+' : '' }}{{ reportData.comparison?.quantity?.rate }}%
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </Card>
        </div>
    </div>
</template>

<style scoped>
.executive-summary { padding: 1rem; }
.kpi-card {
    background: rgba(255, 255, 255, 0.05);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.1);
}
</style>

