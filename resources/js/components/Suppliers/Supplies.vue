<template>
    <div>
        <div class="card">
            <Toolbar class="mb-4">
                <template #start>
                    <Button label="Yeni Tedarik" icon="pi pi-plus" severity="success" class="mr-2" @click="openNew" />
                    <Button label="Sil" icon="pi pi-trash" severity="danger" @click="confirmDeleteSelected" :disabled="!selectedSupplies || !selectedSupplies.length" />
                </template>
                <template #end>
                    <Button label="Dışa Aktar" icon="pi pi-upload" severity="help" @click="exportCSV" />
                </template>
            </Toolbar>
            <DataTable ref="dt" :value="supplies" v-model:selection="selectedSupplies" dataKey="id"
                       :paginator="true" :rows="10" :filters="filters"
                       paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown" :rowsPerPageOptions="[5,10,25]"
                       currentPageReportTemplate="Showing {first} to {last} of {totalRecords} supplies">
                <template #header>
                    <div class="p-inputgroup">
                        <span class="p-inputgroup-addon"><i class="pi pi-search"></i></span>
                        <InputText v-model="filters['global'].value" placeholder="Ara..." />
                    </div>
                </template>
                <Column selectionMode="multiple" style="width: 2rem" :exportable="false"></Column>
                <Column field="supplier.supplier_name" header="Tedarikçi" sortable style="min-width:10rem">
                    <template #body="slotProps">
                        <span>{{ slotProps.data.supplier?.supplier_name || '-' }}</span>
                    </template>
                </Column>
                <Column field="supplied_product" header="Ürün" sortable style="min-width:8rem"></Column>
                <Column field="supplied_product_quantity" header="Miktar (KG)" sortable style="min-width:6rem"></Column>
                <Column field="supplied_product_price" header="Birim Fiyat" sortable style="min-width:6rem">
                    <template #body="slotProps">
                        <span>{{ formatPrice(slotProps.data.supplied_product_price) }} TL</span>
                    </template>
                </Column>
                <Column field="calculateTotalPrice" header="Toplam Tutar" sortable style="min-width:8rem">
                    <template #body="slotProps">
                        <span>{{ formatPrice(calculateTotalPrice(slotProps.data)) }} TL</span>
                    </template>
                </Column>
                <Column field="supply_date" header="Tedarik Tarihi" sortable style="min-width:8rem"></Column>
                <Column field="payment_method" header="Ödeme Yöntemi" sortable style="min-width:8rem">
                    <template #body="slotProps">
                        <span class="p-tag" :class="getPaymentMethodClass(slotProps.data.payment_method)">
                            {{ getPaymentMethodLabel(slotProps.data.payment_method) }}
                        </span>
                    </template>
                </Column>
                <Column :exportable="false" style="min-width:8rem">
                    <template #body="slotProps">
                        <Button icon="pi pi-pencil" outlined rounded class="mr-2" @click="editSupply(slotProps.data)" />
                        <Button icon="pi pi-trash" outlined rounded severity="danger" @click="confirmDeleteSupply(slotProps.data)" />
                    </template>
                </Column>
            </DataTable>
        </div>
        <Toast ref="toast" />

        <!-- Tedarik Ekle/Güncelle Dialog -->
        <Dialog v-model:visible="supplyDialog" :style="{width: '600px'}" header="Tedarik Detayları" :modal="true" class="p-fluid">
            <div class="grid">
                <div class="col-12">
                    <div class="field">
                        <label for="supplier_id">Tedarikçi *</label>
                        <Dropdown id="supplier_id" v-model="supply.supplier_id" :options="suppliers" optionLabel="supplier_name" optionValue="id" placeholder="Tedarikçi Seçin" required :invalid="submitted && !supply.supplier_id" />
                        <small class="p-error" v-if="submitted && !supply.supplier_id">Tedarikçi seçilmelidir.</small>
                    </div>
                </div>
                <div class="col-12 md:col-6">
                    <div class="field">
                        <label for="supplied_product">Ürün *</label>
                        <InputText id="supplied_product" v-model.trim="supply.supplied_product" required :invalid="submitted && !supply.supplied_product" />
                        <small class="p-error" v-if="submitted && !supply.supplied_product">Ürün gereklidir.</small>
                    </div>
                </div>
                <div class="col-12 md:col-6">
                    <div class="field">
                        <label for="supplied_product_quantity">Miktar (KG) *</label>
                        <InputNumber id="supplied_product_quantity" v-model="supply.supplied_product_quantity" required :invalid="submitted && !supply.supplied_product_quantity" />
                        <small class="p-error" v-if="submitted && !supply.supplied_product_quantity">Miktar gereklidir.</small>
                    </div>
                </div>
                <div class="col-12 md:col-6">
                    <div class="field">
                        <label for="supplied_product_price">Birim Fiyat *</label>
                        <InputNumber id="supplied_product_price" v-model="supply.supplied_product_price" mode="decimal" :minFractionDigits="2" :maxFractionDigits="2" required :invalid="submitted && !supply.supplied_product_price" />
                        <small class="p-error" v-if="submitted && !supply.supplied_product_price">Fiyat gereklidir.</small>
                    </div>
                </div>
                <div class="col-12 md:col-6">
                    <div class="field">
                        <label for="supply_date">Tedarik Tarihi *</label>
                        <Calendar id="supply_date" v-model="supply.supply_date" required :invalid="submitted && !supply.supply_date" showIcon dateFormat="yy-mm-dd" />
                        <small class="p-error" v-if="submitted && !supply.supply_date">Tedarik Tarihi gereklidir.</small>
                    </div>
                </div>
                <div class="col-12 md:col-6">
                    <div class="field">
                        <label for="payment_method">Ödeme Yöntemi *</label>
                        <Dropdown id="payment_method" v-model="supply.payment_method" :options="paymentMethods" optionLabel="label" optionValue="value" placeholder="Ödeme Yöntemi Seçin" required :invalid="submitted && !supply.payment_method" />
                        <small class="p-error" v-if="submitted && !supply.payment_method">Ödeme yöntemi seçilmelidir.</small>
                    </div>
                </div>
                <div class="col-12 md:col-6" v-if="supply.payment_method === 'kısmi'">
                    <div class="field">
                        <label for="paid_amount">Ödenen Miktar *</label>
                        <InputNumber id="paid_amount" v-model="supply.paid_amount" mode="decimal" :minFractionDigits="2" :maxFractionDigits="2" :invalid="submitted && supply.payment_method === 'kısmi' && !supply.paid_amount" />
                        <small class="p-error" v-if="submitted && supply.payment_method === 'kısmi' && !supply.paid_amount">Ödenen miktar gereklidir.</small>
                    </div>
                </div>
                <div class="col-12" v-if="supply.supplied_product_quantity && supply.supplied_product_price">
                    <div class="field">
                        <label>Toplam Tutar</label>
                        <div class="p-3 surface-100 border-round">
                            <strong>{{ formatPrice(supply.supplied_product_quantity * supply.supplied_product_price) }} TL</strong>
                        </div>
                    </div>
                </div>
            </div>
            <template #footer>
                <Button label="İptal" icon="pi pi-times" text @click="hideDialog" />
                <Button label="Kaydet" icon="pi pi-check" text @click="saveSupply" />
            </template>
        </Dialog>

        <!-- Tedarik Silme Onay Dialog -->
        <Dialog v-model:visible="deleteSupplyDialog" :style="{width: '450px'}" header="Onayla" :modal="true">
            <div class="confirmation-content">
                <i class="pi pi-exclamation-triangle mr-3" style="font-size: 2rem" />
                <span v-if="supply">Bu tedarik kaydını silmek istediğinize emin misiniz?</span>
            </div>
            <template #footer>
                <Button label="Hayır" icon="pi pi-times" text @click="deleteSupplyDialog = false" />
                <Button label="Evet" icon="pi pi-check" text @click="deleteSupply" />
            </template>
        </Dialog>

        <!-- Seçilen Tedarikleri Silme Onay Dialog -->
        <Dialog v-model:visible="deleteSuppliesDialog" :style="{width: '450px'}" header="Onayla" :modal="true">
            <div class="confirmation-content">
                <i class="pi pi-exclamation-triangle mr-3" style="font-size: 2rem" />
                <span>Seçilen tedarik kayıtlarını silmek istediğinize emin misiniz?</span>
            </div>
            <template #footer>
                <Button label="Hayır" icon="pi pi-times" text @click="deleteSuppliesDialog = false" />
                <Button label="Evet" icon="pi pi-check" text @click="deleteSelectedSupplies" />
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
import Dialog from 'primevue/dialog';
import Toast from 'primevue/toast';
import Calendar from 'primevue/calendar';
import Dropdown from 'primevue/dropdown';

const toast = ref(null);
const supplies = ref([]);
const suppliers = ref([]);
const supplyDialog = ref(false);
const deleteSupplyDialog = ref(false);
const deleteSuppliesDialog = ref(false);
const supply = ref({});
const selectedSupplies = ref([]);
const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
});
const submitted = ref(false);

// Ödeme yöntemi seçenekleri
const paymentMethods = ref([
    { label: 'Peşin', value: 'peşin' },
    { label: 'Borç', value: 'borç' },
    { label: 'Kısmi Ödeme', value: 'kısmi' }
]);

// Tedarikleri al
const fetchSupplies = () => {
    axios.get('/api/supplies')
        .then(response => {
            supplies.value = response.data;
        })
        .catch(error => {
            console.error("Tedarikleri getirirken hata:", error);
            toast.value.add({ severity: 'error', summary: 'Hata', detail: 'Tedarikler yüklenemedi', life: 3000 });
        });
};

// Tedarikçileri al
const fetchSuppliers = () => {
    axios.get('/api/suppliers')
        .then(response => {
            suppliers.value = response.data;
        })
        .catch(error => {
            console.error("Tedarikçileri getirirken hata:", error);
        });
};

onMounted(() => {
    fetchSupplies();
    fetchSuppliers();
});

// Yeni tedarik ekleme işlemi
const openNew = () => {
    supply.value = { payment_method: 'borç' }; // Varsayılan olarak borç seçili
    submitted.value = false;
    supplyDialog.value = true;
};

// Tedarik düzenleme
const editSupply = (supplyData) => {
    supply.value = { ...supplyData };
    // Tarih formatını düzelt
    if (supply.value.supply_date) {
        supply.value.supply_date = new Date(supply.value.supply_date);
    }
    supplyDialog.value = true;
    submitted.value = false;
};

// Dialog kapatma işlemi
const hideDialog = () => {
    supplyDialog.value = false;
    submitted.value = false;
};

// Tedarik kaydetme işlemi
const saveSupply = () => {
    submitted.value = true;

    // Validation kontrolü
    const isValidPayment = supply.value.payment_method !== 'kısmi' || supply.value.paid_amount;
    
    if (supply.value.supplier_id && supply.value.supplied_product && 
        supply.value.supplied_product_quantity && supply.value.supplied_product_price && 
        supply.value.supply_date && supply.value.payment_method && isValidPayment) {
        
        // Tarih formatını API için düzenle (timezone sorununu önlemek için)
        const supplyData = { ...supply.value };
        if (supplyData.supply_date instanceof Date) {
            // Timezone sorununu önlemek için local tarih formatını kullan
            const year = supplyData.supply_date.getFullYear();
            const month = String(supplyData.supply_date.getMonth() + 1).padStart(2, '0');
            const day = String(supplyData.supply_date.getDate()).padStart(2, '0');
            supplyData.supply_date = `${year}-${month}-${day}`;
        }

        if (supply.value.id) {
            axios.put(`/api/supplies/${supply.value.id}`, supplyData)
                .then(() => {
                    toast.value.add({ severity: 'success', summary: 'Başarılı', detail: 'Tedarik başarıyla güncellendi', life: 3000 });
                    fetchSupplies();
                })
                .catch(error => {
                    console.error("Güncelleme hatası:", error);
                    toast.value.add({ severity: 'error', summary: 'Hata', detail: 'Tedarik güncellenemedi', life: 3000 });
                });
        } else {
            axios.post('/api/supplies', supplyData)
                .then(() => {
                    toast.value.add({ severity: 'success', summary: 'Başarılı', detail: 'Tedarik başarıyla eklendi', life: 3000 });
                    fetchSupplies();
                })
                .catch(error => {
                    console.error("Ekleme hatası:", error);
                    toast.value.add({ severity: 'error', summary: 'Hata', detail: 'Tedarik eklenemedi', life: 3000 });
                });
        }
        supplyDialog.value = false;
        supply.value = {};
    } else {
        toast.value.add({ severity: 'error', summary: 'Hata', detail: 'Zorunlu alanlar doldurulmalıdır', life: 3000 });
    }
};

// Tedarik silme işlemleri
const confirmDeleteSupply = (supplyData) => {
    supply.value = { ...supplyData };
    deleteSupplyDialog.value = true;
};

const deleteSupply = () => {
    axios.delete(`/api/supplies/${supply.value.id}`)
        .then(() => {
            toast.value.add({ severity: 'success', summary: 'Başarılı', detail: 'Tedarik başarıyla silindi', life: 3000 });
            fetchSupplies();
        })
        .catch(error => {
            console.error("Silme hatası:", error);
            toast.value.add({ severity: 'error', summary: 'Hata', detail: 'Tedarik silinemedi', life: 3000 });
        });

    deleteSupplyDialog.value = false;
    supply.value = {};
};

const confirmDeleteSelected = () => {
    if (selectedSupplies.value.length > 0) {
        deleteSuppliesDialog.value = true;
    } else {
        toast.value.add({ severity: 'warn', summary: 'Uyarı', detail: 'Silmek için tedarik seçmelisiniz', life: 3000 });
    }
};

const deleteSelectedSupplies = () => {
    const ids = selectedSupplies.value.map(supply => supply.id);
    axios.post('/api/supplies/delete', { ids })
        .then(() => {
            toast.value.add({ severity: 'success', summary: 'Başarılı', detail: 'Seçilen tedarikler başarıyla silindi', life: 3000 });
            fetchSupplies();
        })
        .catch(error => {
            console.error("Toplu silme hatası:", error);
            toast.value.add({ severity: 'error', summary: 'Hata', detail: 'Tedarikler silinemedi', life: 3000 });
        });

    deleteSuppliesDialog.value = false;
    selectedSupplies.value = [];
};

// Yardımcı fonksiyonlar
const calculateTotalPrice = (data) => {
    if (data.supplied_product_quantity && data.supplied_product_price) {
        return parseFloat((data.supplied_product_quantity * data.supplied_product_price)).toFixed(2);
    }
    return '0.00';
};

const formatPrice = (price) => {
    return new Intl.NumberFormat('tr-TR').format(price || 0);
};

// Ödeme yöntemi label ve class fonksiyonları
const getPaymentMethodLabel = (method) => {
    const labels = {
        'peşin': 'Peşin',
        'borç': 'Borç',
        'kısmi': 'Kısmi'
    };
    return labels[method] || method;
};

const getPaymentMethodClass = (method) => {
    const classes = {
        'peşin': 'p-tag-success',
        'borç': 'p-tag-danger', 
        'kısmi': 'p-tag-warning'
    };
    return classes[method] || '';
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
