<template>
    <div>
        <DataTable
            :value="formattedLogs"
            ref="dt"
            tableStyle="min-width: 50rem"
            paginator
            :rows="5"
            :rowsPerPageOptions="[5, 10, 20, 50]"
            paginatorTemplate="RowsPerPageDropdown FirstPageLink PrevPageLink CurrentPageReport NextPageLink LastPageLink"
            currentPageReportTemplate="{first} to {last} of {totalRecords}"
        >
            <template #header>
                <div class="text-end pb-4">
                    <Button icon="pi pi-external-link" label="Export" @click="exportCSV($event)" />
                </div>
            </template>
            <Column field="id" header="Log ID" style="width: 5%" />
            <Column field="pacs_entry_id" header="PACS Entry ID" style="width: 10%" />
            <Column field="user.name" header="Kullanıcı" style="width: 15%" />
            <Column field="action" header="İşlem Türü" style="width: 15%" />
            <Column field="changes" header="Değişiklikler" style="width: 25%" />
            <Column field="created_at" header="Oluşturulma Tarihi" style="width: 15%" />
            <Column field="updated_at" header="Güncelleme Tarihi" style="width: 15%" />
        </DataTable>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import Button from 'primevue/button';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import axios from 'axios';
import { format } from 'date-fns';

const dt = ref();
const pacsEntriesLogs = ref([]);

onMounted(() => {
    getPacsEntriesLogs();
});

const getPacsEntriesLogs = () => {
    axios.get('/api/getAllPacsEntriesLogs').then(resp => {
        pacsEntriesLogs.value = resp.data.reverse();
    });
};

const formattedLogs = computed(() => {
    return pacsEntriesLogs.value.map(log => ({
        ...log,
        created_at: log.created_at ? format(new Date(log.created_at), 'dd/MM/yyyy') : '',
        updated_at: log.updated_at ? format(new Date(log.updated_at), 'dd/MM/yyyy') : ''
    }));
});

const exportCSV = () => {
    dt.value.exportCSV();
};
</script>
