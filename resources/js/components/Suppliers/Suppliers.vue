<template>
    <div>
        <div class="card">
            <Toolbar class="mb-4">
                <template #start>
                    <Button label="Yeni Kayıt Ekle" icon="pi pi-plus" severity="success" class="mr-2" @click="openNew" />
                    <Button label="Sil" icon="pi pi-trash" severity="danger" @click="confirmDeleteSelected" :disabled="!selectedSuppliers || !selectedSuppliers.length" />
                </template>
            </Toolbar>
            <DataTable ref="dt" :value="suppliers" v-model:selection="selectedSuppliers" dataKey="id"
                       :paginator="true" :rows="10"
                       paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown" :rowsPerPageOptions="[5,10,25]">

                <Column selectionMode="multiple" style="width: 3rem" :exportable="false"></Column>
                <Column field="id" header="ID" sortable style="min-width:2rem"></Column>
                <Column field="customer.name" header="Müşteri Adı" sortable style="min-width:5rem" :body="customerName"></Column>
                <Column field="supplied_product" header="Ürün" sortable style="min-width:5rem"></Column>
                <Column field="supplied_product_quantity" header="Miktar ( KG )" sortable style="min-width:5rem"></Column>
                <Column field="supplied_product_price" header="Birim Fiyat" sortable style="min-width:5rem"></Column>
                <Column field="calculateTotalPrice" header="Toplam Tutar" sortable style="min-width:5rem">
                    <template #body="slotProps">
                        <span>{{ formatPrice(calculateTotalPrice(slotProps.data)) }} TL</span>
                    </template>
                </Column>
                <Column field="supply_date" header="Tedarik Tarihi" sortable style="min-width:5rem"></Column>
                <Column :exportable="false" style="min-width:8rem">
                    <template #body="slotProps">
                        <Button icon="pi pi-pencil" outlined rounded class="mr-2" @click="editSupplier(slotProps.data)" />
                        <Button icon="pi pi-trash" outlined rounded severity="danger" @click="confirmDeleteSupplier(slotProps.data)" />
                    </template>
                </Column>
            </DataTable>
        </div>
        <Toast ref="toast" />

        <!-- Tedarikçi Ekle/Güncelle Dialog -->
        <Dialog v-model:visible="supplierDialog" :style="{width: '450px'}" header="Tedarikçi" :modal="true" class="p-fluid">
            <div class="field">
                <label for="customer_id">Müşteri</label>
                <Dropdown id="customer_id" v-model="supplier.customer_id" :options="customers" optionLabel="name" placeholder="Müşteri Seçin" required :invalid="submitted && !supplier.customer_id" />
                <small class="p-error" v-if="submitted && !supplier.customer_id">Müşteri seçilmelidir.</small>
            </div>
            <div class="field">
                <label for="supplied_product">Ürün</label>
                <InputText id="supplied_product" v-model.trim="supplier.supplied_product" required :invalid="submitted && !supplier.supplied_product" />
                <small class="p-error" v-if="submitted && !supplier.supplied_product">Ürün gereklidir.</small>
            </div>
            <div class="field">
                <label for="supplied_product_quantity">Miktar(KG)</label>
                <InputText id="supplied_product_quantity" v-model.number="supplier.supplied_product_quantity" required :invalid="submitted && !supplier.supplied_product_quantity" />
                <small class="p-error" v-if="submitted && !supplier.supplied_product_quantity">Miktar gereklidir.</small>
            </div>
            <div class="field">
                <label for="supplied_product_price">Fiyat(Birim)</label>
                <InputText id="supplied_product_price" v-model.number="supplier.supplied_product_price" required :invalid="submitted && !supplier.supplied_product_price" />
                <small class="p-error" v-if="submitted && !supplier.supplied_product_price">Fiyat gereklidir.</small>
            </div>
            <div class="field">
                <label for="supply_date">Tedarik Tarihi</label>
                <Calendar id="supply_date" v-model="supplier.supply_date" required :invalid="submitted && !supplier.supply_date" showIcon />
                <small class="p-error" v-if="submitted && !supplier.supply_date">Tedarik Tarihi gereklidir.</small>
            </div>
            <template #footer>
                <Button label="İptal" icon="pi pi-times" text @click="hideDialog" />
                <Button label="Kaydet" icon="pi pi-check" text @click="saveSupplier" />
            </template>
        </Dialog>

        <!-- Tedarikçi Silme Onay Dialog -->
        <Dialog v-model:visible="deleteSupplierDialog" :style="{width: '450px'}" header="Tedarikçi Sil" :modal="true" class="p-fluid">
            <p>Bu tedarikçiyi silmek istediğinize emin misiniz?</p>
            <template #footer>
                <Button label="İptal" icon="pi pi-times" text @click="hideDeleteDialog" />
                <Button label="Sil" icon="pi pi-check" severity="danger" @click="deleteSupplier" />
            </template>
        </Dialog>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import Button from 'primevue/button';
import Toolbar from 'primevue/toolbar';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import FileUpload from 'primevue/fileupload';
import InputText from 'primevue/inputtext';
import Dialog from 'primevue/dialog';
import Toast from 'primevue/toast';
import Calendar from 'primevue/calendar';
import Dropdown from 'primevue/dropdown';

const toast = ref(null);
const suppliers = ref([]);
const customers = ref([]);
const supplierDialog = ref(false);
const deleteSupplierDialog = ref(false);
const supplier = ref({});
const selectedSuppliers = ref([]);

const submitted = ref(false);

// Müşterileri al
const fetchCustomers = () => {
    axios.get('/api/customers')
        .then(response => {
            customers.value = response.data;
        })
        .catch(error => {
            console.error("Error fetching customers:", error);
        });
};

// Tedarikçileri al
const fetchSuppliers = () => {
    axios.get('/api/suppliers')
        .then(response => {
            suppliers.value = response.data.reverse();
        })
        .catch(error => {
            console.error("Error fetching suppliers:", error);
        });
};

onMounted(() => {
    fetchCustomers();
    fetchSuppliers();
});

// Yeni tedarikçi ekleme işlemi
const openNew = () => {
    supplier.value = {};
    submitted.value = false;
    supplierDialog.value = true;
};

// Formu kapatma işlemi
const hideDialog = () => {
    supplierDialog.value = false;
    submitted.value = false;
};

// Tedarikçi kaydetme işlemi
const saveSupplier = () => {
    submitted.value = true;
    if (supplier.value.customer_id && supplier.value.supplied_product && supplier.value.supplied_product_quantity &&
        supplier.value.supplied_product_price && supplier.value.supply_date) {
        if (supplier.value.id) {
            axios.put(`/api/suppliers/${supplier.value.id}`, supplier.value)
                .then(() => {
                    toast.value.add({ severity: 'success', summary: 'Başarılı', detail: 'Tedarikçi başarıyla güncellendi', life: 3000 });
                    fetchSuppliers();
                });
        } else {
            axios.post('/api/suppliers', supplier.value)
                .then(() => {
                    toast.value.add({ severity: 'success', summary: 'Başarılı', detail: 'Tedarikçi başarıyla eklendi', life: 3000 });
                    fetchSuppliers();
                });
        }
        supplierDialog.value = false;
        supplier.value = {};
    } else {
        toast.value.add({ severity: 'error', summary: 'Hata', detail: 'Tüm alanlar zorunludur', life: 3000 });
    }
};

const editSupplier = (supplierData) => {
    supplier.value = { ...supplierData };
    supplierDialog.value = true;
};

// Müşteri adı gösterme fonksiyonu
const customerName = (data) => {
    return data.customer ? data.customer.name : '';
};

// Tedarikçi silme işlemi
const confirmDeleteSupplier = (supplierData) => {
    supplier.value = supplierData;
    deleteSupplierDialog.value = true;
};

const confirmDeleteSelected = () => {
    if (selectedSuppliers.value.length > 0) {
        deleteSupplierDialog.value = true;
    } else {
        toast.value.add({ severity: 'warn', summary: 'Uyarı', detail: 'Silmek için bir tedarikçi seçmelisiniz', life: 3000 });
    }
};

const hideDeleteDialog = () => {
    deleteSupplierDialog.value = false;
};

const deleteSupplier = () => {
    axios.delete(`/api/suppliers/${supplier.value.id}`)
        .then(() => {
            toast.value.add({ severity: 'success', summary: 'Başarılı', detail: 'Tedarikçi başarıyla silindi', life: 3000 });
            fetchSuppliers();
            deleteSupplierDialog.value = false;
        })
        .catch(error => {
            console.error("Error deleting supplier:", error);
            toast.value.add({ severity: 'error', summary: 'Hata', detail: 'Tedarikçi silinemedi', life: 3000 });
        });
};

// Toplam fiyat hesaplama fonksiyonu
const calculateTotalPrice = (data) => {
    if (data.supplied_product_quantity && data.supplied_product_price) {
        return parseFloat((data.supplied_product_quantity * data.supplied_product_price)).toFixed(2);
    }
    return '0.00'; // Eğer miktar veya fiyat yoksa 0 döndürülür
};

const formatPrice = (price) => {
    return new Intl.NumberFormat('tr-TR').format(price); // Türkçe formatta binlik ayırıcı kullanır
};
</script>
