<template>
    <div>
        <div class="card">
            <Toolbar class="mb-4">
                <template #start>
                    <Button label="Sil" icon="pi pi-trash" severity="danger" @click="confirmDeleteSelected" :disabled="!selectedRequests || !selectedRequests.length" />
                </template>
                <template #end>
                    <Dropdown v-model="selectedType" :options="typeOptions" optionLabel="label" placeholder="Tip Filtrele" class="mr-2" @change="loadContactRequests" />
                    <Dropdown v-model="selectedStatus" :options="statusOptions" optionLabel="label" placeholder="Durum Filtrele" class="mr-2" @change="loadContactRequests" />
                    <Button label="Dışa Aktar" icon="pi pi-upload" severity="help" @click="exportCSV" />
                </template>
            </Toolbar>
            <DataTable ref="dt" :value="contactRequests" v-model:selection="selectedRequests" dataKey="id"
                       :paginator="true" :rows="10" :filters="filters"
                       paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown" :rowsPerPageOptions="[5,10,25]"
                       currentPageReportTemplate="Showing {first} to {last} of {totalRecords} requests">
                <template #header>
                    <div class="p-inputgroup">
                        <span class="p-inputgroup-addon"><i class="pi pi-search"></i></span>
                        <InputText v-model="filters['global'].value" placeholder="Ara..." />
                    </div>
                </template>
                <Column selectionMode="multiple" style="width: 2rem" :exportable="false"></Column>
                <Column field="name" header="Ad Soyad" sortable style="min-width:10rem"></Column>
                <Column field="email" header="E-posta" sortable style="min-width:12rem"></Column>
                <Column field="phone" header="Telefon" sortable style="min-width:10rem"></Column>
                <Column field="type" header="Tip" sortable style="min-width:8rem">
                    <template #body="slotProps">
                        <Tag :value="slotProps.data.type === 'quote' ? 'Teklif' : 'İletişim'" 
                             :severity="slotProps.data.type === 'quote' ? 'success' : 'info'" />
                    </template>
                </Column>
                <Column field="status" header="Durum" sortable style="min-width:8rem">
                    <template #body="slotProps">
                        <Tag :value="getStatusLabel(slotProps.data.status)" 
                             :severity="getStatusSeverity(slotProps.data.status)" />
                    </template>
                </Column>
                <Column field="created_at" header="Tarih" sortable style="min-width:10rem">
                    <template #body="slotProps">
                        {{ formatDate(slotProps.data.created_at) }}
                    </template>
                </Column>
                <Column :exportable="false" style="min-width:8rem">
                    <template #body="slotProps">
                        <Button icon="pi pi-eye" outlined rounded class="mr-2" @click="viewRequest(slotProps.data)" />
                        <Button icon="pi pi-trash" outlined rounded severity="danger" @click="confirmDeleteRequest(slotProps.data)" />
                    </template>
                </Column>
            </DataTable>
        </div>
        <Toast ref="toast" />

        <!-- İletişim İsteği Detayları Diyaloğu -->
        <Dialog v-model:visible="requestDialog" :style="{width: '600px'}" header="İletişim İsteği Detayları" :modal="true" class="p-fluid">
            <div class="field">
                <label>Ad Soyad</label>
                <InputText v-model.trim="request.name" disabled />
            </div>
            <div class="field">
                <label>E-posta</label>
                <InputText v-model.trim="request.email" disabled />
            </div>
            <div class="field">
                <label>Telefon</label>
                <InputText v-model.trim="request.phone" disabled />
            </div>
            <div class="field" v-if="request.type === 'quote'">
                <label>Ürün</label>
                <InputText v-model.trim="request.product" disabled />
            </div>
            <div class="field" v-if="request.type === 'quote'">
                <label>Miktar</label>
                <InputText v-model.trim="request.quantity" disabled />
            </div>
            <div class="field" v-if="request.subject">
                <label>Konu</label>
                <InputText v-model.trim="request.subject" disabled />
            </div>
            <div class="field" v-if="request.message">
                <label>Mesaj</label>
                <Textarea v-model.trim="request.message" rows="5" disabled />
            </div>
            <div class="field">
                <label>Durum</label>
                <Dropdown 
                    v-model="request.status" 
                    :options="statusOptions.filter(opt => opt.value !== null)" 
                    optionLabel="label" 
                    optionValue="value" 
                    @change="updateStatus" 
                />
            </div>
            <template #footer>
                <Button label="Kapat" icon="pi pi-times" text @click="hideDialog" />
            </template>
        </Dialog>

        <Dialog v-model:visible="deleteRequestDialog" :style="{width: '450px'}" header="Onayla" :modal="true">
            <div class="confirmation-content">
                <i class="pi pi-exclamation-triangle mr-3" style="font-size: 2rem" />
                <span v-if="request">Silmek istediğinize emin misiniz <b>{{request.name}}</b>?</span>
            </div>
            <template #footer>
                <Button label="Hayır" icon="pi pi-times" text @click="deleteRequestDialog = false" />
                <Button label="Evet" icon="pi pi-check" text @click="deleteRequest" />
            </template>
        </Dialog>

        <!-- Seçilen İstekleri Silme Onay Diyaloğu -->
        <Dialog v-model:visible="deleteRequestsDialog" :style="{width: '450px'}" header="Onayla" :modal="true">
            <div class="confirmation-content">
                <i class="pi pi-exclamation-triangle mr-3" style="font-size: 2rem" />
                <span>Seçilen istekleri silmek istediğinize emin misiniz?</span>
            </div>
            <template #footer>
                <Button label="Hayır" icon="pi pi-times" text @click="deleteRequestsDialog = false" />
                <Button label="Evet" icon="pi pi-check" text @click="deleteSelectedRequests" />
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
import Textarea from 'primevue/textarea';
import Dialog from 'primevue/dialog';
import Toast from 'primevue/toast';
import Tag from 'primevue/tag';
import Dropdown from 'primevue/dropdown';

const toast = ref();
const contactRequests = ref([]);
const requestDialog = ref(false);
const deleteRequestDialog = ref(false);
const deleteRequestsDialog = ref(false);
const request = ref({});
const selectedRequests = ref();
const filters = ref({
    'global': { value: null, matchMode: FilterMatchMode.CONTAINS }
});
const dt = ref();
const selectedType = ref(null);
const selectedStatus = ref(null);

const typeOptions = ref([
    { label: 'Tümü', value: null },
    { label: 'İletişim', value: 'contact' },
    { label: 'Teklif', value: 'quote' }
]);

const statusOptions = ref([
    { label: 'Tümü', value: null },
    { label: 'Yeni', value: 'new' },
    { label: 'Okundu', value: 'read' },
    { label: 'Yanıtlandı', value: 'replied' },
    { label: 'Kapatıldı', value: 'closed' }
]);

const getStatusLabel = (status) => {
    const statusMap = {
        'new': 'Yeni',
        'read': 'Okundu',
        'replied': 'Yanıtlandı',
        'closed': 'Kapatıldı'
    };
    return statusMap[status] || status;
};

const getStatusSeverity = (status) => {
    const severityMap = {
        'new': 'warning',
        'read': 'info',
        'replied': 'success',
        'closed': 'secondary'
    };
    return severityMap[status] || 'info';
};

const formatDate = (dateString) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toLocaleDateString('tr-TR', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const loadContactRequests = async () => {
    try {
        const params = {};
        if (selectedType.value) params.type = selectedType.value;
        if (selectedStatus.value) params.status = selectedStatus.value;
        
        const response = await axios.get('/api/contact-requests', { params });
        contactRequests.value = response.data.data || response.data;
    } catch (error) {
        toast.value.show({ severity: 'error', summary: 'Hata', detail: 'İstekler yüklenirken bir hata oluştu', life: 3000 });
    }
};

const viewRequest = (requestData) => {
    request.value = { ...requestData };
    requestDialog.value = true;
};

const hideDialog = () => {
    requestDialog.value = false;
    request.value = {};
};

const updateStatus = async () => {
    if (!request.value.id || !request.value.status) {
        toast.value.show({ severity: 'warn', summary: 'Uyarı', detail: 'Lütfen geçerli bir durum seçin', life: 3000 });
        return;
    }
    
    try {
        const response = await axios.put(`/api/contact-requests/${request.value.id}`, {
            status: request.value.status
        });
        toast.value.show({ severity: 'success', summary: 'Başarılı', detail: 'Durum güncellendi', life: 3000 });
        // Dialog'u kapatmadan önce listeyi yenile
        await loadContactRequests();
    } catch (error) {
        console.error('Update status error:', error);
        const errorMessage = error.response?.data?.message || error.response?.data?.error || error.message || 'Durum güncellenirken bir hata oluştu';
        toast.value.show({ severity: 'error', summary: 'Hata', detail: errorMessage, life: 3000 });
    }
};

const confirmDeleteRequest = (requestData) => {
    request.value = requestData;
    deleteRequestDialog.value = true;
};

const deleteRequest = async () => {
    try {
        await axios.delete(`/api/contact-requests/${request.value.id}`);
        toast.value.show({ severity: 'success', summary: 'Başarılı', detail: 'İstek silindi', life: 3000 });
        deleteRequestDialog.value = false;
        loadContactRequests();
    } catch (error) {
        toast.value.show({ severity: 'error', summary: 'Hata', detail: 'İstek silinirken bir hata oluştu', life: 3000 });
    }
};

const confirmDeleteSelected = () => {
    deleteRequestsDialog.value = true;
};

const deleteSelectedRequests = async () => {
    try {
        const ids = selectedRequests.value.map(r => r.id);
        await axios.delete('/api/contact-requests', { data: { ids } });
        toast.value.show({ severity: 'success', summary: 'Başarılı', detail: 'Seçili istekler silindi', life: 3000 });
        deleteRequestsDialog.value = false;
        selectedRequests.value = null;
        loadContactRequests();
    } catch (error) {
        toast.value.show({ severity: 'error', summary: 'Hata', detail: 'İstekler silinirken bir hata oluştu', life: 3000 });
    }
};

const exportCSV = () => {
    dt.value.exportCSV();
};

onMounted(() => {
    loadContactRequests();
});
</script>

<style scoped>
.confirmation-content {
    display: flex;
    align-items: center;
    justify-content: center;
}
</style>

