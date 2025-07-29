<template>
    <Card class="kpi-card">
        <template #content>
            <div class="kpi-content">
                <i :class="`pi ${icon} kpi-icon ${iconClass}`"></i>
                <div class="kpi-info">
                    <h3>{{ formattedValue }}</h3>
                    <p>{{ label }}</p>
                </div>
            </div>
        </template>
    </Card>
</template>

<script setup>
import { computed } from 'vue';
import Card from 'primevue/card';

// Props
const props = defineProps({
    icon: {
        type: String,
        required: true
    },
    iconClass: {
        type: String,
        default: ''
    },
    value: {
        type: [Number, String],
        required: true
    },
    label: {
        type: String,
        required: true
    },
    format: {
        type: String,
        default: 'number', // 'number', 'currency', 'text'
        validator: (value) => ['number', 'currency', 'text'].includes(value)
    }
});

// Computed
const formattedValue = computed(() => {
    if (props.format === 'text') {
        return props.value;
    } else if (props.format === 'currency') {
        return `₺${Number(props.value).toLocaleString()}`;
    } else if (props.format === 'number') {
        return Number(props.value).toLocaleString();
    }
    return props.value;
});
</script>

<style scoped>
.kpi-card {
    transition: all 0.3s ease;
    background: rgba(30, 41, 59, 0.95) !important;
    border: 1px solid rgba(71, 85, 105, 0.3) !important;
    box-shadow: 0 4px 20px rgba(0,0,0,0.25);
}

.kpi-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 30px rgba(0,0,0,0.4);
}

.kpi-content {
    display: flex;
    align-items: center;
    gap: 20px;
    padding: 10px;
}

.kpi-icon {
    font-size: 3rem;
    opacity: 0.8;
}

.kpi-icon.production { color: #3B82F6; }
.kpi-icon.sales { color: #10B981; }
.kpi-icon.workers { color: #8B5CF6; }
.kpi-icon.machines { color: #F59E0B; }
.kpi-icon.customers { color: #EC4899; }
.kpi-icon.orders { color: #06B6D4; }

.kpi-info h3 {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 5px;
    color: #F1F5F9 !important;
}

.kpi-info p {
    color: #94A3B8 !important;
    font-weight: 500;
}
</style>
