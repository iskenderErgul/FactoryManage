<template>
    <div>
        <div class="card">
            <Toolbar class="mb-4">
                <template #start>
                    <Button label="Yeni Satış" icon="pi pi-plus" severity="success" class="mr-2" @click="openNew" />
                    <Button label="Sil" icon="pi pi-trash" severity="danger" @click="confirmDeleteSelected" :disabled="!selectedCustomerTransaction || !selectedCustomerTransaction.length" />
                </template>
                <template #end>
                    <Button label="Dışa Aktar" icon="pi pi-upload" severity="help" @click="exportCSV" />
                </template>
            </Toolbar>
            <DataTable
                :value="customers"
                v-model:selection="selectedCustomerTransaction"
                dataKey="id"
                :paginator="true"
                :rows="10"
                :rowsPerPageOptions="[5, 10, 25]"
                currentPageReportTemplate="Mevcut {first} ile {last} arasında, toplam {totalRecords} satış">
                <Column selectionMode="multiple" style="width: 2rem" :exportable="false"></Column>
                <Column field="name" header="Müşteri Adı" sortable style="min-width:10rem"></Column>
                <Column :exportable="false" style="min-width:8rem">
                    <template #body="slotProps">
                        <Button icon="pi pi-info-circle" outlined rounded class="mr-2" @click="openSaleDetailDialog(slotProps.data)" />
                        <Button icon="pi pi-pencil" outlined rounded class="mr-2" @click="openUpdateCustomerTransactionDialog(slotProps.data)" />
                        <Button icon="pi pi-trash" outlined rounded class="mr-2" severity="danger" @click="confirmDeleteSale(slotProps.data)" />
                        <Button icon="pi pi-print" outlined rounded  severity="info"   @click="openPrintDailog(slotProps.data)" />
                    </template>
                </Column>
            </DataTable>

        </div>
        <Toast ref="toast" />

        <!-- Satış Detayları Diyaloğu -->
        <Dialog v-model:visible="updateCustomerTransactionDialog" maximizable modal header="Müşteri İşlemleri Düzenle" :style="{ width: '50rem' }" :breakpoints="{ '1199px': '75vw', '575px': '90vw' }">
            <div class="p-fluid">
                <!-- Müşteri Bilgileri -->
                <h3 class="text-center">Müşteri Bilgileri</h3>
                <div class="p-field">
                    <label for="customerName">Ad:</label>
                    <InputText id="customerName" v-model="selectedCustomerTransaction.name" readonly />
                </div>
                <div class="p-field">
                    <label for="customerEmail">Email:</label>
                    <InputText id="customerEmail" v-model="selectedCustomerTransaction.email" readonly />
                </div>
                <div class="p-field">
                    <label for="customerPhone">Telefon:</label>
                    <InputText id="customerPhone" v-model="selectedCustomerTransaction.phone" readonly />
                </div>
                <div class="p-field">
                    <label for="customerAddress">Adres:</label>
                    <InputText id="customerAddress" v-model="selectedCustomerTransaction.address" readonly />
                </div>

                <!-- İşlemler Tablosu -->
                <h3 class="text-center">İşlem Geçmişi</h3>
                <DataTable :value="customerTransactions">
                    <Column field="date" header="Tarih" sortable></Column>
                    <Column field="description" header="Açıklama" sortable></Column>
                    <Column field="type" header="Tür" sortable></Column>
                    <Column field="amount" header="Tutar (TL)" sortable></Column>
                    <Column :header="''" style="width: 8rem">
                        <template #body="slotProps">
                            <Button
                                icon="pi pi-pencil"
                                class="p-button-rounded p-button-info mr-2"
                                @click="openEditDialog(slotProps.data)"
                            />
                            <Button
                                icon="pi pi-trash"
                                class="p-button-rounded p-button-danger"
                                @click="removeTransaction(slotProps.data)"
                            />
                        </template>
                    </Column>
                </DataTable>

                <div class="total-summary" style="text-align: right; margin-top: 20px;">
                    <p><strong>Toplam Borç:</strong> {{ formatAmount(totalDebt(customerTransactions)) }} TL</p>
                    <p><strong>Toplam Ödeme:</strong> {{ formatAmount(totalCredit(customerTransactions)) }} TL</p>
                    <p><strong style="font-size: 1.5em; font-weight: bold;">Genel Toplam:</strong> {{ formatSignedTotal(calculateTotalAmount(customerTransactions)) }} TL</p>
                </div>

                <div class="p-field" style="text-align: right; margin-top: 20px;">
                    <Button label="Kaydet" @click="" />
                </div>
            </div>

            <Dialog header="İşlem Düzenle" v-model:visible="isEditDialogVisible" :modal="true" :closable="false">
                <div class="p-fluid">
                    <!-- İşlem Türü -->
                    <div class="p-field">
                        <label for="type">İşlem Türü</label>
                        <Dropdown
                            id="type"
                            v-model="editingTransaction.type"
                            :options="[
                    { label: 'Borç', value: 'borç' },
                    { label: 'Ödeme', value: 'ödeme' }
                ]"
                            optionLabel="label"
                            optionValue="value"
                            placeholder="İşlem Türü Seçin"
                        />
                    </div>

                    <!-- Tarih -->
                    <div class="p-field">
                        <label for="date">Tarih</label>
                        <Calendar id="date" v-model="editingTransaction.date" />
                    </div>

                    <!-- Tutar -->
                    <div class="p-field">
                        <label for="amount">Tutar (TL)</label>
                        <InputNumber
                            id="amount"
                            v-model="editingTransaction.amount"
                            mode="currency"
                            currency="TRY"
                            locale="tr-TR"
                        />
                    </div>

                    <!-- Açıklama -->
                    <div class="p-field">
                        <label for="description">Açıklama</label>
                        <InputText id="description" v-model="editingTransaction.description" />
                    </div>
                </div>

                <!-- Footer -->
                <template #footer>
                    <Button label="İptal" icon="pi pi-times" class="p-button-text" @click="isEditDialogVisible = false" />
                    <Button label="Kaydetss" icon="pi pi-check" class="p-button-text" @click="saveEdit" />
                </template>
            </Dialog>

        </Dialog>

        <Dialog v-model:visible="addCustomerTransactionDialog" maximizable modal header="Yeni Satış Ekle" :style="{ width: '50rem' }" :breakpoints="{ '1199px': '75vw', '575px': '90vw' }">
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
                        <InputText id="productPrice" v-model.number="productPrice" type="number" min="0" required/>
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
        <Dialog v-model:visible="deleteCustomerTransactionDialog" :style="{width: '450px'}" header="Onayla" :modal="true">
            <div class="confirmation-content">
                <i class="pi pi-exclamation-triangle mr-3" style="font-size: 2rem" />
                <span v-if="sale">Silmek istediğinize emin misiniz <b>{{ sale.customer_name }}</b>?</span>
            </div>
            <template #footer>
                <Button label="Hayır" icon="pi pi-times" text @click="deleteSaleDialog = false" />
                <Button label="Evet" icon="pi pi-check" text @click="deleteSale" />
            </template>
        </Dialog>
        <Dialog v-model:visible="deleteCustomerTransactionsDialog" :style="{width: '450px'}" header="Onayla" :modal="true">
            <div class="confirmation-content">
                <i class="pi pi-exclamation-triangle mr-3" style="font-size: 2rem" />
                <span>Seçilen satışları silmek istediğinize emin misiniz?</span>
            </div>
            <template #footer>
                <Button label="Hayır" icon="pi pi-times" text @click="deleteSalesDialog = false" />
                <Button label="Evet" icon="pi pi-check" text @click="deleteSelectedSales" />
            </template>
        </Dialog>
        <Dialog v-model:visible="detailCustomerTransactionDialog" maximizable modal header="Satış Detayları" :style="{ width: '70rem' }" :breakpoints="{ '1199px': '75vw', '575px': '90vw' }">
            <div class="p-fluid">
                <h3>Müşteri Bilgileri</h3>
                <div class="p-field">
                    <label for="customerName">Ad:</label>
                    <InputText id="customerName" v-model="selectedCustomerTransaction.name" readonly />
                </div>
                <div class="p-field">
                    <label for="customerEmail">Email:</label>
                    <InputText id="customerEmail" v-model="selectedCustomerTransaction.email" readonly />
                </div>
                <div class="p-field">
                    <label for="customerPhone">Telefon:</label>
                    <InputText id="customerPhone" v-model="selectedCustomerTransaction.phone" readonly />
                </div>
                <div class="p-field">
                    <label for="customerAddress">Adres:</label>
                    <InputText id="customerAddress" v-model="selectedCustomerTransaction.address" readonly />
                </div>

                <h3>İşlem Geçmişi</h3>
                <DataTable :value="selectedCustomerTransaction.transactions" :paginator="true" :rows="5">
                    <Column field="date" header="İşlem Tarihi" sortable></Column>
                    <Column field="description" header="Açıklama" sortable></Column>
                    <Column field="type" header="Tür" sortable></Column>
                    <Column field="amount" header="Miktar(TL)" body="formatAmount" sortable></Column>
                </DataTable>

                <div class="total-summary" style="text-align: right; margin-top: 20px;">
                    <p><strong>Toplam Borç:</strong> {{ formatAmount(totalDebt(selectedCustomerTransaction.transactions)) }} TL</p>
                    <p><strong>Toplam Ödeme:</strong> {{ formatAmount(totalCredit(selectedCustomerTransaction.transactions)) }} TL</p>
                    <p><strong style="font-size: 1.5em; font-weight: bold;">Genel Toplam:</strong> {{ formatSignedTotal(calculateTotalAmount(selectedCustomerTransaction.transactions)) }} TL</p>
                </div>
            </div>
        </Dialog>




        <Dialog v-model:visible="printSale" maximizable modal  :style="{ width: '50rem' }" :breakpoints="{ '1199px': '75vw', '575px': '90vw' }">

            <div class="p-8 bg-gray-800 text-white rounded-lg shadow-lg">
                <!-- Fatura Başlık ve Logo -->
                <div class="flex justify-content-between items-center mb-5">

                    <div>
                        <img src="../../../../public/Logo.png" alt="Logo" class="w-6 h-6" />
                    </div>
                    <div class="flex flex-column align-items-center justify-content-center w-4 ">
                        <h2 class="text-3xl font-bold  text-black-alpha-90">Fatura</h2>
                        <p class="text-sm text-gray-400">Fatura No: {{generateRandomNumber() }}</p>
                        <p class="text-sm text-gray-400 mr ">Fatura Tarihi : {{ selectedPrintSales.sale_date }}</p>
                    </div>
                </div>

                <!-- Müşteri Bilgileri -->
                <div class="mb-6">
                    <h3 class="text-xl font-semibold font-bold  text-black-alpha-90">Müşteri Bilgileri</h3>
                    <p class="text-gray-400">Ad: {{ selectedPrintSales.customer.name }}</p>
                    <p class="text-gray-400">Adres: {{ selectedPrintSales.customer.address }}</p>
                    <p class="text-gray-400">E-posta: {{ selectedPrintSales.customer.email }}</p>
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
const addCustomerTransactionDialog = ref(false);
const updateCustomerTransactionDialog = ref(false);
const detailCustomerTransactionDialog = ref(false);
const deleteCustomerTransactionDialog = ref(false);
const deleteCustomerTransactionsDialog = ref(false);
const selectedCustomerTransaction = ref([]);
const submitted = ref(false);
const isEditDialogVisible = ref(false);
const editingTransaction = ref({});
const selectedCustomer = ref(null);
const newTransaction = ref({
    type: '',
    date: '',
    amount: null,
    description: '',
});

const customerTransactions = ref([]);
const saleDate = ref('');
const printSale = ref(false)
const  selectedPrintSales  = ref();


onMounted(() => {
    fetchSales();
    fetchCustomers();
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
    selectedCustomerTransaction.value = data;
    detailCustomerTransactionDialog.value = true;
}

const openUpdateCustomerTransactionDialog = (data) => {

    // Gelen müşteri bilgilerini atama
    selectedCustomerTransaction.value = {
        id: data.id,
        name: data.name,
        email: data.email,
        phone: data.phone,
        address: data.address,
        debt: parseFloat(data.debt),
        transactions: data.transactions.map(transaction => ({
            ...transaction,
            amount: parseFloat(transaction.amount), // Miktarı sayıya çevir
        })),
    };

    // İlk işlem tarihini varsayılan olarak alıyoruz
    saleDate.value = data.transactions[0]?.date || '';

    // Müşteri bilgisi
    selectedCustomer.value = {
        id: data.id,
        name: data.name,
        email: data.email,
        phone: data.phone,
        address: data.address,
    };

    // İşlem detayları (satış ürünleri yerine işlemler)
    customerTransactions.value = data.transactions.map(transaction => ({
        id : transaction.id,
        description: transaction.description, // Açıklama
        type: transaction.type === 'borç' ? 'Borç' : 'Ödeme', // Tür
        amount: transaction.amount, // Miktar
        date: transaction.date, // İşlem tarihi
    }));


    // Dialogu açıyoruz
    updateCustomerTransactionDialog.value = true;
};


const openEditDialog = (transaction) => {
    editingTransaction.value = { ...transaction };
    editingTransaction.value.price = parseFloat(editingTransaction.value.price);
    isEditDialogVisible.value = true;

};

const saveEdit = () => {
    const index = customerTransactions.value.findIndex(t => t.id === editingTransaction.value.id);
    if (index !== -1) {
        // Daha güvenli bir güncelleme yöntemi
        customerTransactions.value.splice(index, 1, { ...editingTransaction.value });
    }
    // Güncel borç, ödeme ve genel toplam hesapla
    const updatedDebt = totalDebt(customerTransactions.value);
    const updatedCredit = totalCredit(customerTransactions.value);
    const updatedBalance = calculateTotalAmount(customerTransactions.value);


    isEditDialogVisible.value = false; // Dialogu kapat
};






const removeTransaction = (transaction) => {
    customerTransactions.value = customerTransactions.value.filter(t => t.id !== transaction.id);
};


const calculateTotalAmount = (transactions) => {
    let totalDebt = 0;

    transactions.forEach((transaction) => {
        const type = transaction.type.toLowerCase(); // Türü küçük harfe çevir
        if (type === "borç") {
            totalDebt += parseFloat(transaction.amount); // Borç ekle
        } else if (type === "ödeme") {
            totalDebt -= parseFloat(transaction.amount); // Ödemeyi borçtan çıkar
        }
    });

    return totalDebt; // Genel toplamı döndür
};

// Sayıları formatlamak için
const formatSignedTotal = (value) => {
    const formattedValue = formatAmount(Math.abs(value));
    return value > 0 ? `-${formattedValue}` : `+${formattedValue}`;
};

// Format edilen değer
const formatAmount = (value) => {
    return new Intl.NumberFormat("tr-TR", { minimumFractionDigits: 0 }).format(value);
};

const totalDebt = (transactions) => {
    return transactions
        .filter((transaction) => transaction.type.toLowerCase() === "borç")
        .reduce((sum, transaction) => sum + parseFloat(transaction.amount), 0);
};

const totalCredit = (transactions) => {
    return transactions
        .filter((transaction) => transaction.type.toLowerCase() === "ödeme")
        .reduce((sum, transaction) => sum + parseFloat(transaction.amount), 0);
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

        axios.put(`/api/sales/${selectedCustomerTransaction.value.id}`, saleData)
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
    const ids = selectedCustomerTransaction.value.map(sale => sale.id);
    axios.delete('/api/sales', { data: { ids } })
        .then(() => {
            toast.value.add({ severity: 'success', summary: 'Başarılı', detail: 'Seçilen satışlar başarıyla silindi', life: 3000 });
            fetchSales();
        });

    deleteSalesDialog.value = false;
    selectedCustomerTransaction.value = [];
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
