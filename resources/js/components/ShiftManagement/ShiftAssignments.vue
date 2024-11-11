<template>
    <div>
        <div class="card">
            <Toolbar class="mb-4">
                <template #start>
                    <Button label="Yeni Atama" icon="pi pi-plus" severity="success" class="mr-2" @click="openNewAssignment" />
                    <Button label="Sil" icon="pi pi-trash" severity="danger" @click="confirmDeleteSelected" :disabled="!selectedAssignments || !selectedAssignments.length" />
                </template>

                <template #end>
                    <Button label="Dışa Aktar" icon="pi pi-upload" severity="help" @click="exportCSV($event)" />
                </template>
            </Toolbar>

            <DataTable ref="dt" :value="assignments" v-model:selection="selectedAssignments" dataKey="id"
                       :paginator="true" :rows="10" :filters="filters"
                       paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown" :rowsPerPageOptions="[5,10,25]"
                       currentPageReportTemplate="Mevcut {first} ile {last} arasında {totalRecords} atama">

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
                <Column field="user.name" header="İşçi Adı" sortable style="min-width:16rem"></Column>
                <Column field="shift.template.name" header="Vardiya Adı" sortable style="min-width:16rem"></Column>
                <Column field="shift.template.start_time" header="Başlangıç Saati" sortable style="min-width:16rem"></Column>
                <Column field="shift.template.end_time" header="Bitiş Saati" sortable style="min-width:16rem"></Column>

                <Column :exportable="false" style="min-width:8rem">
                    <template #body="slotProps">
                        <Button icon="pi pi-pencil" outlined rounded class="mr-2" @click="editAssignment(slotProps.data)" />
                        <Button icon="pi pi-trash" outlined rounded severity="danger" @click="confirmDeleteAssignment(slotProps.data)" />
                    </template>
                </Column>
            </DataTable>
        </div>
        <Toast ref="toast" />

        <!-- Dialog for assignment details -->
        <Dialog v-model:visible="assignmentDialog" :style="{width: '450px'}" header="Atama Detayları" :modal="true" class="p-fluid">
            <div class="field">
                <label for="user">İşçi</label>
                <Dropdown id="user" v-model="assignment.user_id" :options="users" optionLabel="name" optionValue="id" placeholder="İşçi Seç" />
            </div>
            <div class="field">
                <label for="shift">Vardiya</label>
                <Dropdown id="shift" v-model="assignment.shift_id" :options="shifts" optionLabel="shift_name" optionValue="id" placeholder="Vardiya Seç" />
            </div>

            <template #footer>
                <Button label="İptal" icon="pi pi-times" text @click="hideDialog"/>
                <Button label="Kaydet" icon="pi pi-check" text @click="saveAssignment" />
            </template>
        </Dialog>

        <!-- Confirmation dialog for deletion -->
        <Dialog v-model:visible="deleteAssignmentDialog" :style="{width: '450px'}" header="Onay" :modal="true">
            <div class="confirmation-content">
                <i class="pi pi-exclamation-triangle mr-3" style="font-size: 2rem" />
                <span>Bu atamayı silmek istediğinizden emin misiniz? <b>{{assignment.user_name}} - {{assignment.shift_name}}</b>?</span>
            </div>
            <template #footer>
                <Button label="Hayır" icon="pi pi-times" text @click="deleteAssignmentDialog = false"/>
                <Button label="Evet" icon="pi pi-check" text @click="deleteAssignment" />
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
import Dropdown from 'primevue/dropdown';
import { FilterMatchMode } from 'primevue/api';


const toast = ref(null);
const dt = ref();
const assignments = ref([]);
const assignmentDialog = ref(false);
const deleteAssignmentDialog = ref(false);
const assignment = ref({});
const selectedAssignments = ref();
const filters = ref({
    'global': { value: null, matchMode: FilterMatchMode.CONTAINS },
});
const submitted = ref(false);
const users = ref([]);
const shifts = ref([]);

// Fetch initial data for assignments, users, and shifts
onMounted(async () => {
    await fetchAssignments();
    await fetchWorkers();
    await fetchShifts();
});

const fetchAssignments = async () => {
    try {
        const response = await axios.get('/api/shift/shift-assignments');
        assignments.value = response.data;
    } catch (error) {
        console.error(error);
        toast.value.add({ severity: 'error', summary: 'Hata', detail: 'Atamalar yüklenemedi', life: 3000 });
    }
};

const fetchWorkers = async () => {
    try {
        const response = await axios.get('/api/get-all-workers');
        users.value = response.data;
    } catch (error) {
        console.error(error);
    }
};

const fetchShifts = async () => {
    try {
        // Shift Templates'lerini çekmek için doğru endpoint'i kullanıyoruz
        const response = await axios.get('/api/shift/shift-templates');
        shifts.value = response.data.map(shift => ({
            id: shift.id,
            shift_name: `${shift.name} : ${shift.start_time} - ${shift.end_time}`
        }));
    } catch (error) {
        console.error(error);
    }
};

const openNewAssignment = () => {
    assignment.value = {};
    submitted.value = false;
    assignmentDialog.value = true;
};

const hideDialog = () => {
    assignmentDialog.value = false;
    submitted.value = false;
};

const saveAssignment = async () => {
    submitted.value = true;

    if (assignment.value.user_id && assignment.value.shift_id) {
        try {
            let response;
            if (assignment.value.id) {
                response = await axios.put(`/api/shift/shift-assignments/${assignment.value.id}`, assignment.value);
            } else {
                response = await axios.post('/api/shift/shift-assignments', assignment.value);
            }


            await fetchAssignments();
            assignmentDialog.value = false;
            assignment.value = {};
            toast.value.add({
                severity: 'success',
                summary: 'Başarılı',
                detail: response.data.message,
                life: 3000
            });
        } catch (error) {
            console.error(error);


            let errorMessage = 'Bir hata oluştu.';
            if (error.response && error.response.data && error.response.data.message) {
                errorMessage = error.response.data.message;
            }

            toast.value.add({
                severity: 'error',
                summary: 'Hata',
                detail: errorMessage,
                life: 3000
            });
        }
    } else {
        toast.value.add({
            severity: 'error',
            summary: 'Hata',
            detail: 'İşçi ve vardiya alanları gerekli',
            life: 3000
        });
    }
};

const editAssignment = (assignmentRecord) => {
    assignment.value = { ...assignmentRecord };
    assignmentDialog.value = true;
};

const confirmDeleteAssignment = (assignmentRecord) => {
    assignment.value = assignmentRecord;
    deleteAssignmentDialog.value = true;
};

const deleteAssignment = async () => {
    try {
        const response =await axios.delete(`/api/shift/shift-assignments/${assignment.value.id}`);
        await fetchAssignments();
        deleteAssignmentDialog.value = false;
        assignment.value = {};
        toast.value.add({ severity: 'success', summary: 'Başarılı', detail: response.data.message, life: 3000 });
    } catch (error) {
        console.error(error);
        toast.value.add({ severity: 'error', summary: 'Hata', detail: response.data.message, life: 3000 });
    }
};

const confirmDeleteSelected = () => {
    deleteAssignmentDialog.value = true;
};

const deleteSelectedAssignments = async () => {
    try {
        await Promise.all(selectedAssignments.value.map(assignment => axios.delete(`/api/assignments/${assignment.id}`)));
        await fetchAssignments();
        deleteAssignmentDialog.value = false;
        selectedAssignments.value = null;
        toast.value.add({ severity: 'success', summary: 'Başarılı', detail: 'Seçilen atamalar başarıyla silindi', life: 3000 });
    } catch (error) {
        console.error(error);
        toast.value.add({ severity: 'error', summary: 'Hata', detail: 'Atamalar silinemedi', life: 3000 });
    }
};
</script>
