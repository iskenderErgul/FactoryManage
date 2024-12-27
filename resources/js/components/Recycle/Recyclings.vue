<template>
    <div>
        <div class="card">
            <Toolbar class="mb-4">
                <template #start>
                    <Button label="Yeni Kayıt Ekle" icon="pi pi-plus" severity="success" class="mr-2" @click="openNew" />
                    <Button label="Sil" icon="pi pi-trash" severity="danger" @click="confirmDeleteSelected" :disabled="!selectedRecyclings || !selectedRecyclings.length" />
                </template>
                <template #end>
                    <FileUpload mode="basic" accept=".csv" :maxFileSize="1000000" label="Import" chooseLabel="Import" class="mr-2 inline-block" />
                    <Button label="Export" icon="pi pi-upload" severity="help" @click="exportCSV" />
                </template>
            </Toolbar>
            <DataTable ref="dt" :value="recyclings" v-model:selection="selectedRecyclings" dataKey="id"
                       :paginator="true" :rows="10" :filters="filters"
                       paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown" :rowsPerPageOptions="[5,10,25]"
                       currentPageReportTemplate="Showing {first} to {last} of {totalRecords} recyclings">
                <template #header>
                    <div class="p-inputgroup">
                        <span class="p-inputgroup-addon"><i class="pi pi-search"></i></span>
                        <InputText v-model="filters['global'].value" placeholder="Search..." />
                    </div>
                </template>
                <Column selectionMode="multiple" style="width: 3rem" :exportable="false"></Column>
                <Column field="id" header="ID" sortable style="min-width:12rem"></Column>
                <Column field="company_name" header="Firma Adı" sortable style="min-width:16rem"></Column>
                <Column field="material_type" header="Ürünün Türü" sortable style="min-width:16rem"></Column>
                <Column field="recycling_date" header="Tarih" sortable style="min-width:16rem"></Column>
                <Column field="recycling_quantity" header="Miktar(KG)" sortable style="min-width:12rem"></Column>
                <Column :exportable="false" style="min-width:8rem">
                    <template #body="slotProps">
                        <Button icon="pi pi-pencil" outlined rounded class="mr-2" @click="editRecycling(slotProps.data)" />
                        <Button icon="pi pi-trash" outlined rounded severity="danger" @click="confirmDeleteRecycling(slotProps.data)" />
                    </template>
                </Column>
            </DataTable>
        </div>
        <Toast ref="toast" />

        <!-- Recycling Details Dialog -->
        <Dialog v-model:visible="recyclingDialog" :style="{width: '450px'}" header="Geri Dönüşüm    " :modal="true" class="p-fluid">
            <div class="field">
                <label for="company_name">Firma Adı</label>
                <InputText id="company_name" v-model.trim="recycling.company_name" required :invalid="submitted && !recycling.company_name" />
                <small class="p-error" v-if="submitted && !recycling.company_name">Company Name is required.</small>
            </div>
            <div class="field">
                <label for="material_type">Ürün Tipi</label>
                <InputText id="material_type" v-model.trim="recycling.material_type" required :invalid="submitted && !recycling.material_type" />
                <small class="p-error" v-if="submitted && !recycling.material_type">Material Type is required.</small>
            </div>
            <div class="field">
                <label for="recycling_date">Tarih</label>
                <Calendar id="recycling_date" v-model="recycling.recycling_date" required :invalid="submitted && !recycling.recycling_date" showIcon />
                <small class="p-error" v-if="submitted && !recycling.recycling_date">Recycling Date is required.</small>
            </div>
            <div class="field">
                <label for="recycling_quantity">Miktar(KG)</label>
                <InputText id="recycling_quantity" v-model.number="recycling.recycling_quantity" required :invalid="submitted && !recycling.recycling_quantity" />
                <small class="p-error" v-if="submitted && !recycling.recycling_quantity">Quantity is required.</small>
            </div>
            <template #footer>
                <Button label="İptal" icon="pi pi-times" text @click="hideDialog" />
                <Button label="Kaydet" icon="pi pi-check" text @click="saveRecycling" />
            </template>
        </Dialog>

        <!-- Delete Recycling Confirmation Dialog -->
        <Dialog v-model:visible="deleteRecyclingDialog" :style="{width: '450px'}" header="Confirm" :modal="true">
            <div class="confirmation-content">
                <i class="pi pi-exclamation-triangle mr-3" style="font-size: 2rem" />
                <span v-if="recycling">Are you sure you want to delete <b>{{recycling.company_name}}</b>?</span>
            </div>
            <template #footer>
                <Button label="No" icon="pi pi-times" text @click="deleteRecyclingDialog = false" />
                <Button label="Yes" icon="pi pi-check" text @click="deleteRecycling" />
            </template>
        </Dialog>

        <!-- Delete Selected Recyclings Confirmation Dialog -->
        <Dialog v-model:visible="deleteRecyclingsDialog" :style="{width: '450px'}" header="Confirm" :modal="true">
            <div class="confirmation-content">
                <i class="pi pi-exclamation-triangle mr-3" style="font-size: 2rem" />
                <span>Are you sure you want to delete the selected recyclings?</span>
            </div>
            <template #footer>
                <Button label="No" icon="pi pi-times" text @click="deleteRecyclingsDialog = false" />
                <Button label="Yes" icon="pi pi-check" text @click="deleteSelectedRecyclings" />
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
import Calendar from "primevue/calendar";

const toast = ref(null);
const recyclings = ref([]);
const recyclingDialog = ref(false);
const deleteRecyclingDialog = ref(false);
const deleteRecyclingsDialog = ref(false);
const recycling = ref({});
const selectedRecyclings = ref([]);
const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
});
const submitted = ref(false);

const fetchRecyclings = () => {
    axios.get('/api/recyclings')
        .then(response => {
            recyclings.value = response.data.reverse()  ;
        })
        .catch(error => {
            console.error("Error fetching recyclings:", error);
        });
};

onMounted(() => {
    fetchRecyclings();
});

const openNew = () => {
    recycling.value = {};
    submitted.value = false;
    recyclingDialog.value = true;
};

const hideDialog = () => {
    recyclingDialog.value = false;
    submitted.value = false;
};

const saveRecycling = () => {
    submitted.value = true;

    if (recycling.value.company_name && recycling.value.material_type && recycling.value.recycling_date && recycling.value.recycling_quantity) {
        if (recycling.value.id) {
            axios.put(`/api/recyclings/${recycling.value.id}`, recycling.value)
                .then(() => {
                    toast.value.add({ severity: 'success', summary: 'Success', detail: 'Recycling updated successfully', life: 3000 });
                    fetchRecyclings();
                });
        } else {
            axios.post('/api/recyclings', recycling.value)
                .then(() => {
                    toast.value.add({ severity: 'success', summary: 'Success', detail: 'Recycling added successfully', life: 3000 });
                    fetchRecyclings();
                });
        }
        recyclingDialog.value = false;
        recycling.value = {};
    } else {
        toast.value.add({ severity: 'error', summary: 'Error', detail: 'All fields are required', life: 3000 });
    }
};

const editRecycling = (recyclingData) => {
    recycling.value = { ...recyclingData };
    recyclingDialog.value = true;
};

const confirmDeleteRecycling = (recyclingData) => {
    recycling.value = recyclingData;
    deleteRecyclingDialog.value = true;
};

const deleteRecycling = () => {
    axios.delete(`/api/recyclings/${recycling.value.id}`)
        .then(() => {
            toast.value.add({ severity: 'success', summary: 'Success', detail: 'Recycling deleted successfully', life: 3000 });
            fetchRecyclings();
            deleteRecyclingDialog.value = false;
        });
};

const confirmDeleteSelected = () => {
    deleteRecyclingsDialog.value = true;
};

const deleteSelectedRecyclings = () => {
    const recyclingIds = selectedRecyclings.value.map(recycling => recycling.id);
    axios.post('/api/recyclings/delete', { ids: recyclingIds })
        .then(() => {
            toast.value.add({ severity: 'success', summary: 'Success', detail: 'Selected recyclings deleted successfully', life: 3000 });
            fetchRecyclings();
            deleteRecyclingsDialog.value = false;
            selectedRecyclings.value = [];
        });
};

const exportCSV = () => {
    // Export functionality implementation
};
</script>
