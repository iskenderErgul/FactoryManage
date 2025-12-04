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
    reportData.value = await fetchExecutiveSummary(start, end);
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
        <div v-else-if="reportData?.data" class="report-content">
            <!-- Period Info -->
            <Card class="mb-4">
                <template #content>
                    <div class="text-center">
                        <h2 class="text-xl font-bold text-white mb-2">📋 Üretim Özet Raporu</h2>
                        <p class="text-gray-300">
                            {{ new Date(reportData.data.period.start_date).toLocaleDateString('tr-TR') }} - 
                            {{ new Date(reportData.data.period.end_date).toLocaleDateString('tr-TR') }}
                            <span class="text-gray-500 ml-2">({{ reportData.data.period.days }} gün)</span>
                        </p>
                    </div>
                </template>
            </Card>

            <!-- KPIs -->
            <div class="grid mb-4">
                <div class="col-6 md:col-3" v-for="(kpi, index) in reportData.data.kpis" :key="index">
                    <Card class="kpi-card">
                        <template #content>
                            <div class="text-center">
                                <div class="text-sm text-gray-300 mb-1">{{ kpi.name }}</div>
                                <div class="text-2xl font-bold text-white">
                                    {{ formatNumber(kpi.value) }}
                                    <span class="text-sm text-gray-400">{{ kpi.unit }}</span>
                                </div>
                                <div v-if="kpi.change !== null" 
                                     :class="['text-sm', getChangeColor(kpi.change)]">
                                    {{ kpi.change >= 0 ? '+' : '' }}{{ kpi.change }}% önceki döneme göre
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
                            <DataTable :value="reportData.data.top_products" :rows="5">
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
                            <DataTable :value="reportData.data.top_workers" :rows="5">
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
            <Card v-if="reportData.data.previous_period">
                <template #title>📊 Önceki Dönem Karşılaştırması</template>
                <template #content>
                    <div class="grid">
                        <div class="col-12 md:col-4">
                            <div class="p-3 bg-gray-800 rounded">
                                <div class="text-sm text-gray-400">Önceki Dönem</div>
                                <div class="text-lg text-gray-200">
                                    {{ new Date(reportData.data.previous_period.previous_start).toLocaleDateString('tr-TR') }} - 
                                    {{ new Date(reportData.data.previous_period.previous_end).toLocaleDateString('tr-TR') }}
                                </div>
                            </div>
                        </div>
                        <div class="col-12 md:col-4">
                            <div class="p-3 bg-gray-800 rounded">
                                <div class="text-sm text-gray-400">Önceki Dönem Üretimi</div>
                                <div class="text-lg text-gray-200">
                                    {{ formatNumber(reportData.data.previous_period.previous_total) }}
                                </div>
                            </div>
                        </div>
                        <div class="col-12 md:col-4">
                            <div class="p-3 bg-gray-800 rounded">
                                <div class="text-sm text-gray-400">Değişim</div>
                                <div class="text-lg font-bold" 
                                     :class="getChangeColor(reportData.data.previous_period.quantity_change)">
                                    {{ reportData.data.previous_period.quantity_change >= 0 ? '+' : '' }}{{ reportData.data.previous_period.quantity_change }}%
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
