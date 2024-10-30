<template>
    <div>
        <h1>Machines</h1>
        <div class="card">
            <Toolbar class="mb-4">
                <template #start>
                    <Button label="New" icon="pi pi-plus" severity="success" class="mr-2" @click="openNew" />
                    <Button label="Delete" icon="pi pi-trash" severity="danger" @click="confirmDeleteSelected" :disabled="!selectedMachines || !selectedMachines.length" />
                </template>
                <template #end>
                    <FileUpload mode="basic" accept="image/*" :maxFileSize="1000000" label="Import" chooseLabel="Import" class="mr-2 inline-block" />
                    <Button label="Export" icon="pi pi-upload" severity="help" @click="exportCSV" />
                </template>
            </Toolbar>
            <DataTable ref="dt" :value="machines" v-model:selection="selectedMachines" dataKey="id"
                       :paginator="true" :rows="10" :filters="filters"
                       paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown" :rowsPerPageOptions="[5,10,25]"
                       currentPageReportTemplate="Showing {first} to {last} of {totalRecords} machines">
                <template #header>
                    <div class="p-inputgroup">
                        <span class="p-inputgroup-addon"><i class="pi pi-search"></i></span>
                        <InputText v-model="filters['global'].value" placeholder="Search..." />
                    </div>
                </template>
                <Column selectionMode="multiple" style="width: 3rem" :exportable="false"></Column>
                <Column field="id" header="Makina Id" sortable style="min-width:12rem"></Column>
                <Column field="machine_name" header="Makina Adı" sortable style="min-width:16rem"></Column>
                <Column :exportable="false" style="min-width:8rem">
                    <template #body="slotProps">
                        <Button icon="pi pi-pencil" outlined rounded class="mr-2" @click="editMachine(slotProps.data)" />
                        <Button icon="pi pi-trash" outlined rounded severity="danger" @click="confirmDeleteMachine(slotProps.data)" />
                    </template>
                </Column>
            </DataTable>
        </div>
        <Toast ref="toast" />

        <!-- Machine Details Dialog -->
        <Dialog v-model:visible="machineDialog" :style="{width: '450px'}" header="Machine Details" :modal="true" class="p-fluid">
            <div class="field">
                <label for="name">Machine Name</label>
                <InputText id="name" v-model.trim="machine.name" required :invalid="submitted && !machine.name" />
                <small class="p-error" v-if="submitted && !machine.name">Name is required.</small>
            </div>
            <template #footer>
                <Button label="Cancel" icon="pi pi-times" text @click="hideDialog" />
                <Button label="Save" icon="pi pi-check" text @click="saveMachine" />
            </template>
        </Dialog>

        <!-- Delete Machine Confirmation Dialog -->
        <Dialog v-model:visible="deleteMachineDialog" :style="{width: '450px'}" header="Confirm" :modal="true">
            <div class="confirmation-content">
                <i class="pi pi-exclamation-triangle mr-3" style="font-size: 2rem" />
                <span v-if="machine">Are you sure you want to delete <b>{{machine.name}}</b>?</span>
            </div>
            <template #footer>
                <Button label="No" icon="pi pi-times" text @click="deleteMachineDialog = false" />
                <Button label="Yes" icon="pi pi-check" text @click="deleteMachine" />
            </template>
        </Dialog>

        <!-- Delete Selected Machines Confirmation Dialog -->
        <Dialog v-model:visible="deleteMachinesDialog" :style="{width: '450px'}" header="Confirm" :modal="true">
            <div class="confirmation-content">
                <i class="pi pi-exclamation-triangle mr-3" style="font-size: 2rem" />
                <span>Are you sure you want to delete the selected machines?</span>
            </div>
            <template #footer>
                <Button label="No" icon="pi pi-times" text @click="deleteMachinesDialog = false" />
                <Button label="Yes" icon="pi pi-check" text @click="deleteSelectedMachines" />
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

const toast = ref(null);
const machines = ref([]);
const machineDialog = ref(false);
const deleteMachineDialog = ref(false);
const deleteMachinesDialog = ref(false);
const machine = ref({});
const selectedMachines = ref([]);
const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
});
const submitted = ref(false);

const openEditDialog = (machineData) => {
    machine.id = machineData.id;
    machine.name = machineData.name;
    machineDialog.value = true;
};

const fetchMachines = () => {
    axios.get('/api/machines')
        .then(response => {
            machines.value = response.data;
        })
        .catch(error => {
            console.error("Error fetching machines:", error);
        });
};

onMounted(() => {
    fetchMachines();
});

const openNew = () => {
    machine.value = {};
    submitted.value = false;
    machineDialog.value = true;
};

const hideDialog = () => {
    machineDialog.value = false;
    submitted.value = false;
};

const saveMachine = () => {
    submitted.value = true;

    if (machine.value.name) {
        if (machine.value.id) {
            axios.put(`/api/machines/${machine.value.id}`, machine.value)
                .then(() => {
                    toast.value.add({ severity: 'success', summary: 'Success', detail: 'Machine updated successfully', life: 3000 });
                    fetchMachines();
                });
        } else {
            axios.post('/api/machines', machine.value)
                .then(() => {
                    toast.value.add({ severity: 'success', summary: 'Success', detail: 'Machine added successfully', life: 3000 });
                    fetchMachines();
                });
        }
        machineDialog.value = false;
        machine.value = {};
    } else {
        toast.value.add({ severity: 'error', summary: 'Error', detail: 'Machine name is required', life: 3000 });
    }
};

const editMachine = (machineData) => {
    machine.value = { ...machineData };
    machineDialog.value = true;
};

const confirmDeleteMachine = (machineData) => {
    machine.value = machineData;
    deleteMachineDialog.value = true;
};

const deleteMachine = () => {
    axios.delete(`/api/machines/${machine.value.id}`)
        .then(() => {
            toast.value.add({ severity: 'success', summary: 'Success', detail: 'Machine deleted successfully', life: 3000 });
            fetchMachines();
            deleteMachineDialog.value = false;
        });
};

const confirmDeleteSelected = () => {
    deleteMachinesDialog.value = true;
};

const deleteSelectedMachines = () => {
    const machineIds = selectedMachines.value.map(machine => machine.id);
    axios.post('/api/machines/delete', { ids: machineIds })
        .then(() => {
            toast.value.add({ severity: 'success', summary: 'Success', detail: 'Selected machines deleted successfully', life: 3000 });
            fetchMachines();
            deleteMachinesDialog.value = false;
            selectedMachines.value = [];
        });
};

const exportCSV = () => {
    // Export functionality implementation
};
</script>
