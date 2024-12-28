<template>
    <div>
        <div class="card">
            <Toolbar class="mb-4">
                <template #start>
                    <Button label="Yeni Müşteri" icon="pi pi-plus" severity="success" class="mr-2" @click="openNew" />
                    <Button label="Sil" icon="pi pi-trash" severity="danger" @click="confirmDeleteSelected" :disabled="!selectedCustomers || !selectedCustomers.length" />
                </template>
                <template #end>
                    <Button label="Dışa Aktar" icon="pi pi-upload" severity="help" @click="exportCSV" />
                </template>
            </Toolbar>
            <DataTable ref="dt" :value="customers" v-model:selection="selectedCustomers" dataKey="id"
                       :paginator="true" :rows="10" :filters="filters"
                       paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown" :rowsPerPageOptions="[5,10,25]"
                       currentPageReportTemplate="Showing {first} to {last} of {totalRecords} customers">
                <template #header>
                    <div class="p-inputgroup">
                        <span class="p-inputgroup-addon"><i class="pi pi-search"></i></span>
                        <InputText v-model="filters['global'].value" placeholder="Ara..." />
                    </div>
                </template>
                <Column selectionMode="multiple" style="width: 2rem" :exportable="false"></Column>
                <Column field="id" header="Müşteri ID" sortable style="min-width:8rem"></Column>
                <Column field="name" header="İsim" sortable style="min-width:10rem"></Column>
                <Column field="email" header="E-posta" sortable style="min-width:12rem"></Column>
                <Column field="phone" header="Telefon" sortable style="min-width:10rem"></Column>
                <Column field="address" header="Adres" sortable style="min-width:10rem"></Column>
                <Column field="debt" header="Borç (₺)" sortable style="min-width:8rem"></Column>
                <Column :exportable="false" style="min-width:8rem">
                    <template #body="slotProps">
                        <Button icon="pi pi-pencil" outlined rounded class="mr-2" @click="editCustomer(slotProps.data)" />
                        <Button icon="pi pi-trash" outlined rounded severity="danger" @click="confirmDeleteCustomer(slotProps.data)" />
                    </template>
                </Column>
            </DataTable>
        </div>
        <Toast ref="toast" />

        <!-- Müşteri Detayları Diyaloğu -->
        <Dialog v-model:visible="customerDialog" :style="{width: '450px'}" header="Müşteri Detayları" :modal="true" class="p-fluid">
            <div class="field">
                <label for="customerName">İsim</label>
                <InputText id="customerName" v-model.trim="customer.name" required :invalid="submitted && !customer.name" />
                <small class="p-error" v-if="submitted && !customer.name">İsim zorunludur.</small>
            </div>
            <div class="field">
                <label for="customerEmail">E-posta</label>
                <InputText id="customerEmail" v-model.trim="customer.email" required :invalid="submitted && !customer.email" />
                <small class="p-error" v-if="submitted && !customer.email">E-posta zorunludur.</small>
            </div>
            <div class="field">
                <label for="customerPhone">Telefon</label>
                <InputText id="customerPhone" v-model.trim="customer.phone" />
            </div>
            <div class="field">
                <label for="customerAddress">Adres</label>
                <InputText id="customerAddress" v-model.trim="customer.address" />
            </div>
            <div class="field">
                <label for="customerDebt">Borç (₺)</label>
                <InputText id="customerDebt" v-model.trim="customer.debt" type="number" required :invalid="submitted && !customer.debt" />
                <small class="p-error" v-if="submitted && !customer.debt">Borç zorunludur.</small>
            </div>
            <template #footer>
                <Button label="İptal" icon="pi pi-times" text @click="hideDialog" />
                <Button label="Kaydet" icon="pi pi-check" text @click="saveCustomer" />
            </template>
        </Dialog>

        <Dialog v-model:visible="deleteCustomerDialog" :style="{width: '450px'}" header="Onayla" :modal="true">
            <div class="confirmation-content">
                <i class="pi pi-exclamation-triangle mr-3" style="font-size: 2rem" />
                <span v-if="customer">Silmek istediğinize emin misiniz <b>{{customer.name}}</b>?</span>
            </div>
            <template #footer>
                <Button label="Hayır" icon="pi pi-times" text @click="deleteCustomerDialog = false" />
                <Button label="Evet" icon="pi pi-check" text @click="deleteCustomer" />
            </template>
        </Dialog>

        <!-- Seçilen Müşterileri Silme Onay Diyaloğu -->
        <Dialog v-model:visible="deleteCustomersDialog" :style="{width: '450px'}" header="Onayla" :modal="true">
            <div class="confirmation-content">
                <i class="pi pi-exclamation-triangle mr-3" style="font-size: 2rem" />
                <span>Seçilen müşterileri silmek istediğinize emin misiniz?</span>
            </div>
            <template #footer>
                <Button label="Hayır" icon="pi pi-times" text @click="deleteCustomersDialog = false" />
                <Button label="Evet" icon="pi pi-check" text @click="deleteSelectedCustomers" />
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
import InputText from 'primevue/inputtext';
import Dialog from 'primevue/dialog';
import Toast from 'primevue/toast';

const toast = ref(null);
const customers = ref([]);
const customerDialog = ref(false);
const deleteCustomerDialog = ref(false);
const deleteCustomersDialog = ref(false);
const customer = ref(); // Varsayılan olarak borç sıfır
const selectedCustomers = ref([]);
const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
});
const submitted = ref(false);

const fetchCustomers = () => {
    axios.get('/api/customers')
        .then(response => {
            customers.value = response.data;
        })
        .catch(error => {
            console.error("Müşterileri getirirken hata:", error);
        });
};

onMounted(() => {
    fetchCustomers();
});

const editCustomer = (customerToEdit) => {
    customer.value = { ...customerToEdit };
    customerDialog.value = true;
    submitted.value = false;
};

const openNew = () => {
    customer.value = { debt: 0 };
    submitted.value = false;
    customerDialog.value = true;
};

const hideDialog = () => {
    customerDialog.value = false;
    submitted.value = false;
};

const saveCustomer = () => {
    submitted.value = true;

    if (customer.value.name && customer.value.email && customer.value.debt >= 0) {
        if (customer.value.id) {
            axios.put(`/api/customers/${customer.value.id}`, customer.value)
                .then(() => {
                    toast.value.add({ severity: 'success', summary: 'Başarılı', detail: 'Müşteri başarıyla güncellendi', life: 3000 });
                    fetchCustomers();
                });
        } else {
            axios.post('/api/customers', customer.value)
                .then(() => {
                    toast.value.add({ severity: 'success', summary: 'Başarılı', detail: 'Müşteri başarıyla eklendi', life: 3000 });
                    fetchCustomers();
                });
        }
        customerDialog.value = false;
        customer.value = {};
    } else {
        toast.value.add({ severity: 'error', summary: 'Hata', detail: 'İsim, e-posta ve borç alanları zorunludur', life: 3000 });
    }
};

const confirmDeleteCustomer = (customerToDelete) => {
    customer.value = { ...customerToDelete };
    deleteCustomerDialog.value = true;
};

const deleteCustomer = () => {
    axios.delete(`/api/customers/${customer.value.id}`)
        .then(() => {
            toast.value.add({ severity: 'success', summary: 'Başarılı', detail: 'Müşteri başarıyla silindi', life: 3000 });
            fetchCustomers();
        });

    deleteCustomerDialog.value = false;
    customer.value = {};
};

const confirmDeleteSelected = () => {
    deleteCustomersDialog.value = true;
};

const deleteSelectedCustomers = () => {
    const ids = selectedCustomers.value.map(customer => customer.id);
    axios.delete('/api/customers', { data: { ids } })
        .then(() => {
            toast.value.add({ severity: 'success', summary: 'Başarılı', detail: 'Seçilen müşteriler başarıyla silindi', life: 3000 });
            fetchCustomers();
        });

    deleteCustomersDialog.value = false;
    selectedCustomers.value = [];
};

const exportCSV = () => {
    // CSV dışa aktarma işlemleri
};
</script>

<style scoped>
.card {
    margin: 2rem 0;
}
</style>
