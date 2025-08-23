<template>
    <div>
        <div class="card">
            <Toolbar class="mb-4">
                <template #start>
                    <Button label="Yeni Tedarikçi" icon="pi pi-plus" severity="success" class="mr-2" @click="openNew" />
                    <Button label="Sil" icon="pi pi-trash" severity="danger" @click="confirmDeleteSelected" :disabled="!selectedSuppliers || !selectedSuppliers.length" />
                </template>
                <template #end>
                    <Button label="Dışa Aktar" icon="pi pi-upload" severity="help" @click="exportCSV" />
                </template>
            </Toolbar>
            <DataTable ref="dt" :value="suppliers" v-model:selection="selectedSuppliers" dataKey="id"
                       :paginator="true" :rows="10" :filters="filters"
                       paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown" :rowsPerPageOptions="[5,10,25]"
                       currentPageReportTemplate="Showing {first} to {last} of {totalRecords} suppliers">
                <template #header>
                    <div class="p-inputgroup">
                        <span class="p-inputgroup-addon"><i class="pi pi-search"></i></span>
                        <InputText v-model="filters['global'].value" placeholder="Ara..." />
                    </div>
                </template>
                <Column selectionMode="multiple" style="width: 2rem" :exportable="false"></Column>
                <Column field="supplier_name" header="Tedarikçi Adı" sortable style="min-width:12rem"></Column>
                <Column field="supplier_email" header="E-posta" sortable style="min-width:12rem"></Column>
                <Column field="supplier_phone" header="Telefon" sortable style="min-width:10rem"></Column>
                <Column field="supplier_address" header="Adres" sortable style="min-width:15rem"></Column>
                <Column field="debt" header="Toplam Borç" sortable style="min-width:8rem">
                    <template #body="slotProps">
                        <span>{{ formatPrice(slotProps.data.debt || 0) }} TL</span>
                    </template>
                </Column>
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
        <Dialog v-model:visible="supplierDialog" :style="{width: '500px'}" header="Tedarikçi Detayları" :modal="true" class="p-fluid">
            <div class="field">
                <label for="supplier_name">Tedarikçi Adı *</label>
                <InputText id="supplier_name" v-model.trim="supplier.supplier_name" required :invalid="submitted && !supplier.supplier_name" />
                <small class="p-error" v-if="submitted && !supplier.supplier_name">Tedarikçi adı zorunludur.</small>
            </div>
            <div class="field">
                <label for="supplier_email">E-posta</label>
                <InputText id="supplier_email" v-model.trim="supplier.supplier_email" />
            </div>
            <div class="field">
                <label for="supplier_phone">Telefon</label>
                <InputText id="supplier_phone" v-model.trim="supplier.supplier_phone" />
            </div>
            <div class="field">
                <label for="supplier_address">Adres</label>
                <Textarea id="supplier_address" v-model="supplier.supplier_address" rows="3" />
            </div>
            <div class="field">
                <label for="debt">Toplam Borç</label>
                <InputNumber id="debt" v-model="supplier.debt" mode="decimal" :minFractionDigits="2" :maxFractionDigits="2" />
            </div>
            <template #footer>
                <Button label="İptal" icon="pi pi-times" text @click="hideDialog" />
                <Button label="Kaydet" icon="pi pi-check" text @click="saveSupplier" />
            </template>
        </Dialog>



        <!-- Tedarikçi Silme Onay Dialog -->
        <Dialog v-model:visible="deleteSupplierDialog" :style="{width: '450px'}" header="Onayla" :modal="true">
            <div class="confirmation-content">
                <i class="pi pi-exclamation-triangle mr-3" style="font-size: 2rem" />
                <span v-if="supplier">Silmek istediğinize emin misiniz <b>{{supplier.supplier_name}}</b>?</span>
            </div>
            <template #footer>
                <Button label="Hayır" icon="pi pi-times" text @click="deleteSupplierDialog = false" />
                <Button label="Evet" icon="pi pi-check" text @click="deleteSupplier" />
            </template>
        </Dialog>

        <!-- Seçilen Tedarikçileri Silme Onay Dialog -->
        <Dialog v-model:visible="deleteSuppliersDialog" :style="{width: '450px'}" header="Onayla" :modal="true">
            <div class="confirmation-content">
                <i class="pi pi-exclamation-triangle mr-3" style="font-size: 2rem" />
                <span>Seçilen tedarikçileri silmek istediğinize emin misiniz?</span>
            </div>
            <template #footer>
                <Button label="Hayır" icon="pi pi-times" text @click="deleteSuppliersDialog = false" />
                <Button label="Evet" icon="pi pi-check" text @click="deleteSelectedSuppliers" />
            </template>
        </Dialog>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';
import { FilterMatchMode } from 'primevue/api';
import Button from 'primevue/button';
import Toolbar from 'primevue/toolbar';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import InputText from 'primevue/inputtext';
import InputNumber from 'primevue/inputnumber';
import Textarea from 'primevue/textarea';
import Dialog from 'primevue/dialog';
import Toast from 'primevue/toast';
import Calendar from 'primevue/calendar';
import Dropdown from 'primevue/dropdown';

const toast = ref(null);
const suppliers = ref([]);
const supplierDialog = ref(false);
const deleteSupplierDialog = ref(false);
const deleteSuppliersDialog = ref(false);
const supplier = ref({});
const selectedSuppliers = ref([]);
const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
});
const submitted = ref(false);

// Tedarikçileri al
const fetchSuppliers = () => {
    axios.get('/api/suppliers')
        .then(response => {
            suppliers.value = response.data;
        })
        .catch(error => {
            console.error("Tedarikçileri getirirken hata:", error);
            toast.value.add({ severity: 'error', summary: 'Hata', detail: 'Tedarikçiler yüklenemedi', life: 3000 });
        });
};

onMounted(() => {
    fetchSuppliers();
});

// Yeni tedarikçi ekleme işlemi
const openNew = () => {
    supplier.value = { debt: 0 };
    submitted.value = false;
    supplierDialog.value = true;
};

// Tedarikçi düzenleme
const editSupplier = (supplierData) => {
    supplier.value = { ...supplierData };
    supplierDialog.value = true;
    submitted.value = false;
};

// Dialog kapatma işlemi
const hideDialog = () => {
    supplierDialog.value = false;
    submitted.value = false;
};

// Tedarikçi kaydetme işlemi
const saveSupplier = () => {
    submitted.value = true;

    if (supplier.value.supplier_name) {
        if (supplier.value.id) {
            axios.put(`/api/suppliers/${supplier.value.id}`, supplier.value)
                .then(() => {
                    toast.value.add({ severity: 'success', summary: 'Başarılı', detail: 'Tedarikçi başarıyla güncellendi', life: 3000 });
                    fetchSuppliers();
                })
                .catch(error => {
                    console.error("Güncelleme hatası:", error);
                    toast.value.add({ severity: 'error', summary: 'Hata', detail: 'Tedarikçi güncellenemedi', life: 3000 });
                });
        } else {
            axios.post('/api/suppliers', supplier.value)
                .then(() => {
                    toast.value.add({ severity: 'success', summary: 'Başarılı', detail: 'Tedarikçi başarıyla eklendi', life: 3000 });
                    fetchSuppliers();
                })
                .catch(error => {
                    console.error("Ekleme hatası:", error);
                    toast.value.add({ severity: 'error', summary: 'Hata', detail: 'Tedarikçi eklenemedi', life: 3000 });
                });
        }
        supplierDialog.value = false;
        supplier.value = {};
    } else {
        toast.value.add({ severity: 'error', summary: 'Hata', detail: 'Tedarikçi adı zorunludur', life: 3000 });
    }
};

// Tedarikçi silme işlemleri
const confirmDeleteSupplier = (supplierData) => {
    supplier.value = { ...supplierData };
    deleteSupplierDialog.value = true;
};

const deleteSupplier = () => {
    axios.delete(`/api/suppliers/${supplier.value.id}`)
        .then(() => {
            toast.value.add({ severity: 'success', summary: 'Başarılı', detail: 'Tedarikçi başarıyla silindi', life: 3000 });
            fetchSuppliers();
        })
        .catch(error => {
            console.error("Silme hatası:", error);
            toast.value.add({ severity: 'error', summary: 'Hata', detail: 'Tedarikçi silinemedi', life: 3000 });
        });

    deleteSupplierDialog.value = false;
    supplier.value = {};
};

const confirmDeleteSelected = () => {
    if (selectedSuppliers.value.length > 0) {
        deleteSuppliersDialog.value = true;
    } else {
        toast.value.add({ severity: 'warn', summary: 'Uyarı', detail: 'Silmek için tedarikçi seçmelisiniz', life: 3000 });
    }
};

const deleteSelectedSuppliers = () => {
    const ids = selectedSuppliers.value.map(supplier => supplier.id);
    axios.post('/api/suppliers/delete', { ids })
        .then(() => {
            toast.value.add({ severity: 'success', summary: 'Başarılı', detail: 'Seçilen tedarikçiler başarıyla silindi', life: 3000 });
            fetchSuppliers();
        })
        .catch(error => {
            console.error("Toplu silme hatası:", error);
            toast.value.add({ severity: 'error', summary: 'Hata', detail: 'Tedarikçiler silinemedi', life: 3000 });
        });

    deleteSuppliersDialog.value = false;
    selectedSuppliers.value = [];
};



// Yardımcı fonksiyonlar

const formatPrice = (price) => {
    return new Intl.NumberFormat('tr-TR').format(price || 0);
};

const exportCSV = () => {
    // CSV dışa aktarma işlemleri
    console.log('CSV Export');
};
</script>

<style scoped>
.card {
    margin: 2rem 0;
}

.confirmation-content {
    display: flex;
    align-items: center;
}
</style>
