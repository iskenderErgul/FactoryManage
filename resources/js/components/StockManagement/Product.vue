<template>
    <div>
        <div class="card">
            <Toolbar class="mb-4">
                <template #start>
                    <Button label="Yeni" icon="pi pi-plus" severity="success" class="mr-2" @click="openNew" />
                    <Button label="Sil" icon="pi pi-trash" severity="danger" @click="confirmDeleteSelected" :disabled="!selectedProducts || !selectedProducts.length" />
                </template>
                <template #end>
                    <FileUpload mode="basic" accept="image/*" :maxFileSize="1000000" label="İçe Aktar" chooseLabel="İçe Aktar" class="mr-2 inline-block" />
                    <Button label="Dışa Aktar" icon="pi pi-upload" severity="help" @click="exportCSV" />
                </template>
            </Toolbar>
            <DataTable ref="dt" :value="products" v-model:selection="selectedProducts" dataKey="id"
                       :paginator="true" :rows="10" :filters="filters"
                       paginatorTemplate="İlk Sayfa Bağlantısı Önceki Sayfa Bağlantısı Sayfa Bağlantıları Sonraki Sayfa Bağlantısı Mevcut Sayfa Raporu Sayfa Başına Satır Aşağı Aşağı"
                       :rowsPerPageOptions="[5,10,25]"
                       currentPageReportTemplate="Mevcut {first} ile {last} arasında, toplam {totalRecords} ürün">
                <template #header>
                    <div class="p-inputgroup">
                        <span class="p-inputgroup-addon"><i class="pi pi-search"></i></span>
                        <InputText v-model="filters['global'].value" placeholder="Ara..." />
                    </div>
                </template>
                <Column selectionMode="multiple" style="width: 2rem" :exportable="false"></Column>
                <Column field="id" header="Ürün ID" sortable style="min-width:8rem"></Column>
                <Column field="product_name" header="Ürün Adı" sortable style="min-width:10rem"></Column>
                <Column field="product_type" header="Ürün Türü" sortable style="min-width:8rem"></Column>
                <Column field="production_cost" header="Maliyet ( TL )" sortable style="min-width:8rem"></Column>
                <Column field="stock_quantity" header="Stok ( Koli )" sortable style="min-width:8rem"></Column>
                <Column field="description" header="Açıklama" sortable style="min-width:12rem"></Column>
                <Column :exportable="false" style="min-width:8rem">
                    <template #body="slotProps">
                        <Button icon="pi pi-pencil" outlined rounded class="mr-2" @click="editProduct(slotProps.data)" />
                        <Button icon="pi pi-trash" outlined rounded severity="danger" @click="confirmDeleteProduct(slotProps.data)" />
                    </template>
                </Column>
            </DataTable>
        </div>
        <Toast ref="toast" />

        <!-- Ürün Detayları Diyaloğu -->
        <Dialog v-model:visible="productDialog" :style="{width: '450px'}" header="Ürün Detayları" :modal="true" class="p-fluid">
            <div class="field">
                <label for="productName">Ürün Adı</label>
                <InputText id="productName" v-model.trim="product.product_name" required :invalid="submitted && !product.product_name" />
                <small class="p-error" v-if="submitted && !product.product_name">Ürün adı zorunludur.</small>
            </div>
            <div class="field">
                <label for="productType">Ürün Türü</label>
                <InputText id="productType" v-model.trim="product.product_type" required :invalid="submitted && !product.product_type" />
                <small class="p-error" v-if="submitted && !product.product_type">Ürün türü zorunludur.</small>
            </div>
            <div class="field">
                <label for="productionCost">Üretim Maliyeti</label>
                <InputText id="productionCost" v-model.number="product.production_cost" required :invalid="submitted && !product.production_cost" />
                <small class="p-error" v-if="submitted && !product.production_cost">Üretim maliyeti zorunludur.</small>
            </div>
            <div class="field">
                <label for="stockQuantity">Stok Miktarı</label>
                <InputText id="stockQuantity" v-model.number="product.stock_quantity" required :invalid="submitted && !product.stock_quantity" />
                <small class="p-error" v-if="submitted && !product.stock_quantity">Stok miktarı zorunludur.</small>
            </div>
            <div class="field">
                <label for="description">Açıklama</label>
                <InputText id="description" v-model.trim="product.description" />
            </div>
            <template #footer>
                <Button label="İptal" icon="pi pi-times" text @click="hideDialog" />
                <Button label="Kaydet" icon="pi pi-check" text @click="saveProduct" />
            </template>
        </Dialog>

        <Dialog v-model:visible="deleteProductDialog" :style="{width: '450px'}" header="Onayla" :modal="true">
            <div class="confirmation-content">
                <i class="pi pi-exclamation-triangle mr-3" style="font-size: 2rem" />
                <span v-if="product">Silmek istediğinize emin misiniz <b>{{product.product_name}}</b>?</span>
            </div>
            <template #footer>
                <Button label="Hayır" icon="pi pi-times" text @click="deleteProductDialog = false" />
                <Button label="Evet" icon="pi pi-check" text @click="deleteProduct" />
            </template>
        </Dialog>

        <!-- Seçilen Ürünleri Silme Onay Diyaloğu -->
        <Dialog v-model:visible="deleteProductsDialog" :style="{width: '450px'}" header="Onayla" :modal="true">
            <div class="confirmation-content">
                <i class="pi pi-exclamation-triangle mr-3" style="font-size: 2rem" />
                <span>Seçilen ürünleri silmek istediğinize emin misiniz?</span>
            </div>
            <template #footer>
                <Button label="Hayır" icon="pi pi-times" text @click="deleteProductsDialog = false" />
                <Button label="Evet" icon="pi pi-check" text @click="deleteSelectedProducts" />
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
const products = ref([]);
const productDialog = ref(false);
const deleteProductDialog = ref(false);
const deleteProductsDialog = ref(false);
const product = ref({});
const selectedProducts = ref([]);
const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
});
const submitted = ref(false);

const fetchProducts = () => {
    axios.get('/api/products')
        .then(response => {
            products.value = response.data;
        })
        .catch(error => {
            console.error("Ürünleri getirirken hata:", error);
        });
};

onMounted(() => {
    fetchProducts();
});

const openNew = () => {
    product.value = {};
    submitted.value = false;
    productDialog.value = true;
};

const hideDialog = () => {
    productDialog.value = false;
    submitted.value = false;
};

const saveProduct = () => {
    submitted.value = true;

    if (product.value.product_name) {
        if (product.value.id) {
            axios.put(`/api/products/${product.value.id}`, product.value)
                .then(() => {
                    toast.value.add({ severity: 'success', summary: 'Başarılı', detail: 'Ürün başarıyla güncellendi', life: 3000 });
                    fetchProducts();
                });
        } else {
            axios.post('/api/products', product.value)
                .then(() => {
                    toast.value.add({ severity: 'success', summary: 'Başarılı', detail: 'Ürün başarıyla eklendi', life: 3000 });
                    fetchProducts();
                });
        }
        productDialog.value = false;
        product.value = {};
    } else {
        toast.value.add({ severity: 'error', summary: 'Hata', detail: 'Ürün adı zorunludur', life: 3000 });
    }
};

const confirmDeleteProduct = (productToDelete) => {
    product.value = { ...productToDelete };
    deleteProductDialog.value = true;
};

const deleteProduct = () => {
    axios.delete(`/api/products/${product.value.id}`)
        .then(() => {
            toast.value.add({ severity: 'success', summary: 'Başarılı', detail: 'Ürün başarıyla silindi', life: 3000 });
            fetchProducts();
        });

    deleteProductDialog.value = false;
    product.value = {};
};

const confirmDeleteSelected = () => {
    deleteProductsDialog.value = true;
};

const deleteSelectedProducts = () => {
    const ids = selectedProducts.value.map(product => product.id);
    axios.delete('/api/products', { data: { ids } })
        .then(() => {
            toast.value.add({ severity: 'success', summary: 'Başarılı', detail: 'Seçilen ürünler başarıyla silindi', life: 3000 });
            fetchProducts();
        });

    deleteProductsDialog.value = false;
    selectedProducts.value = [];
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
