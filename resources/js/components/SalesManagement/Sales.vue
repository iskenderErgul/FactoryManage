<template>
    <div>
        <div class="card">
            <Toolbar class="mb-4">
                <template #start>
                    <Button label="Yeni Satış" icon="pi pi-plus" severity="success" class="mr-2" @click="openNew" />
                    <Button label="Sil" icon="pi pi-trash" severity="danger" @click="confirmDeleteSelected" :disabled="!selectedSales || !selectedSales.length" />
                </template>
                <template #end>
                    <Button label="Dışa Aktar" icon="pi pi-upload" severity="help" @click="exportCSV" />
                </template>
            </Toolbar>
            <DataTable ref="dt" :value="sales" v-model:selection="selectedSales" dataKey="id"
                       :paginator="true" :rows="10"
                       paginatorTemplate="İlk Sayfa Bağlantısı Önceki Sayfa Bağlantısı Sayfa Bağlantıları Sonraki Sayfa Bağlantısı Mevcut Sayfa Raporu Sayfa Başına Satır Aşağı Aşağı"
                       :rowsPerPageOptions="[5,10,25]"
                       currentPageReportTemplate="Mevcut {first} ile {last} arasında, toplam {totalRecords} satış">
                <Column selectionMode="multiple" style="width: 2rem" :exportable="false"></Column>
                <Column field="id" header="Satış ID" sortable style="min-width:8rem"></Column>
                <Column field="customer_name" header="Müşteri Adı" sortable style="min-width:10rem"></Column>
                <Column field="sale_date" header="Satış Tarihi" sortable style="min-width:10rem"></Column>
                <Column :exportable="false" style="min-width:8rem">
                    <template #body="slotProps">
                        <Button icon="pi pi-pencil" outlined rounded class="mr-2" @click="editSale(slotProps.data)" />
                        <Button icon="pi pi-trash" outlined rounded severity="danger" @click="confirmDeleteSale(slotProps.data)" />
                    </template>
                </Column>
            </DataTable>
        </div>
        <Toast ref="toast" />

        <!-- Satış Detayları Diyaloğu -->
        <Dialog v-model:visible="saleDialog" :style="{width: '450px'}" header="Satış Detayları" :modal="true" class="p-fluid">
            <div class="field">
                <label for="customerName">Müşteri Adı</label>
                <InputText id="customerName" v-model.trim="sale.customer_name" required :invalid="submitted && !sale.customer_name" />
                <small class="p-error" v-if="submitted && !sale.customer_name">Müşteri adı zorunludur.</small>
            </div>
            <div class="field">
                <label for="saleDate">Satış Tarihi</label>
                <InputText id="saleDate" v-model="sale.sale_date" required />
            </div>
            <template #footer>
                <Button label="İptal" icon="pi pi-times" text @click="hideDialog" />
                <Button label="Kaydet" icon="pi pi-check" text @click="saveSale" />
            </template>
        </Dialog>

        <Dialog v-model:visible="deleteSaleDialog" :style="{width: '450px'}" header="Onayla" :modal="true">
            <div class="confirmation-content">
                <i class="pi pi-exclamation-triangle mr-3" style="font-size: 2rem" />
                <span v-if="sale">Silmek istediğinize emin misiniz <b>{{ sale.customer_name }}</b>?</span>
            </div>
            <template #footer>
                <Button label="Hayır" icon="pi pi-times" text @click="deleteSaleDialog = false" />
                <Button label="Evet" icon="pi pi-check" text @click="deleteSale" />
            </template>
        </Dialog>

        <Dialog v-model:visible="deleteSalesDialog" :style="{width: '450px'}" header="Onayla" :modal="true">
            <div class="confirmation-content">
                <i class="pi pi-exclamation-triangle mr-3" style="font-size: 2rem" />
                <span>Seçilen satışları silmek istediğinize emin misiniz?</span>
            </div>
            <template #footer>
                <Button label="Hayır" icon="pi pi-times" text @click="deleteSalesDialog = false" />
                <Button label="Evet" icon="pi pi-check" text @click="deleteSelectedSales" />
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

const toast = ref(null);
const sales = ref([]);
const saleDialog = ref(false);
const deleteSaleDialog = ref(false);
const deleteSalesDialog = ref(false);
const sale = ref({});
const selectedSales = ref([]);
const submitted = ref(false);

const fetchSales = () => {
    axios.get('/api/sales')
        .then(response => {
            sales.value = response.data;
        })
        .catch(error => {
            console.error("Satışları getirirken hata:", error);
        });
};

onMounted(() => {
    fetchSales();
});

const openNew = () => {
    sale.value = {};
    submitted.value = false;
    saleDialog.value = true;
};

const hideDialog = () => {
    saleDialog.value = false;
    submitted.value = false;
};

const saveSale = () => {
    submitted.value = true;

    if (sale.value.customer_name) {
        if (sale.value.id) {
            axios.put(`/api/sales/${sale.value.id}`, sale.value)
                .then(() => {
                    toast.value.add({ severity: 'success', summary: 'Başarılı', detail: 'Satış başarıyla güncellendi', life: 3000 });
                    fetchSales();
                });
        } else {
            axios.post('/api/sales', sale.value)
                .then(() => {
                    toast.value.add({ severity: 'success', summary: 'Başarılı', detail: 'Satış başarıyla eklendi', life: 3000 });
                    fetchSales();
                });
        }
        saleDialog.value = false;
        sale.value = {};
    }
};

const confirmDeleteSale = (saleToDelete) => {
    sale.value = { ...saleToDelete };
    deleteSaleDialog.value = true;
};

const deleteSale = () => {
    axios.delete(`/api/sales/${sale.value.id}`)
        .then(() => {
            toast.value.add({ severity: 'success', summary: 'Başarılı', detail: 'Satış başarıyla silindi', life: 3000 });
            fetchSales();
        });

    deleteSaleDialog.value = false;
    sale.value = {};
};

const confirmDeleteSelected = () => {
    deleteSalesDialog.value = true;
};

const deleteSelectedSales = () => {
    const ids = selectedSales.value.map(sale => sale.id);
    axios.delete('/api/sales', { data: { ids } })
        .then(() => {
            toast.value.add({ severity: 'success', summary: 'Başarılı', detail: 'Seçilen satışlar başarıyla silindi', life: 3000 });
            fetchSales();
        });

    deleteSalesDialog.value = false;
    selectedSales.value = [];
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
