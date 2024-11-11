<template>
    <div>
        <div class="card">
            <Toolbar class="mb-4">
                <template #start>
                    <Button label="Yeni Şablon" icon="pi pi-plus" severity="success" class="mr-2" @click="openNew" />
                    <Button label="Sil" icon="pi pi-trash" severity="danger" @click="confirmDeleteSelected" :disabled="!selectedShifts || !selectedShifts.length" />
                </template>

                <template #end>
                    <Button label="Dışa Aktar" icon="pi pi-upload" severity="help" @click="exportCSV($event)" />
                </template>
            </Toolbar>

            <DataTable ref="dt" :value="shifts" v-model:selection="selectedShifts" dataKey="id"
                       :paginator="true" :rows="10" :filters="filters"
                       paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown" :rowsPerPageOptions="[5,10,25]"
                       currentPageReportTemplate="Mevcut {first} ile {last} arasında {totalRecords} şablon">

                <template #header>
                    <div class="p-inputgroup">
                        <span class="p-inputgroup-addon">
                            <i class="pi pi-search"></i>
                        </span>
                        <InputText v-model="filters['global'].value" placeholder="Ara..." />
                    </div>
                </template>

                <Column selectionMode="multiple" style="width: 3rem" :exportable="false"></Column>

                <Column field="id" header="ID" sortable style="min-width:12rem"></Column>
                <Column field="name" header="Ad" sortable style="min-width:16rem"></Column>
                <Column field="start_time" header="Başlangıç Saati" sortable style="min-width:16rem"></Column>
                <Column field="end_time" header="Bitiş Saati" sortable style="min-width:16rem"></Column>
                <Column field="duration" header="Süre (dk)" sortable style="min-width:16rem"></Column>

                <Column :exportable="false" style="min-width:8rem">
                    <template #body="slotProps">
                        <Button icon="pi pi-pencil" outlined rounded class="mr-2" @click="editShift(slotProps.data)" />
                        <Button icon="pi pi-trash" outlined rounded severity="danger" @click="confirmDeleteShift(slotProps.data)" />
                    </template>
                </Column>
            </DataTable>
        </div>
        <Toast ref="toast" />

        <Dialog v-model:visible="shiftDialog" :style="{width: '450px'}" header="Şablon Detayları" :modal="true" class="p-fluid">
            <div class="field">
                <label for="name">Ad</label>
                <InputText id="name" v-model.trim="shift.name" required="true" autofocus :invalid="submitted && !shift.name" />
                <small class="p-error" v-if="submitted && !shift.name">Ad gerekli.</small>
            </div>
            <div class="field">
                <label for="start_time">Başlangıç Saati</label>
                <InputText id="start_time" v-model.trim="shift.start_time" required="true" placeholder="HH:MM:SS" :invalid="submitted && !shift.start_time" />
                <small class="p-error" v-if="submitted && !shift.start_time">Başlangıç saati gerekli.</small>
            </div>
            <div class="field">
                <label for="end_time">Bitiş Saati</label>
                <InputText id="end_time" v-model.trim="shift.end_time" required="true" placeholder="HH:MM:SS" :invalid="submitted && !shift.end_time" />
                <small class="p-error" v-if="submitted && !shift.end_time">Bitiş saati gerekli.</small>
            </div>

            <template #footer>
                <Button label="İptal" icon="pi pi-times" text @click="hideDialog"/>
                <Button label="Kaydet" icon="pi pi-check" text @click="saveShift" />
            </template>
        </Dialog>

        <Dialog v-model:visible="deleteShiftDialog" :style="{width: '450px'}" header="Onay" :modal="true">
            <div class="confirmation-content">
                <i class="pi pi-exclamation-triangle mr-3" style="font-size: 2rem" />
                <span v-if="shift">Bu şablonu silmek istediğinizden emin misiniz? <b>{{shift.name}}</b>?</span>
            </div>
            <template #footer>
                <Button label="Hayır" icon="pi pi-times" text @click="deleteShiftDialog = false"/>
                <Button label="Evet" icon="pi pi-check" text @click="deleteShift" />
            </template>
        </Dialog>

        <Dialog v-model:visible="deleteShiftsDialog" :style="{width: '450px'}" header="Onay" :modal="true">
            <div class="confirmation-content">
                <i class="pi pi-exclamation-triangle mr-3" style="font-size: 2rem" />
                <span v-if="shift">Seçili şablonları silmek istediğinizden emin misiniz?</span>
            </div>
            <template #footer>
                <Button label="Hayır" icon="pi pi-times" text @click="deleteShiftsDialog = false"/>
                <Button label="Evet" icon="pi pi-check" text @click="deleteSelectedShifts" />
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
import InputText from 'primevue/inputtext';
import Dialog from 'primevue/dialog';
import Toast from 'primevue/toast';
import { FilterMatchMode } from 'primevue/api';

const toast = ref(null);
const dt = ref();
const shifts = ref([]);
const shiftDialog = ref(false);
const deleteShiftDialog = ref(false);
const deleteShiftsDialog = ref(false);
const shift = ref({});
const selectedShifts = ref();
const filters = ref({
    'global': { value: null, matchMode: FilterMatchMode.CONTAINS },
});
const submitted = ref(false);

onMounted(async () => {
    await fetchShifts();
});

const fetchShifts = async () => {
    try {
        const response = await axios.get('/api/shift/shift-templates');
        shifts.value = response.data;
    } catch (error) {
        console.error(error);
        toast.value.add({ severity: 'error', summary: 'Hata', detail: 'Şablonlar yüklenemedi', life: 3000 });
    }
};

const openNew = () => {
    shift.value = {};
    submitted.value = false;
    shiftDialog.value = true;
};
const hideDialog = () => {
    shiftDialog.value = false;
    submitted.value = false;
};
const saveShift = async () => {
    submitted.value = true;

    if (shift.value.name && shift.value.name.trim()) {
        try {
            if (shift.value.id) {
                await axios.put(`/api/shift/shift-templates/${shift.value.id}`, shift.value);
            } else {
                console.log(shift.value)
                await axios.post('/api/shift/shift-templates', shift.value);
            }
            await fetchShifts();
            shiftDialog.value = false;
            shift.value = {};
            toast.value.add({ severity: 'success', summary: 'Başarılı', detail: 'Şablon başarıyla kaydedildi', life: 3000 });
        } catch (error) {
            console.error(error);
            toast.value.add({ severity: 'error', summary: 'Hata', detail: 'Şablon kaydedilemedi', life: 3000 });
        }
    } else {
        toast.value.add({ severity: 'error', summary: 'Hata', detail: 'Şablon adı boş olamaz', life: 3000 });
    }
};

const editShift = (shiftTemplate) => {
    shift.value = { ...shiftTemplate };
    shiftDialog.value = true;
};
const confirmDeleteShift = (shiftTemplate) => {
    shift.value = shiftTemplate;
    deleteShiftDialog.value = true;
};
const deleteShift = async () => {
    try {
        const response = await axios.delete(`/api/shift/shift-templates/${shift.value.id}`);
        await fetchShifts();
        deleteShiftDialog.value = false;
        shift.value = {};
        toast.value.add({ severity: 'success', summary: 'Başarılı', detail:response.data.message , life: 3000 });
    } catch (error) {
        console.error(error);
        toast.value.add({ severity: 'error', summary: 'Hata', detail: response.data.message, life: 3000 });
    }
};
const confirmDeleteSelected = () => {
    deleteShiftsDialog.value = true;
};
const deleteSelectedShifts = async () => {
    try {
        await Promise.all(selectedShifts.value.map(selectedShift => axios.delete(`/api/shift/shift-templates/${selectedShift.id}`)));
        await fetchShifts();
        deleteShiftsDialog.value = false;
        selectedShifts.value = null;
        toast.value.add({ severity: 'success', summary: 'Başarılı', detail: 'Seçilen şablonlar başarıyla silindi', life: 3000 });
    } catch (error) {
        console.error(error);
        toast.value.add({ severity: 'error', summary: 'Hata', detail: 'Şablonlar silinemedi', life: 3000 });
    }
};
const exportCSV = (event) => {
    // Implement CSV export functionality here
};

</script>
