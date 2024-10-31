<template>
    <div class="p-card">
        <h2>Üretim Takibi</h2>
        <div class="form-grid">
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
        <Button label="Kaydet" @click="saveProduction" class="save-button" />

    </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';

import axios from 'axios'; // Axios'u kullanacağız
import Button from 'primevue/button';
import Dropdown from 'primevue/dropdown';
import InputNumber from 'primevue/inputnumber';
import Toast from 'primevue/toast';


// Form verileri
const production = reactive({
    machine: null,
    product: null,
    quantity: null
});

// Makineler ve ürünler için veri referansları
const machines = ref([]);
const products = ref([]);
const toast = ref(null);

// Backend'den makineleri ve ürünleri çekme
const fetchMachines = async () => {
    try {
        const response = await axios.get('/api/machines'); // Backend API endpoint
        machines.value = response.data; // API'den gelen makineleri kaydet
    } catch (error) {
        console.error('Makine verileri alınamadı:', error);
        toast.add({ severity: 'error', summary: 'Hata', detail: 'Makine verileri alınırken bir hata oluştu.', life: 3000 });
    }
};

const fetchProducts = async () => {
    try {
        const response = await axios.get('/api/products'); // Backend API endpoint
        products.value = response.data; // API'den gelen ürünleri kaydet
    } catch (error) {
        console.error('Ürün verileri alınamadı:', error);
        toast.add({ severity: 'error', summary: 'Hata', detail: 'Ürün verileri alınırken bir hata oluştu.', life: 3000 });
    }
};

// Sayfa yüklendiğinde makineleri ve ürünleri çek
onMounted(async () => {
    await fetchMachines();
    await fetchProducts();
});


const saveProduction = async () => {
    if (production.machine && production.product && production.quantity) {
        try {
            const response = await axios.post('/api/productions/worker', {
                user_id : 4,
                shift_id : 1,
                machine_id: production.machine.id,
                product_id: production.product.id ,
                quantity: production.quantity
            });

            // Başarılı kayıttan sonra gelen yanıtı logla
            console.log('Kaydedilen Üretim:', response.data);

            // Bilgilendirme mesajı gösterme
            toast.add({ severity: 'success', summary: 'Başarılı', detail: 'Üretim kaydedildi.', life: 3000 });

            // Formu sıfırla
            production.machine = null;
            production.product = null;
            production.quantity = null;
        } catch (error) {
            console.error('Üretim kaydedilirken bir hata oluştu:', error);
            // Hata durumunda bilgilendirme mesajı göster
            toast.add({ severity: 'error', summary: 'Hata', detail: 'Üretim kaydedilirken bir hata oluştu.', life: 3000 });
        }
    } else {
        // Eksik bilgi uyarısı
        toast.add({ severity: 'warn', summary: 'Uyarı', detail: 'Lütfen tüm alanları doldurun.', life: 3000 });
    }
};

</script>

<style scoped>
.p-card {
    padding: 2rem;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
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
