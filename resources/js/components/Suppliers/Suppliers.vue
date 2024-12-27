<template>
    <div>
        <div class="card">
            <Toolbar class="mb-4">
                <template #start>
                    <Button label="Yeni Kayıt Ekle" icon="pi pi-plus" severity="success" class="mr-2" @click="openNew" />
                    <Button label="Sil" icon="pi pi-trash" severity="danger" @click="confirmDeleteSelected" :disabled="!selectedSuppliers || !selectedSuppliers.length" />
                </template>
                <template #end>
                    <FileUpload mode="basic" accept=".csv" :maxFileSize="1000000" label="Import" chooseLabel="Import" class="mr-2 inline-block" />
                    <Button label="Export" icon="pi pi-upload" severity="help" @click="exportCSV" />
                </template>
            </Toolbar>
            <DataTable ref="dt" :value="suppliers" v-model:selection="selectedSuppliers" dataKey="id"
                       :paginator="true" :rows="10" :filters="filters"
                       paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown" :rowsPerPageOptions="[5,10,25]"
                       currentPageReportTemplate="Showing {first} to {last} of {totalRecords} suppliers">
                <template #header>
                    <div class="p-inputgroup">
                        <span class="p-inputgroup-addon"><i class="pi pi-search"></i></span>
                        <InputText v-model="filters['global'].value" placeholder="Search..." />
                    </div>
                </template>
                <Column selectionMode="multiple" style="width: 3rem" :exportable="false"></Column>
                <Column field="id" header="ID" sortable style="min-width:12rem"></Column>
                <Column field="suppliers_name" header="Firma Adı" sortable style="min-width:16rem"></Column>
                <Column field="suppliers_address" header="Adres" sortable style="min-width:16rem"></Column>
                <Column field="supplied_product" header="Ürün" sortable style="min-width:16rem"></Column>
                <Column field="supplied_product_quantity" header="Miktar" sortable style="min-width:12rem"></Column>
                <Column field="supplied_product_price" header="Fiyat" sortable style="min-width:12rem"></Column>
                <Column field="supply_date" header="Tedarik Tarihi" sortable style="min-width:16rem"></Column>
                <Column :exportable="false" style="min-width:8rem">
                    <template #body="slotProps">
                        <Button icon="pi pi-pencil" outlined rounded class="mr-2" @click="editSupplier(slotProps.data)" />
                        <Button icon="pi pi-trash" outlined rounded severity="danger" @click="confirmDeleteSupplier(slotProps.data)" />
                    </template>
                </Column>
            </DataTable>
        </div>
        <Toast ref="toast" />

        <!-- Supplier Details Dialog -->
        <Dialog v-model:visible="supplierDialog" :style="{width: '450px'}" header="Tedarikçi" :modal="true" class="p-fluid">
            <div class="field">
                <label for="suppliers_name">Firma Adı</label>
                <InputText id="suppliers_name" v-model.trim="supplier.suppliers_name" required :invalid="submitted && !supplier.suppliers_name" />
                <small class="p-error" v-if="submitted && !supplier.suppliers_name">Firma Adı gereklidir.</small>
            </div>
            <div class="field">
                <label for="suppliers_address">Adres</label>
                <InputText id="suppliers_address" v-model.trim="supplier.suppliers_address" required :invalid="submitted && !supplier.suppliers_address" />
                <small class="p-error" v-if="submitted && !supplier.suppliers_address">Adres gereklidir.</small>
            </div>
            <div class="field">
                <label for="supplied_product">Ürün</label>
                <InputText id="supplied_product" v-model.trim="supplier.supplied_product" required :invalid="submitted && !supplier.supplied_product" />
                <small class="p-error" v-if="submitted && !supplier.supplied_product">Ürün gereklidir.</small>
            </div>
            <div class="field">
                <label for="supplied_product_quantity">Miktar</label>
                <InputText id="supplied_product_quantity" v-model.number="supplier.supplied_product_quantity" required :invalid="submitted && !supplier.supplied_product_quantity" />
                <small class="p-error" v-if="submitted && !supplier.supplied_product_quantity">Miktar gereklidir.</small>
            </div>
            <div class="field">
                <label for="supplied_product_price">Fiyat</label>
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

        <!-- Delete Supplier Confirmation Dialog -->
        <Dialog v-model:visible="deleteSupplierDialog" :style="{width: '450px'}" header="Onay" :modal="true">
            <div class="confirmation-content">
                <i class="pi pi-exclamation-triangle mr-3" style="font-size: 2rem" />
                <span v-if="supplier">Tedarikçi <b>{{supplier.suppliers_name}}</b> silinecek. Emin misiniz?</span>
            </div>
            <template #footer>
                <Button label="Hayır" icon="pi pi-times" text @click="deleteSupplierDialog = false" />
                <Button label="Evet" icon="pi pi-check" text @click="deleteSupplier" />
            </template>
        </Dialog>

        <!-- Delete Selected Suppliers Confirmation Dialog -->
        <Dialog v-model:visible="deleteSuppliersDialog" :style="{width: '450px'}" header="Onay" :modal="true">
            <div class="confirmation-content">
                <i class="pi pi-exclamation-triangle mr-3" style="font-size: 2rem" />
                <span>Seçilen tedarikçiler silinecek. Emin misiniz?</span>
            </div>
            <template #footer>
                <Button label="Hayır" icon="pi pi-times" text @click="deleteSuppliersDialog = false" />
                <Button label="Evet" icon="pi pi-check" text @click="deleteSelectedSuppliers" />
            </template>
        </Dialog>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { FilterMatchMode } from 'primevue/api';
import Button from 'primevue/button';
import Toolbar from 'primevue/toolbar';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import FileUpload from 'primevue/fileupload';
import InputText from 'primevue/inputtext';
import Dialog from 'primevue/dialog';
import Toast from 'primevue/toast';
import Calendar from 'primevue/calendar';

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
    fetchSuppliers();
});

const openNew = () => {
    supplier.value = {};
    submitted.value = false;
    supplierDialog.value = true;
};

const hideDialog = () => {
    supplierDialog.value = false;
    submitted.value = false;
};

const saveSupplier = () => {
    submitted.value = true;

    if (
        supplier.value.suppliers_name &&
        supplier.value.suppliers_address &&
        supplier.value.supplied_product &&
        supplier.value.supplied_product_quantity &&
        supplier.value.supplied_product_price &&
        supplier.value.supply_date) {
        if (supplier.value.id) {
            axios.put(`/api/suppliers/${supplier.value.id}`, supplier.value)
                .then(() => {
                    toast.value.add({ severity: 'success', summary: 'Success', detail: 'Supplier updated successfully', life: 3000 });
                    fetchSuppliers();
                });
        } else {
            axios.post('/api/suppliers', supplier.value)
                .then(() => {
                    toast.value.add({ severity: 'success', summary: 'Success', detail: 'Supplier added successfully', life: 3000 });
                    fetchSuppliers();
                });
        }
        supplierDialog.value = false;
        supplier.value = {};
    } else {
        toast.value.add({ severity: 'error', summary: 'Error', detail: 'All fields are required', life: 3000 });
    }
};

const editSupplier = (supplierData) => {
    supplier.value = { ...supplierData };
    supplierDialog.value = true;
};

const confirmDeleteSupplier = (supplierData) => {
    supplier.value = supplierData;
    deleteSupplierDialog.value = true;
};

const deleteSupplier = () => {
    axios.delete(`/api/suppliers/${supplier.value.id}`)
        .then(() => {
            toast.value.add({ severity: 'success', summary: 'Success', detail: 'Supplier deleted successfully', life: 3000 });
            fetchSuppliers();
            deleteSupplierDialog.value = false;
        });
};

const confirmDeleteSelected = () => {
    deleteSuppliersDialog.value = true;
};

const deleteSelectedSuppliers = () => {
    const supplierIds = selectedSuppliers.value.map(supplier => supplier.id);
    axios.post('/api/suppliers/delete', { ids: supplierIds })
        .then(() => {
            toast.value.add({ severity: 'success', summary: 'Success', detail: 'Selected suppliers deleted successfully', life: 3000 });
            fetchSuppliers();
            deleteSuppliersDialog.value = false;
            selectedSuppliers.value = [];
        });
};

const exportCSV = () => {
    // Export functionality implementation
};
</script>
