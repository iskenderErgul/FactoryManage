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
            <DataTable
                :value="sales"
                v-model:selection="selectedSales"
                dataKey="id"
                :paginator="true"
                :rows="10"
                :rowsPerPageOptions="[5, 10, 25]"
                currentPageReportTemplate="Mevcut {first} ile {last} arasında, toplam {totalRecords} satış">
                <Column selectionMode="multiple" style="width: 2rem" :exportable="false"></Column>
                <Column field="id" header="Satış ID" sortable style="min-width:8rem"></Column>
                <Column field="customer.name" header="Müşteri Adı" sortable style="min-width:10rem"></Column>
                <Column field="sale_date" header="Satış Tarihi" sortable style="min-width:10rem"></Column>
                <Column :exportable="false" style="min-width:8rem">
                    <template #body="slotProps">
                        <Button icon="pi pi-info-circle" outlined rounded class="mr-2" @click="openSaleDetailDialog(slotProps.data)" />
                        <Button icon="pi pi-pencil" outlined rounded class="mr-2" @click="openUpdateSaleDialog(slotProps.data)" />
                        <Button icon="pi pi-trash" outlined rounded class="mr-2" severity="danger" @click="confirmDeleteSale(slotProps.data)" />
                        <Button icon="pi pi-print" outlined rounded  severity="info"   @click="openPrintDailog(slotProps.data)" />
                    </template>
                </Column>
            </DataTable>

        </div>
        <Toast ref="toast" />

        <!-- Satış Detayları Diyaloğu -->
        <Dialog v-model:visible="updateSaleDialog" maximizable modal header="Yeni Satış Ekle" :style="{ width: '50rem' }" :breakpoints="{ '1199px': '75vw', '575px': '90vw' }">
            <div class="p-fluid">
                <!-- Müşteri Bilgileri -->
                <h3>Müşteri Bilgileri</h3>
                <div class="p-field">
                    <label for="customerSelect">Müşteri Seç:</label>
                    <Dropdown id="customerSelect" v-model="selectedCustomer" :options="customers" optionLabel="name" placeholder="Müşteri Seçin" />
                </div>

                <div class="p-field">
                    <label for="saleDate">Satış Tarihi:</label>
<!--                    <InputText id="saleDate" v-model="saleDate" />-->
                    <Calendar
                        id="saleDate"
                        v-model="saleDate"
                        required
                        :invalid="submitted && !saleDate"
                    />
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

                        <InputNumber
                            id="productPrice"
                            v-model.number="productPrice"
                            type="number"
                            min="0"
                            placeholder="Fiyat Girin"
                            required
                        />
                    </div>
                    <Button label="Ekle" @click="addUpdatingProductToSale" />
                </div>

                <!-- Satış Ürünleri Tablosu -->
                <h3>Satış Ürünleri</h3>
                <!-- DataTable: Satır düzenleme ve silme işlemleri için düğmeler eklendi -->

                <DataTable :value="saleProducts" :paginator="true" :rows="5">
                    <Column field="product_name" header="Ürün Adı" sortable></Column>
                    <Column field="product_type" header="Ürün Türü" sortable></Column>
                    <Column field="pivot.quantity" header="Miktar" sortable></Column>
                    <Column field="pivot.price" header="Birim Fiyat (TL)" sortable></Column>
                    <Column field="total_price" header="Toplam (TL)" sortable></Column>

                    <!-- Düzenle ve Sil Butonları Sütunu -->
                    <Column :header="''" style="width: 8rem">
                        <template #body="slotProps">
                            <Button icon="pi pi-pencil" class="p-button-rounded p-button-info mr-2"
                                    @click="openEditDialog(slotProps.data)" />
                            <Button icon="pi pi-trash" class="p-button-rounded p-button-danger"
                                    @click="removeProductFromSale(slotProps.data)" />
                        </template>
                    </Column>
                </DataTable>
                <div class="total-summary" style="text-align: right; margin-top: 20px;">
                    <strong style="font-size: 1.5em; font-weight: bold;">Genel Toplam (TL): {{ calculateTotalPrice(saleProducts) }}</strong>
                </div>

                <div class="p-field" style="text-align: right; margin-top: 20px;">
                    <Button label="Kaydet" @click="updateSale" />
                </div>

                <!-- Düzenleme Dialogu -->
                <Dialog header="Ürün Düzenle" v-model:visible="isEditDialogVisible" :modal="true" :closable="false">

                    <div class="p-fluid">
                        <div class="p-field">
                            <label for="product_name">Ürün Adı</label>
                            <InputText v-model="editingProduct.product_name" />
                        </div>
                        <div class="p-field">
                            <label for="product_type">Ürün Türü</label>
                            <InputText v-model="editingProduct.product_type" />
                        </div>
                        <div class="p-field">
                            <label for="quantity">Miktar</label>
                            <InputNumber v-model="editingProduct.pivot.quantity" mode="decimal" />
                        </div>
                        <div class="p-field">
                            <label for="price">Birim Fiyat (TL)</label>
                            <InputNumber v-model="editingProduct.pivot.price" mode="currency" currency="TRY" locale="tr-TR" />
                        </div>
                    </div>

                    <template #footer>
                        <Button label="İptal" icon="pi pi-times" class="p-button-text" @click="isEditDialogVisible = false" />
                        <Button label="Kaydet" icon="pi pi-check" class="p-button-text" @click="saveEdit" />
                    </template>
                </Dialog>
            </div>
        </Dialog>
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

                    <Calendar
                        id="saleDate"
                        v-model="saleDate"
                        required
                        :invalid="submitted && !saleDate"
                    />
                </div>

                <!-- Ürün Ekleme -->
                <h3>Ürün Ekle</h3>
                <div class="product-selection">
                    <div class="p-field">
                        <label for="productSelect">Ürün Seç:</label>
                        <Dropdown id="productSelect" v-model="selectedProduct" :options="products" optionLabel="product_name" placeholder="Ürün Seçin"/>

                    </div>
                    <div class="p-field">
                        <label for="productQuantity">Miktar:</label>
                        <InputText id="productQuantity" v-model.number="productQuantity" type="number" min="1"  required/>
                    </div>
                    <div class="p-field">
                        <label for="productPrice">Birim Fiyat (TL):</label>
                        <InputNumber
                            id="productPrice"
                            v-model.number="productPrice"
                            type="number"
                            min="1"
                            placeholder="Fiyat Girin"
                            required
                        />
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

        <Dialog v-model:visible="printSale" maximizable modal  :style="{ width: '70rem' }" :breakpoints="{ '1199px': '75vw', '575px': '90vw' }">

            <div class="p-8 bg-gray-800 text-white rounded-lg shadow-lg">
                <!-- Fatura Başlık ve Logo -->
                <div class="flex justify-content-between items-center mb-5">

                    <div>
                        <img src="../../../../public/Logo.png" alt="Logo" class="w-6 h-6" />
                    </div>
                    <div class="flex flex-column align-items-center justify-content-center w-4 ">
                        <h2 class="text-3xl font-bold  text-black-alpha-90">Fatura</h2>
                        <p class="text-sm text-gray-400">Fatura No: {{selectedPrintSales.customer_id }}</p>
                        <p class="text-sm text-gray-400 mr ">Fatura Tarihi : {{ selectedPrintSales.sale_date }}</p>
                    </div>
                </div>


                <div class="flex justify-content-between ">
                    <!-- Müşteri Bilgileri -->
                    <div class="mb-6">
                        <h3 class="text-xl font-semibold font-bold  text-black-alpha-90">Müşteri Bilgileri</h3>
                        <p class="text-gray-400">Ad: {{ selectedPrintSales.customer.name }}</p>
                        <p class="text-gray-400">Adres: {{ selectedPrintSales.customer.address }}</p>
                        <p class="text-gray-400">E-posta: {{ selectedPrintSales.customer.email }}</p>
                        <p class="text-gray-400">Telefon: {{ selectedPrintSales.customer.phone }}</p>
                    </div>

                    <div class="mb-6 w-75">
                        <h3 class="text-xl font-semibold font-bold  text-black-alpha-90">Teslim Alan</h3>
                        <p class="text-gray-400">Ad-Soyadı: </p>
                        <p class="text-gray-400">Telefon: </p>
                        <p class="text-gray-400">Tc Kimlik Numarası:</p>
                        <p class="text-gray-400">Araç Plakası:</p>
                        <p class="text-gray-400">İmza:</p>
                    </div>
                </div>


                <!-- Ürün Listesi -->
                <div class="mb-6">
                    <h3 class="text-xl font-bold  font-semibold text-black-alpha-90">Ürünler</h3>
                    <table class="w-full table-auto text-gray-300">
                        <thead>
                        <tr class="border-b border-gray-600">
                            <th class="p-2 text-left">Ürün Adı</th>
                            <th class="p-2 text-left">Miktar</th>
                            <th class="p-2 text-left">Birim Fiyat</th>
                            <th class="p-2 text-left">Toplam</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="(product, index) in selectedPrintSales.products" :key="index" class="border-b border-gray-600">
                            <td class="p-2">{{ product.product_name }}</td>
                            <td class="p-2">{{ product.pivot.quantity }}</td>
                            <td class="p-2">{{ product.pivot.price }}</td>
                            <td class="p-2">{{ (product.pivot.quantity * product.pivot.price)}}</td>
                        </tr>
                        </tbody>
                    </table>
                </div>


                <div class="mt-6 flex flex-column">

                    <div class="flex justify-content-end w-full mb-2">
                        <p class="text-xl font-bold text-left text-black-alpha-90">Toplam Tutar</p>
                        <p class="text-lg ml-5 font-bold text-black-alpha-90">{{ calculateTotalPrice(selectedPrintSales.products) }} TL</p>
                    </div>
                </div>

            </div>


        </Dialog>


    </div>
</template>

<script setup>
import {ref, onMounted} from 'vue';
import axios from 'axios';
import Button from 'primevue/button';
import Toolbar from 'primevue/toolbar';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import InputText from 'primevue/inputtext';
import InputNumber from "primevue/inputnumber";
import Dialog from 'primevue/dialog';
import Toast from 'primevue/toast';
import Dropdown from 'primevue/dropdown';
import Calendar from "primevue/calendar";


const customers = ref([]);
const products = ref([]);
const toast = ref(null);
const sales = ref([]);
const saleDialog = ref(false);
const addSaleDialog = ref(false);
const updateSaleDialog = ref(false);
const detailSaleDialog = ref(false);
const deleteSaleDialog = ref(false);
const deleteSalesDialog = ref(false);
const sale = ref({});
const selectedSales = ref([]);
const submitted = ref(false);
const isEditDialogVisible = ref(false);
const editingProduct = ref({});
const selectedCustomer = ref(null);
const selectedProduct = ref(null);
const productQuantity = ref(1);
const productPrice = ref();
const saleProducts = ref([]);
const saleDate = ref('');
const printSale = ref(false)
const  selectedPrintSales  = ref();


onMounted(() => {
    fetchSales();
    fetchCustomers();
    fetchProducts();
});
const fetchSales = () => {
    axios.get('/api/sales')
        .then(response => {
            sales.value = response.data.reverse();
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

const openPrintDailog = (data) => {
    selectedPrintSales.value = data;
    printSale.value=true;
}

const generateRandomNumber = () => {
    return Math.floor(Math.random() * 1000000); // 0 ile 999999 arasında rastgele sayı
};

const openNew = () => {
    sale.value = {};
    selectedCustomer.value = null;
    saleDate.value = '';
    saleProducts.value = [];
    productQuantity.value = 1;
    productPrice.value = '';


    submitted.value = false;
    saleDialog.value = true;
    addSaleDialog.value = true;

};

const openSaleDetailDialog = (data) => {
    selectedSales.value = data;
    detailSaleDialog.value = true;
}

const openUpdateSaleDialog = (data) => {
    selectedSales.value = data;
    saleDate.value = data.sale_date;
    selectedCustomer.value = data.customer;
    saleProducts.value = data.products.map(product => ({
        ...product,
        total_price: product.pivot.price * product.pivot.quantity,
    }));
    updateSaleDialog.value = true;

}

const openEditDialog = (product) => {
    editingProduct.value = { ...product };
    editingProduct.value.price = parseFloat(editingProduct.value.price);
    isEditDialogVisible.value = true;

};

const saveEdit = () => {
    const index = saleProducts.value.findIndex((p) => p.id === editingProduct.value.id);
    if (index !== -1) {
        // Düzenlenen ürünü güncelle
        saleProducts.value[index] = { ...editingProduct.value };

        // Toplam fiyatı güncelle
        saleProducts.value[index].total_price = saleProducts.value[index].pivot.price * saleProducts.value[index].pivot.quantity;
    }
    isEditDialogVisible.value = false;
};

const addUpdatingProductToSale = () => {
    if (selectedProduct.value && productQuantity.value > 0 && productPrice.value >= 0) {
        const existingProductIndex = saleProducts.value.findIndex(product => product.id === selectedProduct.value.id);

        // Ürün zaten varsa güncelle
        if (existingProductIndex !== -1) {
            const currentProduct = saleProducts.value[existingProductIndex];
            const newQuantity = currentProduct.pivot.quantity + productQuantity.value; // Burayı güncelliyoruz
            const productStock = selectedProduct.value.stock_quantity;

            // Stok kontrolü
            if (newQuantity > productStock) {
                toast.value.add({ severity: 'error', summary: 'Hata', detail: 'Yeterli stok yok! Lütfen daha düşük bir miktar seçin.', life: 3000 });
                return;
            }

            // Mevcut ürün güncelleniyor
            currentProduct.pivot.quantity = newQuantity; // Miktarı güncelliyoruz
            currentProduct.pivot.price = parseFloat(productPrice.value); // Birim fiyatı güncelliyoruz
            currentProduct.total_price = currentProduct.pivot.quantity * currentProduct.pivot.price; // Toplam fiyatı güncelliyoruz

        } else {
            // Ürün yoksa yeni bir ürün ekleniyor
            const product = {
                ...selectedProduct.value,
                pivot: {
                    quantity: productQuantity.value, // Miktarı burada tanımlıyoruz
                    price: parseFloat(productPrice.value) // Birim fiyatı burada tanımlıyoruz
                },
                total_price: productQuantity.value * parseFloat(productPrice.value) // Toplam fiyatı burada hesaplıyoruz
            };
            saleProducts.value.push(product);
        }

        // Seçili ürün ve diğer alanları sıfırlama
        selectedProduct.value = null;
        productQuantity.value = 1;
        productPrice.value = '';
    } else {
        toast.value.add({ severity: 'warn', summary: 'Uyarı', detail: 'Lütfen tüm alanları doldurun.', life: 3000 });
    }
};

const removeProductFromSale = (product) => {
    saleProducts.value = saleProducts.value.filter(item => item.id !== product.id); // Ürünü listeden çıkar
    toast.value.add({ severity: 'info', summary: 'Ürün Silindi', detail: `${product.product_name} başarıyla silindi`, life: 3000 });
};

const calculateTotalPrice = (rowData) => {

    return rowData.reduce((total, product) => {
        return total + product.total_price; // Her ürünün total_price'ını ekle
    }, 0);
};

const saveSale = () => {
    submitted.value = true;
    if (selectedCustomer && saleProducts.value.length > 0) {
        const saleData = {
            customer_id: selectedCustomer.value.id,
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

const updateSale = () => {
    if (selectedCustomer && saleProducts.value.length > 0) {
        const saleData = {
            customer_id: selectedCustomer.value.id,
            sale_date: saleDate.value,
            products: saleProducts.value,
        };

        axios.put(`/api/sales/${selectedSales.value.id}`, saleData)
            .then(() => {
                toast.value.add({ severity: 'success', summary: 'Başarılı', detail: 'Satış başarıyla güncellendi', life: 3000 });
                fetchSales();


                sale.value = {};
                selectedCustomer.value = null;
                saleDate.value = '';
                saleProducts.value = [];
                productQuantity.value = 1;
                productPrice.value = '';
            })
            .catch(error => {
                toast.value.add({ severity: 'error', summary: 'Hata', detail: 'Güncelleme başarısız.', life: 3000 });
            });
        updateSaleDialog.value = false;
    }
};

const addProductToSale = () => {
    if (selectedProduct && selectedProduct.value && productQuantity.value > 0 && productPrice.value > 0) {
        const productStock = selectedProduct.value.stock_quantity;

        if (productQuantity.value > productStock) {
            toast.value.add({ severity: 'error', summary: 'Hata', detail: 'Yeterli stok yok! Lütfen daha düşük bir miktar seçin.', life: 3000 });
            return;
        }

        const existingProductIndex = saleProducts.value.findIndex(product => product.id === selectedProduct.value.id);

        if (existingProductIndex !== -1) {
            saleProducts.value[existingProductIndex].quantity += productQuantity.value;
            saleProducts.value[existingProductIndex].total_price = parseFloat(productPrice.value) * saleProducts.value[existingProductIndex].quantity;
        } else {
            const product = {
                ...selectedProduct.value,
                quantity: productQuantity.value,
                price: parseFloat(productPrice.value),
                total_price: parseFloat(productPrice.value) * productQuantity.value
            };
            saleProducts.value.push(product);
        }
        selectedProduct.value = null;
        productQuantity.value = 1;
        productPrice.value = '';
    } else {
        toast.value.add({ severity: 'warn', summary: 'Uyarı', detail: 'Lütfen tüm alanları doldurun.', life: 3000 });
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
    axios.get('/api/sales-export', { responseType: 'blob' })
        .then(response => {
            const url = window.URL.createObjectURL(new Blob([response.data]));
            const link = document.createElement('a');
            link.href = url;
            link.setAttribute('download', 'sales.xlsx');
            document.body.appendChild(link);
            link.click();
        })
        .catch(error => {
            console.error('Export failed:', error);
        });

    axios.get('/api/sales-product-export', { responseType: 'blob' })
        .then(response => {
            const url = window.URL.createObjectURL(new Blob([response.data]));
            const link = document.createElement('a');
            link.href = url;
            link.setAttribute('download', 'sales-product.xlsx');
            document.body.appendChild(link);
            link.click();
        })
        .catch(error => {
            console.error('Export failed:', error);
        });
};

</script>

<style scoped>
.card {
    margin: 2rem 0;
}
@media print {
    .p-button {
        display: none;
    }
}
</style>
