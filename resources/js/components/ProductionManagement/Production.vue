<template>
    <div>
        <div class="card">
            <Toolbar class="mb-4">
                <template #start>
                    <Button label="Yeni" icon="pi pi-plus" severity="success" class="mr-2" @click="openNew" />
                    <Button label="Sil" icon="pi pi-trash" severity="danger" @click="confirmDeleteSelected" :disabled="!selectedProductions || !selectedProductions.length" />
                </template>
                <template #end>
                    <Button label="Dışa Aktar" icon="pi pi-upload" severity="help" @click="exportCSV" />
                </template>
            </Toolbar>
            <DataTable ref="dt" :value="productions" v-model:selection="selectedProductions" dataKey="id"
                       :paginator="true" :rows="10" :filters="filters"
                       paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
                       :rowsPerPageOptions="[5,10,25]"
                       currentPageReportTemplate="Gösterilen {first} ile {last} arasında {totalRecords} üretim">

                <template #header>
                    <div class="p-inputgroup">
                        <span class="p-inputgroup-addon"><i class="pi pi-search"></i></span>
                        <InputText v-model="filters['global'].value" placeholder="Ara..." />
                    </div>
                </template>

                <Column selectionMode="multiple" style="width: 3rem" :exportable="false"></Column>
                <Column field="id" header="Ürün Id" sortable style="min-width:8rem"></Column>
                <Column field="user.name" header="Üreten" sortable style="min-width:12rem"></Column>
                <Column field="product.product_name" header="Ürün Adı" sortable style="min-width:20rem"></Column>
                <Column field="quantity" header="Üretilen Miktar ( Koli )" sortable style="min-width:10rem"></Column>
                <Column field="machine.machine_name" header="Üretildiği Makina" sortable style="min-width:16rem"></Column>
                <Column header="Üretildiği Vardiya" sortable style="min-width:12rem">
                    <template #body="slotProps">
                        {{ slotProps.data.shift.template.start_time }} - {{ slotProps.data.shift.template.end_time }}
                    </template>
                </Column>
                <Column field="production_date" header="Üretim Tarihi" sortable style="min-width:16rem"></Column>
                <Column :exportable="false" style="min-width:8rem">
                    <template #body="slotProps">
                        <Button icon="pi pi-pencil" outlined rounded class="mr-2" @click="openEditDialog(slotProps.data)" />
                        <Button icon="pi pi-trash" outlined rounded severity="danger" @click="confirmDeleteProduction(slotProps.data)" />
                    </template>
                </Column>
            </DataTable>

        </div>
        <Toast ref="toast" />

        <!-- Üretim Ekleme Diyaloğu -->
        <Dialog v-model:visible="newProductionDialog" :style="{width: '450px'}" header="Yeni Üretim Ekle" :modal="true" class="p-fluid">
            <div class="field">
                <label for="product_id">Ürün</label>
                <Dropdown id="product_id" v-model="production.product_id" :options="products" optionLabel="product_name" optionValue="id" required :invalid="submitted && !production.product_id" />
                <small class="p-error" v-if="submitted && !production.product_id">Ürün seçimi gerekli.</small>
            </div>
            <div class="field">
                <label for="quantity">Üretilen Miktar ( Koli )</label>
                <InputText id="quantity" type="number" v-model.number="production.quantity" required :invalid="submitted && !production.quantity" />
                <small class="p-error" v-if="submitted && !production.quantity">Miktar gereklidir.</small>
            </div>
            <div class="field">
                <label for="machine_id">Makine</label>
                <Dropdown id="machine_id" v-model="production.machine_id" :options="machines" optionLabel="machine_name" optionValue="id" required :invalid="submitted && !production.machine_id" />
                <small class="p-error" v-if="submitted && !production.machine_id">Makine seçimi gereklidir.</small>
            </div>
            <div class="field">
                <label for="shifts_id">Üretildiği Vardiya</label>
                <Dropdown id="shifts_id" v-model="production.shift_id" :options="formattedShifts" optionLabel="label" optionValue="id" required :invalid="submitted && !production.shift_id" />
                <small class="p-error" v-if="submitted && !production.shift_id">Makine seçimi gereklidir.</small>
            </div>
            <div class="field">
                <label for="production_date">Üretim Tarihi</label>
                <Calendar
                    id="production_date"
                    v-model="production.production_date"
                    required
                    :invalid="submitted && !production.production_date"
                />
                <small class="p-error" v-if="submitted && !production.production_date">Tarih seçimi gereklidir.</small>
            </div>
            <div class="field">
                <label for="worker_id">Üreten İşçi</label>
                <Dropdown id="worker_id" v-model="production.worker_id" :options="workers" optionLabel="name" optionValue="id" required :invalid="submitted && !production.worker_id" />
                <small class="p-error" v-if="submitted && !production.worker_id">İşçi seçimi gereklidir.</small>
            </div>
            <template #footer>
                <Button label="İptal" icon="pi pi-times" text @click="hideDialog" />
                <Button label="Kaydet" icon="pi pi-check" text @click="saveProduction" />
            </template>
        </Dialog>

        <!-- Üretim Güncelleme Diyaloğu -->
        <Dialog v-model:visible="editProductionDialog" :style="{width: '450px'}" header="Üretim Güncelle" :modal="true" class="p-fluid">
            <div class="field">
                <label for="product_id">Ürün</label>
                <Dropdown id="product_id" v-model="production.product_id" :options="products" optionLabel="product_name" optionValue="id" required :invalid="submitted && !production.product_id" />
                <small class="p-error" v-if="submitted && !production.product_id">Ürün seçimi gerekli.</small>
            </div>
            <div class="field">
                <label for="quantity">Miktar</label>
                <InputText id="quantity" type="number" v-model.number="production.quantity" required :invalid="submitted && !production.quantity" />
                <small class="p-error" v-if="submitted && !production.quantity">Miktar gereklidir.</small>
            </div>
            <div class="field">
                <label for="machine_id">Makine</label>
                <Dropdown id="machine_id" v-model="production.machine_id" :options="machines" optionLabel="machine_name" optionValue="id" required :invalid="submitted && !production.machine_id" />
                <small class="p-error" v-if="submitted && !production.machine_id">Makine seçimi gereklidir.</small>
            </div>
            <div class="field">
                <label for="shift_id">Üretildiği Vardiya</label>
                <Dropdown id="shift_id" v-model="production.shift_id" :options="formattedShifts" optionLabel="label" optionValue="id" required :invalid="submitted && !production.shift_id" />
                <small class="p-error" v-if="submitted && !production.shift_id">Vardiya seçimi gereklidir.</small>
            </div>
            <div class="field">
                <label for="production_date">Üretim Tarihi</label>
                <Calendar
                    id="production_date"
                    v-model="production.production_date"
                    required
                    :invalid="submitted && !production.production_date"
                />
                <small class="p-error" v-if="submitted && !production.production_date">Tarih seçimi gereklidir.</small>
            </div>
            <div class="field">
                <label for="worker_id">İşçi</label>
                <Dropdown id="worker_id" v-model="production.worker_id" :options="workers" optionLabel="name" optionValue="id" required :invalid="submitted && !production.worker_id" />
                <small class="p-error" v-if="submitted && !production.worker_id">İşçi seçimi gereklidir.</small>
            </div>
            <template #footer>
                <Button label="İptal" icon="pi pi-times" text @click="hideDialog" />
                <Button label="Güncelle" icon="pi pi-check" text @click="updateProduction" />
            </template>
        </Dialog>

        <!-- Üretim Silme Onay Diyaloğu -->
        <Dialog v-model:visible="deleteProductionDialog" :style="{width: '450px'}" header="Onay" :modal="true">
            <div class="confirmation-content">
                <i class="pi pi-exclamation-triangle mr-3" style="font-size: 2rem" />
                <span v-if="production">Silmek istediğiniz <b>{{production.product_name}}</b> olduğuna emin misiniz?</span>
            </div>
            <template #footer>
                <Button label="Hayır" icon="pi pi-times" text @click="deleteProductionDialog = false" />
                <Button label="Evet" icon="pi pi-check" text @click="deleteProduction" />
            </template>
        </Dialog>

        <!-- Seçilen Üretimleri Silme Onay Diyaloğu -->
        <Dialog v-model:visible="deleteProductionsDialog" :style="{width: '450px'}" header="Onay" :modal="true">
            <div class="confirmation-content">
                <i class="pi pi-exclamation-triangle mr-3" style="font-size: 2rem" />
                <span>Seçilen üretimleri silmek istediğinizden emin misiniz?</span>
            </div>
            <template #footer>
                <Button label="Hayır" icon="pi pi-times" text @click="deleteProductionsDialog = false" />
                <Button label="Evet" icon="pi pi-check" text @click="deleteProductions" />
            </template>
        </Dialog>
    </div>
</template>

<script setup>
import { ref, onMounted ,computed} from 'vue';
import axios from 'axios';
import { FilterMatchMode } from 'primevue/api';
import Button from 'primevue/button';
import Toolbar from 'primevue/toolbar';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import InputText from 'primevue/inputtext';
import Dialog from 'primevue/dialog';
import Toast from 'primevue/toast';
import Dropdown from "primevue/dropdown";
import Calendar from "primevue/calendar";



const toast = ref(null);
const productions = ref([]);
const workers = ref([]);
const products = ref();
const machines = ref();
const shifts = ref();
const newProductionDialog = ref(false);
const editProductionDialog = ref(false);
const deleteProductionDialog = ref(false);
const deleteProductionsDialog = ref(false);
const production = ref({});
const selectedProductions = ref([]);
const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
});
const submitted = ref(false);

// Üretimleri alma
const fetchProductions = () => {
    axios.get('/api/productions')
        .then(response => {
            productions.value = response.data;
        })
        .catch(error => {
            console.error("Üretimler alınırken hata:", error);
        });
};

const formattedShifts = computed(() => {
    return shifts.value.map(shift => ({
        id: shift.id,
        label: `${shift.start_time} - ${shift.end_time}` // Burada formatlıyoruz
    }));
});


const getAllWorkers = () => {
    axios.get('/api/getAllWorkers')
        .then(response => {
            workers.value= response.data
        })
        .catch(error => {
        console.error("Üretimler alınırken hata:", error);
    });
}

const getProducts = () => {
    axios.get('/api/products')
        .then(response => {
            products.value=response.data
        })
}
const getShifts = () => {
    axios.get('/api/getShifts')
        .then(response => {
            shifts.value=response.data
        })
}

const getMachines = () => {
    axios.get('/api/machines')
        .then(response => {
            machines.value=response.data;
        })
}

// Sayfa yüklendiğinde üretimleri getir
onMounted(() => {
    fetchProductions();
    getAllWorkers();
    getProducts();
    getMachines();
    getShifts();
});

// Yeni üretim aç
const openNew = () => {
    production.value = {};
    submitted.value = false;
    newProductionDialog.value = true;
};

// Üretim güncelleme aç
const openEditDialog = (data) => {
    production.value = { ...data };
    submitted.value = false;
    editProductionDialog.value = true;
};

// Diyaloğu kapat
const hideDialog = () => {
    newProductionDialog.value = false;
    editProductionDialog.value = false;
    submitted.value = false;
};

// Üretim kaydet
const saveProduction = () => {
    submitted.value = true;
    console.log('if dışı')
    if (production.value.product_id && production.value.quantity && production.value.machine_id && production.value.worker_id) {
        console.log('if içi')
        axios.post('/api/productions/admin', production.value)
            .then(() => {
                toast.value.add({ severity: 'success', summary: 'Başarılı', detail: 'Üretim başarıyla eklendi', life: 3000 });
                fetchProductions();
            })
            .finally(() => {
                hideDialog();
            });
    }
};

// Üretimi güncelle
const updateProduction = () => {
    submitted.value = true;

    if (production.value.product_id && production.value.quantity && production.value.machine_id && production.value.worker_id) {
        axios.put(`/api/productions/${production.value.id}`, production.value)
            .then(() => {
                toast.value.add({ severity: 'success', summary: 'Başarılı', detail: 'Üretim başarıyla güncellendi', life: 3000 });
                fetchProductions();
            })
            .finally(() => {
                hideDialog();
            });
    }
};

// Üretimi silme onayı
const confirmDeleteProduction = (prod) => {
    production.value = { ...prod };
    deleteProductionDialog.value = true;
};

// Üretimi sil
const deleteProduction = () => {
    axios.delete(`/api/productions/${production.value.id}`)
        .then(() => {
            toast.value.add({ severity: 'success', summary: 'Başarılı', detail: 'Üretim başarıyla silindi', life: 3000 });
            fetchProductions();
        })
        .finally(() => {
            deleteProductionDialog.value = false;
            production.value = {};
        });
};

// Seçilen üretimleri silme onayı
const confirmDeleteSelected = () => {
    deleteProductionsDialog.value = true;
};

const exportCSV = () => {
    axios.get('/api/production-export', { responseType: 'blob' })
        .then(response => {
            const url = window.URL.createObjectURL(new Blob([response.data]));
            const link = document.createElement('a');
            link.href = url;
            link.setAttribute('download', 'production.xlsx');
            document.body.appendChild(link);
            link.click();
        })
        .catch(error => {
            console.error('Export failed:', error);
        });
};

// Seçilen üretimleri sil
const deleteSelectedProductions = () => {
    const ids = selectedProductions.value.map(prod => prod.id);
    axios.delete('/api/productions', { data: { ids } })
        .then(() => {
            toast.value.add({ severity: 'success', summary: 'Başarılı', detail: 'Seçilen üretimler başarıyla silindi', life: 3000 });
            fetchProductions();
        })
        .finally(() => {
            deleteProductionsDialog.value = false;
        });
};

</script>

<style scoped>
/* Stil ayarları buraya eklenebilir */
</style>
