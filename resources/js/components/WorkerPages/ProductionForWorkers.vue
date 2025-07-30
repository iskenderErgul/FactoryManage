<template>
    <Toast ref="toast" />
    <div class="p-card">
        <h2 class="text-center">Üretim Ekle</h2>

        <!-- Mevcut Vardiya Bilgisi -->
        <div class="current-shift-info mb-4" v-if="selectedShift">
            <div class="shift-badge">
                <i class="pi pi-clock"></i>
                <span>Mevcut Vardiya: <strong>{{ selectedShift.name }}</strong></span>
                <small>({{ selectedShift.start_time.substring(0,5) }} - {{ selectedShift.end_time.substring(0,5) }})</small>
            </div>
        </div>

        <div class="form-grid">
            <!-- Vardiya seçimi kaldırıldı, otomatik tespit edilecek -->
            <div class="form-group">
                <label for="machine">Üretildiği Makineyi Seçin:</label>
                <Dropdown
                    id="machine"
                    v-model="production.machine"
                    :options="machines"
                    optionLabel="machine_name"
                    placeholder="Makine Seçin"
                    :disabled="!selectedShift"
                />
            </div>
            <div class="form-group">
                <label for="product">Üretilen Ürün Seçin:</label>
                <Dropdown
                    id="product"
                    v-model="production.product"
                    :options="products"
                    optionLabel="product_name"
                    placeholder="Ürün Seçin"
                    :disabled="!selectedShift"
                />
            </div>
            <div class="form-group">
                <label for="quantity">Üretilen Miktar (Koli Cinsinden):</label>
                <InputNumber
                    id="quantity"
                    v-model="production.quantity"
                    mode="decimal"
                    placeholder="Miktar"
                    :disabled="!selectedShift"
                />
            </div>
        </div>
        <Button
            label="Kaydet"
            @click="saveProduction"
            class="save-button w-full"
            :disabled="!selectedShift"
        />

        <div v-if="!selectedShift && !loadingShift" class="no-shift-warning mt-3">
            <Message severity="warn" :closable="false">
                Şu anda aktif bir vardiyada değilsiniz. Üretim kaydı yapabilmek için vardiya saatlerinizde olmalısınız.
            </Message>
        </div>
    </div>

    <div class="card mt-4">
        <div>
            <h2 class="text-center">Geçmiş Üretimler</h2>
        </div>
        <DataTable
            :value="filteredProductions"
            tableStyle="min-width: 50rem"
            paginator
            :rows="5"
            :rowsPerPageOptions="[5, 10, 20, 50]"
        >
            <Column field="shift_name" header="Vardiya"></Column>
            <Column field="production_date" header="Üretim Tarihi"></Column>
            <Column field="machine_name" header="Makine"></Column>
            <Column field="product_name" header="Ürün"></Column>
            <Column field="quantity" header="Miktar"></Column>
        </DataTable>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue';
import { useStore } from 'vuex';
import axios from 'axios';
import Button from 'primevue/button';
import Dropdown from 'primevue/dropdown';
import InputNumber from 'primevue/inputnumber';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Toast from 'primevue/toast';
import Message from 'primevue/message';

const toast = ref(null);
const productions = ref([]);
const store = useStore();
const machines = ref([]);
const products = ref([]);
const todayShifts = ref([]);
const selectedShift = ref(null);
const loadingShift = ref(true);

const currentUser = computed(() => store.state.user);

const filteredProductions = computed(() => {
    return productions.value
        .filter((prod) => prod.user_id === currentUser.value.id)
        .map((prod) => ({
            shift_name: prod.shift.template.name,
            production_date: prod.production_date,
            machine_name: prod.machine.machine_name,
            product_name: prod.product.product_name,
            quantity: prod.quantity,
        }));
});

// Form verileri - shift kaldırıldı
const production = reactive({
    machine: null,
    product: null,
    quantity: null
});

const fetchProductions = () => {
    axios.get('/api/productions')
        .then(response => {
            productions.value = response.data.reverse();
        })
        .catch(error => {
            console.error("Üretimler alınırken hata:", error);
        });
};

const fetchMachines = async () => {
    try {
        const response = await axios.get('/api/machines');
        machines.value = response.data;
    } catch (error) {
        toast.value.add({
            severity: 'error',
            summary: 'Hata',
            detail: 'Makine verileri alınırken bir hata oluştu.',
            life: 3000
        });
    }
};

const fetchProducts = async () => {
    try {
        const response = await axios.get('/api/products');
        products.value = response.data;
    } catch (error) {
        toast.value.add({
            severity: 'error',
            summary: 'Hata',
            detail: 'Ürün verileri alınırken bir hata oluştu.',
            life: 3000
        });
    }
};

// Mevcut vardiyayı getir
const fetchCurrentShift = async () => {
    loadingShift.value = true;
    try {
        const response = await axios.get('/api/current-shift');
        todayShifts.value = response.data.today_shifts || [];
        selectedShift.value = todayShifts.value.length > 0 ? todayShifts.value[0] : null;
        if (todayShifts.value.length === 0) {
            toast.value.add({
                severity: 'info',
                summary: 'Bilgi',
                detail: 'Bugün için atanmış vardiyanız yok.',
                life: 4000
            });
        }
    } catch (error) {
        console.error('Mevcut vardiya alınırken hata:', error);
        toast.value.add({
            severity: 'warn',
            summary: 'Uyarı',
            detail: 'Vardiya bilgisi alınamadı.',
            life: 3000
        });
    } finally {
        loadingShift.value = false;
    }
};

// Sayfa yüklendiğinde verileri çek
onMounted(async () => {
    await fetchMachines();
    await fetchProducts();
    await fetchProductions();
    await fetchCurrentShift();

    // Her 5 dakikada bir mevcut vardiyayı kontrol et
    setInterval(fetchCurrentShift, 5 * 60 * 1000);
});

const saveProduction = async () => {
    if (!selectedShift.value) {
        toast.value.add({
            severity: 'warn',
            summary: 'Uyarı',
            detail: 'Bugün için atanmış bir vardiyanız yok.',
            life: 3000
        });
        return;
    }
    if (production.machine && production.product && production.quantity) {
        try {
            const response = await axios.post('/api/productions/worker', {
                user_id: currentUser.value.id,
                machine_id: production.machine.id,
                product_id: production.product.id,
                quantity: production.quantity,
                shift_id: selectedShift.value.id // shift_id gönder
            });
            await fetchProductions();
            toast.value.add({
                severity: 'success',
                summary: 'Başarılı',
                detail: response.data.message || 'Üretim kaydedildi.',
                life: 3000
            });
            production.machine = null;
            production.product = null;
            production.quantity = null;
        } catch (error) {
            console.error('Üretim kaydetme hatası:', error);
            const errorMessage = error.response?.data?.message || 'Üretim kaydedilirken bir hata oluştu.';
            toast.value.add({
                severity: 'error',
                summary: 'Hata',
                detail: errorMessage,
                life: 3000
            });
        }
    } else {
        toast.value.add({
            severity: 'warn',
            summary: 'Uyarı',
            detail: 'Lütfen tüm alanları doldurun.',
            life: 3000
        });
    }
};
</script>

<style scoped>
.p-card {
    padding: 2rem;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.save-button {
    margin-top: 1rem;
}

.current-shift-info {
    background: linear-gradient(135deg, #22c55e, #16a34a);
    border-radius: 12px;
    padding: 1rem;
    color: white;
    text-align: center;
}

.shift-badge {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.shift-badge i {
    font-size: 1.2rem;
}

.no-shift-warning {
    text-align: center;
}

@media (max-width: 768px) {
    .form-grid {
        grid-template-columns: 1fr;
    }

    .shift-badge {
        flex-direction: column;
        gap: 0.25rem;
    }
}
</style>
