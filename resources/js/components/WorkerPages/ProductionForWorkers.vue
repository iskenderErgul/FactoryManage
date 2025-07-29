<template>
    <Toast ref="toast" />
    <div class="p-card">
        <h2 class="text-center">Üretim Ekle</h2>
        <div class="form-grid">
            <div class="form-group">
                <label for="shift">Vardiya Seçin:</label>
                <Dropdown id="shift" v-model="production.shift" :options="shiftTemplates" optionLabel="name" placeholder="Vardiya Seçin" />
            </div>
            <div class="form-group">
                <label for="machine">Üretildiği Makineyi Seçin:</label>
                <Dropdown id="machine" v-model="production.machine" :options="machines" optionLabel="machine_name" placeholder="Makine Seçin" />
            </div>
            <div class="form-group">
                <label for="product">Üretilen Ürün Seçin:</label>
                <Dropdown id="product" v-model="production.product" :options="products" optionLabel="product_name" placeholder="Ürün Seçin" />
            </div>
            <div class="form-group">
                <label for="quantity">Üretilen Miktar(Koli Cinsinden ):</label>
                <InputNumber id="quantity" v-model="production.quantity" mode="decimal" placeholder="Miktar" />
            </div>
        </div>
        <Button label="Kaydet" @click="saveProduction" class="save-button w-full" />
    </div>

    <div class="card mt-4">
        <div>
            <h2 class="text-center">Geçmiş Üretimler</h2>
        </div>
        <DataTable :value="filteredProductions" tableStyle="min-width: 50rem" paginator :rows="5" :rowsPerPageOptions="[5, 10, 20, 50]">
            <Column field="shift_name" header="Vardiya"></Column>
            <Column field="production_date" header="Üretim Tarihi"></Column>
            <Column field="machine_name" header="Makine"></Column>
            <Column field="product_name" header="Ürün"></Column>
            <Column field="quantity" header="Miktar"></Column>
        </DataTable>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted , computed } from 'vue';
import { useStore } from 'vuex';

import axios from 'axios'; // Axios'u kullanacağız
import Button from 'primevue/button';
import Dropdown from 'primevue/dropdown';
import InputNumber from 'primevue/inputnumber';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Toast from 'primevue/toast';

const toast = ref(null);
const productions = ref([]);
const store = useStore();
const shifts = ref([]);
const machines = ref([]);
const products = ref([]);
const shiftTemplates = ref([]); // Yeni eklenen vardiya şablonları

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

const fetchProductions = () =>  {
    axios.get('/api/productions')
        .then(response => {
            productions.value = response.data.reverse();
        })
        .catch(error => {
            console.error("Üretimler alınırken hata:", error);
        });
};


// Form verileri
const production = reactive({
    shift: null, // Vardiya şablonu
    machine: null,
    product: null,
    quantity: null
});

const fetchMachines = async () => {
    try {
        const response = await axios.get('/api/machines'); // Backend API endpoint
        machines.value = response.data; // API'den gelen makineleri kaydet
    } catch (error) {
        toast.add({ severity: 'error', summary: 'Hata', detail: 'Makine verileri alınırken bir hata oluştu.', life: 3000 });
    }
};
const fetchProducts = async () => {
    try {
        const response = await axios.get('/api/products'); // Backend API endpoint
        products.value = response.data; // API'den gelen ürünleri kaydet
    } catch (error) {
        toast.add({ severity: 'error', summary: 'Hata', detail: 'Ürün verileri alınırken bir hata oluştu.', life: 3000 });
    }
};
const fetchShiftTemplates = async () => {
    try {
        const response = await axios.get(`/api/shift/user-shift-templates/${currentUser.value.id}`);
        shiftTemplates.value = response.data;
    } catch (error) {
        toast.value.add({ severity: 'error', summary: 'Hata', detail: 'Vardiya şablonları yüklenemedi', life: 3000 });
    }
}

// Sayfa yüklendiğinde makineleri, ürünleri ve vardiya şablonlarını çek
onMounted(async () => {
    await fetchMachines();
    await fetchProducts();
    await fetchProductions();
    await fetchShiftTemplates();
});

const saveProduction = async () => {
    if (production.shift && production.machine && production.product && production.quantity) {
        try {
            const response = await axios.post('/api/productions/worker', {
                user_id: currentUser.value.id,
                shift_template_id: production.shift.id,
                machine_id: production.machine.id,
                product_id: production.product.id,
                quantity: production.quantity
            });

            // Üretim başarıyla eklendikten sonra listeyi yenileyin
            await fetchProductions();

            toast.value.add({ severity: 'success', summary: 'Başarılı', detail: 'Üretim kaydedildi.', life: 3000 });

            production.shift = null;
            production.machine = null;
            production.product = null;
            production.quantity = null;
        } catch (error) {
            toast.value.add({ severity: 'error', summary: 'Hata', detail: 'Üretim kaydedilirken bir hata oluştu.', life: 3000 });
        }
    } else {
        toast.value.add({ severity: 'warn', summary: 'Uyarı', detail: 'Lütfen tüm alanları doldurun.', life: 3000 });
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
</style>
