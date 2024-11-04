
<template>

    <div class="text-center">
        <input v-model="userId" placeholder="ID girin" class="p-inputtext mb-2" />
        <div>
            <Button label="Giriş" icon="pi pi-sign-in" @click="createEntry('checkin')" class="mr-2" />
            <Button label="Çıkış" icon="pi pi-sign-out" @click="createEntry('checkout')" />
        </div>
    </div>
    <Toast ref="toast" />


    <div>
        <DataTable
            :value="formattedEntries"
            ref="dt"
            tableStyle="min-width: 50rem"
            paginator
            :rows="5"
            :rowsPerPageOptions="[5, 10, 20, 50]"
            paginatorTemplate="RowsPerPageDropdown FirstPageLink PrevPageLink CurrentPageReport NextPageLink LastPageLink"
            currentPageReportTemplate="{first} ile {last} arasında {totalRecords}"
        >
            <template #header>
                <div class="text-end pb-4">
                    <Button icon="pi pi-external-link" label="Export" @click="exportCSV($event)" />
                </div>
            </template>
            <Column field="id" header="Entry ID" style="width: 5%" />
            <Column field="user.name" header="Kullanıcı" style="width: 20%" />
            <Column field="entry_type" header="Giriş Çıkış Türü" style="width: 20%" />
            <Column field="created_at" header="Oluşturulma Tarihi" style="width: 25%" />
            <Column field="updated_at" header="Güncelleme Tarihi" style="width: 25%" />
        </DataTable>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import Button from 'primevue/button';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Toast from 'primevue/toast';
import axios from 'axios';
import { format } from 'date-fns';


const toast = ref(null);
const dt = ref();
const pacsEntries = ref([]);
const userId = ref('');



onMounted(() => {
    getPacsEntries();
});
const getPacsEntries = () => {
    axios.get('/api/getAllPacsEntries').then(resp => {
        pacsEntries.value = resp.data.reverse();
    });
};
const formattedEntries = computed(() => {
    return pacsEntries.value.map(entry => ({
        ...entry,
        created_at: entry.created_at ? format(new Date(entry.created_at), 'dd/MM/yyyy HH:mm:ss') : '',
        updated_at: entry.updated_at ? format(new Date(entry.updated_at), 'dd/MM/yyyy HH:mm:ss') : ''
    }));
});
const exportCSV = () => {
    dt.value.exportCSV();
};


const createEntry = async (entryType) => {
    if (!userId.value) {
        toast.value.add({ severity: 'errors', summary: 'Başarısız', detail: 'Lütfen Bir Id giriniz', life: 3000 });
        return;
    }

    try {
        const response = await axios.post('/api/createPacsEntry', {
            user_id : userId.value,
            entry_type: entryType,
        });
        getPacsEntries();
        toast.value.add({ severity: 'success', summary: 'Başarılı', detail: `Başarıyla ${entryType === 'checkin' ? 'giriş' : 'çıkış'} işlemi gerçekleştirildi!`, life: 3000 });
    } catch (error) {
        toast.value.add({ severity: 'error', summary: 'Başarısız', detail:error.data, life: 3000 });
    }
};
</script>
