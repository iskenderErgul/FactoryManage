<template>
    <div>
        <div class="card">
            <Toolbar class="mb-4">
                <template #start>
                    <Button label="Yeni" icon="pi pi-plus" severity="success" class="mr-2" @click="openNew" />
                    <Button label="Sil" icon="pi pi-trash" severity="danger" @click="confirmDeleteSelected" :disabled="!selectedCosts || !selectedCosts.length" />
                </template>
                <template #end>
                    <Button label="Dışa Aktar" icon="pi pi-upload" severity="help" @click="exportCSV" />
                </template>
            </Toolbar>
            <DataTable ref="dt" :value="costs" v-model:selection="selectedCosts" dataKey="id"
                       :paginator="true" :rows="10" :filters="filters"
                       paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
                       :rowsPerPageOptions="[5,10,25]"
                       currentPageReportTemplate="Mevcut {first} ile {last} arasında, toplam {totalRecords} maliyet">
                <template #header>
                    <div class="p-inputgroup">
                        <span class="p-inputgroup-addon"><i class="pi pi-search"></i></span>
                        <InputText v-model="filters['global'].value" placeholder="Ara..." />
                    </div>
                </template>
                <Column selectionMode="multiple" style="width: 2rem" :exportable="false"></Column>
                <Column field="id" header="Maliyet ID" sortable style="min-width:8rem"></Column>
                <Column field="cost_type" header="Maliyet Türü" sortable style="min-width:10rem"></Column>
                <Column field="amount" header="Tutar (TL)" sortable style="min-width:8rem">
                    <template #body="{ data }">
                        {{ formatAmount(data.amount) }}
                    </template>
                </Column>
                <Column field="cost_date" header="Maliyet Tarihi" sortable style="min-width:10rem"></Column>
                <Column :exportable="false" style="min-width:8rem">
                    <template #body="slotProps">
                        <Button icon="pi pi-pencil" outlined rounded class="mr-2" @click="editCost(slotProps.data)" />
                        <Button icon="pi pi-trash" outlined rounded severity="danger" @click="confirmDeleteCost(slotProps.data)" />
                    </template>
                </Column>
            </DataTable>
        </div>
        <Toast ref="toast" />

        <!-- Maliyet Detayları Diyaloğu -->
        <Dialog v-model:visible="costDialog" :style="{width: '450px'}" header="Maliyet Detayları" :modal="true" class="p-fluid">
            <div class="field">
                <label for="costType">Maliyet Türü</label>
                <InputText id="costType" v-model.trim="cost.cost_type" required :invalid="submitted && !cost.cost_type" />
                <small class="p-error" v-if="submitted && !cost.cost_type">Maliyet türü zorunludur.</small>
            </div>
            <div class="field">
                <label for="amount">Tutar (TL)</label>
                <InputText id="amount" v-model.number="cost.amount" required :invalid="submitted && !cost.amount" />
                <small class="p-error" v-if="submitted && !cost.amount">Tutar zorunludur.</small>
            </div>
            <div class="field">
                <label for="costDate">Maliyet Tarihi</label>
                <Calendar
                    id="cost_date"
                    v-model="cost.cost_date"
                    required
                    :invalid="submitted && !cost.cost_date"
                />
                <small class="p-error" v-if="submitted && !cost.cost_date">Maliyet tarihi zorunludur.</small>

            </div>
            <template #footer>
                <Button label="İptal" icon="pi pi-times" text @click="hideDialog" />
                <Button label="Kaydet" icon="pi pi-check" text @click="saveCost" />
            </template>
        </Dialog>


        <Dialog v-model:visible="deleteCostDialog" :style="{width: '450px'}" header="Onayla" :modal="true">
            <div class="confirmation-content">
                <i class="pi pi-exclamation-triangle mr-3" style="font-size: 2rem" />
                <span v-if="cost">Silmek istediğinize emin misiniz <b>{{cost.cost_type}}</b>?</span>
            </div>
            <template #footer>
                <Button label="Hayır" icon="pi pi-times" text @click="deleteCostDialog = false" />
                <Button label="Evet" icon="pi pi-check" text @click="deleteCost" />
            </template>
        </Dialog>
        <Dialog v-model:visible="deleteCostsDialog" :style="{width: '450px'}" header="Onayla" :modal="true">
            <div class="confirmation-content">
                <i class="pi pi-exclamation-triangle mr-3" style="font-size: 2rem" />
                <span>Seçilen maliyetleri silmek istediğinize emin misiniz?</span>
            </div>
            <template #footer>
                <Button label="Hayır" icon="pi pi-times" text @click="deleteCostsDialog = false" />
                <Button label="Evet" icon="pi pi-check" text @click="deleteSelectedCosts" />
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
import Calendar from "primevue/calendar";

const toast = ref(null);
const costs = ref([]);
const costDialog = ref(false);
const deleteCostDialog = ref(false);
const deleteCostsDialog = ref(false);
const cost = ref({});
const selectedCosts = ref([]);
const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
});
const submitted = ref(false);

const fetchCosts = () => {
    axios.get('/api/costs')
        .then(response => {
            costs.value = response.data;
        })
        .catch(error => {
            console.error("Maliyetleri getirirken hata:", error);
        });
};

onMounted(() => {
    fetchCosts();
});

const openNew = () => {
    cost.value = {};
    submitted.value = false;
    costDialog.value = true;
};

const hideDialog = () => {
    costDialog.value = false;
    submitted.value = false;
};

const saveCost = () => {
    submitted.value = true;

    if (cost.value.cost_type) {
        if (cost.value.id) {
            axios.put(`/api/costs/${cost.value.id}`, cost.value)
                .then(() => {
                    toast.value.add({ severity: 'success', summary: 'Başarılı', detail: 'Maliyet başarıyla güncellendi', life: 3000 });
                    fetchCosts();
                });
        } else {
            axios.post('/api/costs', cost.value)
                .then(() => {
                    toast.value.add({ severity: 'success', summary: 'Başarılı', detail: 'Maliyet başarıyla eklendi', life: 3000 });
                    fetchCosts();
                });
        }
        costDialog.value = false;
        cost.value = {};
    } else {
        toast.value.add({ severity: 'error', summary: 'Hata', detail: 'Maliyet türü zorunludur', life: 3000 });
    }
};

const editCost = (costToEdit) => {
    cost.value = { ...costToEdit };
    costDialog.value = true;
};

const confirmDeleteCost = (costToDelete) => {
    cost.value = { ...costToDelete };
    deleteCostDialog.value = true;
};

const deleteCost = () => {
    axios.delete(`/api/costs/${cost.value.id}`)
        .then(() => {
            toast.value.add({ severity: 'success', summary: 'Başarılı', detail: 'Maliyet başarıyla silindi', life: 3000 });
            fetchCosts();
        });

    deleteCostDialog.value = false;
    cost.value = {};
};

const confirmDeleteSelected = () => {
    deleteCostsDialog.value = true;
};

const deleteSelectedCosts = () => {
    const ids = selectedCosts.value.map(cost => cost.id);
    axios.delete('/api/costs', { data: { ids } })
        .then(() => {
            toast.value.add({ severity: 'success', summary: 'Başarılı', detail: 'Seçilen maliyetler başarıyla silindi', life: 3000 });
            fetchCosts();
        });

    deleteCostsDialog.value = false;
    selectedCosts.value = [];
};

const exportCSV = () => {
        axios.get('/api/costs-export', { responseType: 'blob' })
            .then(response => {
                const url = window.URL.createObjectURL(new Blob([response.data]));
                const link = document.createElement('a');
                link.href = url;
                link.setAttribute('download', 'costs.xlsx');
                document.body.appendChild(link);
                link.click();
            })
            .catch(error => {
                console.error('Export failed:', error);
            });
};

const formatAmount = (amount) => {
    return amount % 1 === 0 ? amount.toString().split('.')[0] : amount.toFixed(2);
}
</script>

<style scoped>
.p-inputgroup {
    width: 100%;
}
</style>
