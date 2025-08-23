<template>
    <Card class="chart-card">
        <template #header>
            <div class="card-header-with-toggle">
                <h4>{{ title }}</h4>
                <SelectButton
                    :modelValue="viewMode"
                    @update:modelValue="$emit('update:viewMode', $event)"
                    :options="viewModeOptions"
                    optionLabel="label"
                    optionValue="value" />
            </div>
        </template>
        <template #content>
            <Chart
                v-if="viewMode === 'chart'"
                :type="chartType"
                :data="chartData"
                :options="chartOptions" />

            <DataTable
                v-else
                :value="tableData"
                paginator
                :rows="tableRows"
                :sortField="sortField"
                :sortOrder="sortOrder"
                paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
                :rowsPerPageOptions="[10, 25, 50, 100]"
                currentPageReportTemplate="Mevcut {first} ile {last} arasında, toplam {totalRecords} kayıt">

                <Column
                    v-for="col in tableColumns"
                    :key="col.field"
                    :field="col.field"
                    :header="col.header"
                    :sortable="col.sortable">

                    <template #body="slotProps" v-if="col.type || $slots['cell-' + col.field]">
                        <!-- Custom cell slot -->
                        <slot 
                            v-if="$slots['cell-' + col.field]" 
                            :name="'cell-' + col.field" 
                            :data="slotProps.data" 
                            :value="slotProps.data[col.field]">
                        </slot>
                        <!-- Badge -->
                        <Badge
                            v-else-if="col.type === 'badge'"
                            :value="slotProps.data[col.field]"
                            :severity="col.severity || 'success'" />

                        <!-- Currency -->
                        <span v-else-if="col.type === 'currency'">
                            ₺{{ slotProps.data[col.field].toLocaleString() }}
                        </span>

                        <!-- Progress Bar -->
                        <ProgressBar
                            v-else-if="col.type === 'progress'"
                            :value="slotProps.data[col.field]" />

                        <!-- Tag -->
                        <Tag
                            v-else-if="col.type === 'tag'"
                            :value="slotProps.data[col.field]"
                            :severity="getTagSeverity(slotProps.data[col.field], col.severityMap)" />

                        <!-- Rating -->
                        <Rating
                            v-else-if="col.type === 'rating'"
                            :value="slotProps.data[col.field]"
                            :readonly="true"
                            :cancel="false" />

                        <!-- Date -->
                        <span v-else-if="col.type === 'date'">
                            {{ formatDate(slotProps.data[col.field]) }}
                        </span>

                        <!-- Actions -->
                        <div v-else-if="col.type === 'actions'">
                            <slot name="actions" :data="slotProps.data"></slot>
                        </div>

                        <!-- Default text -->
                        <span v-else>{{ slotProps.data[col.field] }}</span>
                    </template>
                </Column>
            </DataTable>
        </template>
    </Card>
</template>

<script setup>
import { ref } from 'vue';
import Card from 'primevue/card';
import Chart from 'primevue/chart';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import SelectButton from 'primevue/selectbutton';
import Badge from 'primevue/badge';
import Tag from 'primevue/tag';
import ProgressBar from 'primevue/progressbar';
import Rating from 'primevue/rating';

// Props
const props = defineProps({
    title: {
        type: String,
        required: true
    },
    viewMode: {
        type: String,
        default: 'chart'
    },
    chartType: {
        type: String,
        default: 'line'
    },
    chartData: {
        type: Object,
        required: true
    },
    chartOptions: {
        type: Object,
        required: true
    },
    tableData: {
        type: Array,
        required: true
    },
    tableColumns: {
        type: Array,
        required: true
    },
    tableRows: {
        type: Number,
        default: 10
    },
    sortField: {
        type: String,
        default: null
    },
    sortOrder: {
        type: Number,
        default: 1
    }
});

// Emits
const emit = defineEmits(['update:viewMode']);

// View Mode Options
const viewModeOptions = ref([
    { label: '📊', value: 'chart' },
    { label: '📋', value: 'table' }
]);

// Utility Functions
const getTagSeverity = (value, severityMap) => {
    if (!severityMap) return 'info';
    return severityMap[value] || 'info';
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('tr-TR');
};
</script>

<style scoped>
.chart-card {
    min-height: 400px;
    background: rgba(30, 41, 59, 0.95) !important;
    border: 1px solid rgba(71, 85, 105, 0.3) !important;
    box-shadow: 0 4px 20px rgba(0,0,0,0.25);
}

.card-header-with-toggle {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 0;
}

.card-header-with-toggle h4 {
    margin: 0;
    color: #F1F5F9 !important;
    font-weight: 600;
}

@media (max-width: 768px) {
    .card-header-with-toggle {
        flex-direction: column;
        gap: 10px;
        align-items: stretch;
    }
}
</style>
