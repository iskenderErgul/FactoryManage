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
                <Column field="customer.name" header="Müşteri Adı" sortable style="min-width:10rem"></Column>
                <Column field="sale_date" header="Satış Tarihi" sortable style="min-width:10rem"></Column>
                <Column :exportable="false" style="min-width:8rem">
                    <template #body="slotProps">
                        <Button icon="pi pi-info-circle" outlined rounded class="mr-2" @click="openSaleDetailDialog(slotProps.data)" />
                        <Button icon="pi pi-pencil" outlined rounded class="mr-2" @click="editSale(slotProps.data)" />
                        <Button icon="pi pi-trash" outlined rounded severity="danger" @click="confirmDeleteSale(slotProps.data)" />
                    </template>
                </Column>
            </DataTable>
        </div>
        <Toast ref="toast" />

        <!-- Satış Detayları Diyaloğu -->

        <Dialog v-model:visible="addSaleDialog" maximizable modal header="Yeni Satış Ekle" :style="{ width: '50rem' }" :breakpoints="{ '1199px': '75vw', '575px': '90vw' }">
            <div class="p-fluid">
                <!-- Müşteri Bilgileri -->
                <h3>Müşteri Bilgileri</h3>
                <div class="p-field">
                    <label for="customerSelect">Müşteri Seç:</label>
                    <Dropdown id="customerSelect" v-model="selectedCustomer" :options="customers" optionLabel="name" placeholder="Müşteri Seçin" />
                </div>

                <div class="p-field">
                    <label for="saleDate">Satış Tarihi:</label>
                    <InputText id="saleDate" v-model="saleDate" />
                </div>

                <!-- Ürün Ekleme -->
                <h3>Ürün Ekle</h3>
                <div class="product-selection">
                    <div class="p-field">
                        <label for="productSelect">Ürün Seç:</label>
                        <Dropdown id="productSelect" v-model="selectedProduct" :options="products" optionLabel="product_name" placeholder="Ürün Seçin" />

                    </div>
                    <div class="p-field">
                        <label for="productQuantity">Miktar:</label>
                        <InputText id="productQuantity" v-model.number="productQuantity" type="number" min="1" />
                    </div>
                    <div class="p-field">
                        <label for="productPrice">Birim Fiyat (TL):</label>
                        <InputText id="productPrice" v-model.number="productPrice" type="number" min="0" />
                    </div>
                    <Button label="Ekle" @click="addProductToSale" />
                </div>

                <!-- Satış Ürünleri Tablosu -->
                <h3>Satış Ürünleri</h3>
                <DataTable :value="saleProducts" :paginator="true" :rows="5">
                    <Column field="product_name" header="Ürün Adı" sortable></Column>
                    <Column field="product_type" header="Ürün Türü" sortable></Column>
                    <Column field="quantity" header="Miktar" sortable></Column>
                    <Column field="price" header="Birim Fiyat (TL)" sortable></Column>
                    <Column field="total_price" header="Toplam (TL)" sortable></Column>
                </DataTable>

                <div class="total-summary" style="text-align: right; margin-top: 20px;">
                    <strong style="font-size: 1.5em; font-weight: bold;">Genel Toplam (TL): {{ calculateTotalPrice(saleProducts) }}</strong>
                </div>

                <div class="p-field" style="text-align: right; margin-top: 20px;">
                    <Button label="Kaydet" @click="saveSale" />
                </div>
            </div>
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
        <Dialog v-model:visible="detailSaleDialog" maximizable modal header="Satış Detayları" :style="{ width: '50rem' }" :breakpoints="{ '1199px': '75vw', '575px': '90vw' }">
            <div class="p-fluid">
                <h3>Müşteri Bilgileri</h3>
                <div class="p-field">
                    <label for="customerName">Ad:</label>
                    <InputText id="customerName" v-model="selectedSales.customer.name" readonly />
                </div>
                <div class="p-field">
                    <label for="customerEmail">Email:</label>
                    <InputText id="customerEmail" v-model="selectedSales.customer.email" readonly />
                </div>
                <div class="p-field">
                    <label for="customerPhone">Telefon:</label>
                    <InputText id="customerPhone" v-model="selectedSales.customer.phone" readonly />
                </div>
                <div class="p-field">
                    <label for="customerAddress">Adres:</label>
                    <InputText id="customerAddress" v-model="selectedSales.customer.address" readonly />
                </div>

                <div class="p-field">
                    <label for="saleDate">Satış Tarihi:</label>
                    <InputText id="saleDate" v-model="selectedSales.sale_date" readonly />
                </div>

                <h3>Satış Ürünleri</h3>
                <DataTable :value="selectedSales.products" :paginator="true" :rows="5">
                    <Column field="product_name" header="Ürün Adı" sortable></Column>
                    <Column field="product_type" header="Ürün Türü" sortable></Column>
                    <Column field="pivot.quantity" header="Miktar" sortable></Column>
                    <Column field="pivot.price" header="Birim Fiyat (TL)" sortable></Column>
                    <Column field="total_price" header="Toplam (TL)" sortable=""></Column>

                </DataTable>
                <div class="total-summary" style="text-align: right; margin-top: 20px;">
                    <strong style="font-size: 1.5em; font-weight: bold;">Genel Toplam (TL): {{ calculateTotalPrice(selectedSales.products) }}</strong>
                </div>
            </div>
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

const customers = ref([]);
const products = ref([]);
const toast = ref(null);
const sales = ref([]);
const saleDialog = ref(false);
const addSaleDialog = ref(false);
const detailSaleDialog = ref(false);
const deleteSaleDialog = ref(false);
const deleteSalesDialog = ref(false);
const sale = ref({});
const selectedSales = ref([]);
const submitted = ref(false);


const selectedCustomer = ref(null);
const selectedProduct = ref(null);
const productQuantity = ref(1);
const productPrice = ref();
const saleProducts = ref([]);
const saleDate = ref('');

const fetchSales = () => {
    axios.get('/api/sales')
        .then(response => {
            sales.value = response.data;
        })
        .catch(error => {
            console.error("Satışları getirirken hata:", error);
        });
};
const fetchCustomers = () => {
    axios.get('/api/customers')
        .then(response => {
            customers.value = response.data;
        })
        .catch(error => {
            console.error("Müşterileri getirirken hata:", error);
        });
};
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
    fetchSales();
    fetchCustomers();
    fetchProducts();
});

const openNew = () => {
    sale.value = {};
    submitted.value = false;
    saleDialog.value = true;
    addSaleDialog.value = true;

};

const openSaleDetailDialog = (data) => {
    selectedSales.value = data;
    detailSaleDialog.value = true;
}
// Toplam Fiyat Hesaplama Fonksiyonu
const calculateTotalPrice = (rowData) => {

    return rowData.reduce((total, product) => {
        return total + product.total_price; // Her ürünün total_price'ını ekle
    }, 0);
};

const saveSale = () => {
    submitted.value = true;

    if (selectedCustomer && saleProducts.value.length > 0) {
        const saleData = {
            customer_id: selectedCustomer.id,
            sale_date: saleDate.value,
            products: saleProducts.value,
        };

        axios.post('/api/sales', saleData)
            .then(() => {
                toast.value.add({ severity: 'success', summary: 'Başarılı', detail: 'Satış başarıyla eklendi', life: 3000 });
                fetchSales();
            });
        addSaleDialog.value = false;
    }
};

const addProductToSale = () => {
    console.log('Seçilen Ürün:', selectedProduct); // Kontrol için log ekleyin
    console.log('Seçilen Ürün Değeri:', selectedProduct.value); // Proxy değerini kontrol edin

    if (selectedProduct && selectedProduct.value && productQuantity.value > 0 && productPrice.value >= 0) {
        const product = {
            ...selectedProduct.value, // Burada _value kullanın
            quantity: productQuantity.value,
            price : parseFloat(productPrice.value),
            total_price: parseFloat(productPrice.value) * productQuantity.value
        };
        console.log('product', product);
        saleProducts.value.push(product);
        console.log('Satış Ürünleri:', saleProducts.value);

        // Değişkenleri sıfırla
        selectedProduct.value = null;
        productQuantity.value = 1;
        productPrice.value = '';
    } else {
        alert("Lütfen tüm alanları doldurun.");
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
