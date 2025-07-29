<template>
    <div class="filter-section">
        <Card>
            <template #content>
                <div class="filter-grid">
                    <div class="filter-item" v-if="showDateRange">
                        <label>Tarih Aralığı:</label>
                        <Calendar
                            :modelValue="filters.dateRange"
                            @update:modelValue="updateFilter('dateRange', $event)"
                            selectionMode="range" />
                    </div>

                    <div class="filter-item" v-if="machines.length > 0">
                        <label>Makine:</label>
                        <Dropdown
                            :modelValue="filters.machine"
                            @update:modelValue="updateFilter('machine', $event)"
                            :options="machines"
                            optionLabel="machine_name"
                            optionValue="id"
                            placeholder="Tüm Makineler"
                            showClear />
                    </div>

                    <div class="filter-item" v-if="workers.length > 0">
                        <label>İşçi:</label>
                        <Dropdown
                            :modelValue="filters.worker"
                            @update:modelValue="updateFilter('worker', $event)"
                            :options="workers"
                            optionLabel="name"
                            optionValue="id"
                            placeholder="Tüm İşçiler"
                            showClear />
                    </div>

                    <div class="filter-item" v-if="customers.length > 0">
                        <label>Müşteri:</label>
                        <Dropdown
                            :modelValue="filters.customer"
                            @update:modelValue="updateFilter('customer', $event)"
                            :options="customers"
                            optionLabel="name"
                            optionValue="id"
                            placeholder="Tüm Müşteriler"
                            showClear />
                    </div>

                    <div class="filter-item" v-if="products.length > 0">
                        <label>Ürün:</label>
                        <Dropdown
                            :modelValue="filters.product"
                            @update:modelValue="updateFilter('product', $event)"
                            :options="products"
                            optionLabel="product_name"
                            optionValue="id"
                            placeholder="Tüm Ürünler"
                            showClear />
                    </div>

                    <div class="filter-actions">
                        <Button
                            label="Filtrele"
                            @click="applyFilter"
                            class="filter-btn"
                            icon="pi pi-search" />
                        <Button
                            label="Temizle"
                            @click="clearFilters"
                            class="clear-btn"
                            icon="pi pi-times"
                            severity="secondary" />
                    </div>
                </div>
            </template>
        </Card>
    </div>
</template>

<script setup>
import { ref, reactive } from 'vue';
import Card from 'primevue/card';
import Calendar from 'primevue/calendar';
import Dropdown from 'primevue/dropdown';
import Button from 'primevue/button';

// Props
const props = defineProps({
    filters: {
        type: Object,
        default: () => ({})
    },
    machines: {
        type: Array,
        default: () => []
    },
    workers: {
        type: Array,
        default: () => []
    },
    customers: {
        type: Array,
        default: () => []
    },
    products: {
        type: Array,
        default: () => []
    },
    showDateRange: {
        type: Boolean,
        default: true
    }
});

// Emits
const emit = defineEmits(['filter']);

// Local reactive filters
const localFilters = reactive({ ...props.filters });

// Methods
const updateFilter = (key, value) => {
    localFilters[key] = value;
};

const applyFilter = () => {
    emit('filter', { ...localFilters });
};

const clearFilters = () => {
    Object.keys(localFilters).forEach(key => {
        localFilters[key] = null;
    });
    emit('filter', { ...localFilters });
};
</script>

<style scoped>
.filter-section {
    margin-bottom: 25px;
}

.filter-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    align-items: end;
}

.filter-item label {
    display: block;
    margin-bottom: 5px;
    font-weight: 500;
    color: #F1F5F9 !important;
}

.filter-actions {
    display: flex;
    gap: 10px;
    align-items: center;
}

.filter-btn, .clear-btn {
    height: fit-content;
    white-space: nowrap;
}

/* Form Controls Override */
:deep(.p-inputtext) {
    background: rgba(51, 65, 85, 0.8) !important;
    border: 1px solid rgba(71, 85, 105, 0.5) !important;
    color: #E2E8F0 !important;
}

:deep(.p-dropdown) {
    background: rgba(51, 65, 85, 0.8) !important;
    border: 1px solid rgba(71, 85, 105, 0.5) !important;
    color: #E2E8F0 !important;
}

:deep(.p-dropdown .p-dropdown-label) {
    color: #E2E8F0 !important;
}

:deep(.p-calendar .p-inputtext) {
    background: rgba(51, 65, 85, 0.8) !important;
    color: #E2E8F0 !important;
}

@media (max-width: 768px) {
    .filter-grid {
        grid-template-columns: 1fr;
    }

    .filter-actions {
        justify-content: stretch;
    }

    .filter-btn, .clear-btn {
        flex: 1;
    }
}
</style>
