<template>
    <div>
        <div class="card">
            <Toolbar class="mb-4">
                <template #start>
                    <Button label="Yeni Sipariş" icon="pi pi-plus" severity="success" class="mr-2" @click="openNew" />
                </template>
            </Toolbar>

            <DataTable
                :value="orders"
                v-model:selection="selectedOrders"
                dataKey="id"
                :paginator="true"
                :rows="10"
                :rowsPerPageOptions="[5, 10, 25]"
                currentPageReportTemplate="Mevcut {first} ile {last} arasında, toplam {totalRecords} sipariş">
                <Column selectionMode="multiple" style="width: 2rem" :exportable="false"></Column>
                <Column field="customer.name" header="Müşteri Adı" sortable style="min-width:10rem"></Column>
                <Column field="order_date" header="Sipariş Tarihi" sortable style="min-width:10rem"></Column>
                <Column field="status" header="Sipariş Durumu" sortable style="min-width:10rem"></Column>
                <Column :exportable="false" style="min-width:10rem">
                    <template #body="slotProps">
                        <Button icon="pi pi-info-circle" outlined rounded class="mr-2" @click="openOrderDetailDialog(slotProps.data)" />
                        <Button icon="pi pi-pencil" outlined rounded class="mr-2" @click="openUpdateOrderDialog(slotProps.data)" />
                        <Button icon="pi pi-print" outlined rounded class="mr-2" severity="info" @click="openOrderPrintDialog(slotProps.data)" />
                        <Button icon="pi pi-trash" outlined rounded class="mr-2" severity="danger" @click="confirmDeleteOrder(slotProps.data)" />
                    </template>
                </Column>
            </DataTable>
        </div>

        <Toast ref="toast" />

        <!-- Satış Detayları Diyaloğu -->
        <Dialog v-model:visible="updateOrderDialog" maximizable modal header="Sipariş Güncelle" :style="{ width: '50rem' }" :breakpoints="{ '1199px': '75vw', '575px': '90vw' }">
            <div class="p-fluid">
                <!-- Müşteri Bilgileri -->
                <h3>Müşteri Bilgileri</h3>
                <div class="p-field">
                    <label for="customerSelect">Müşteri Seç:</label>
                    <Dropdown id="customerSelect" v-model="selectedCustomer" :options="customers" optionLabel="name" placeholder="Müşteri Seçin" />
                </div>

                <div class="p-field">
                    <label for="orderDate">Sipariş Tarihi:</label>
                    <Calendar id="orderDate" v-model="orderDate" required :invalid="submitted && !orderDate" />
                </div>

                <div class="p-field">
                    <label for="orderStatus">Sipariş Durumu:</label>
                    <Dropdown id="orderStatus" v-model="selectedStatus" :options="orderStatusOptions" optionLabel="label" placeholder="Durum Seçin" />
                </div>

                <!-- Ürün Ekleme -->
                <h3>Ürün Ekle</h3>
                <div class="product-selection">
                    <div class="p-field mb-3">
                        <label for="productSelect">Ürün Seç:</label>
                        <Dropdown id="productSelect" v-model="selectedProduct" :options="products" optionLabel="product_name" placeholder="Ürün Seçin" />
                    </div>
                    <div class="p-field mb-3">
                        <label for="productQuantity">Miktar:</label>
                        <InputText id="productQuantity" v-model.number="productQuantity" type="number" min="1" />
                    </div>
                    <Button label="Ekle" @click="addUpdatingProductToOrder" />
                </div>

                <!-- Sipariş Ürünleri Tablosu -->
                <h3>Sipariş Ürünleri</h3>
                <DataTable :value="orderProducts" :paginator="true" :rows="5">
                    <Column field="product_name" header="Ürün Adı" sortable></Column>
                    <Column field="product_type" header="Ürün Türü" sortable></Column>
                    <Column field="pivot.quantity" header="Miktar" sortable></Column>
                    <!-- Düzenle ve Sil Butonları Sütunu -->
                    <Column :header="''" style="width: 8rem">
                        <template #body="slotProps">
                            <Button icon="pi pi-pencil" class="p-button-rounded p-button-info mr-2" @click="openEditDialog(slotProps.data)" />
                            <Button icon="pi pi-trash" class="p-button-rounded p-button-danger" @click="removeProductFromOrder(slotProps.data)" />
                        </template>
                    </Column>
                </DataTable>

                <div class="p-field" style="text-align: right; margin-top: 20px;">
                    <Button label="Kaydet" @click="updateOrder" />
                </div>

                <!-- Ürün Düzenleme Dialogu -->
                <Dialog header="Ürün Düzenle" v-model:visible="isEditDialogVisible" :modal="true" :closable="false">
                    <div class="p-fluid">
                        <div class="p-field">
                            <label for="product_name">Ürün Adı</label>
                            <InputText v-model="editingProduct.product_name" />
<!--                            <Dropdown id="productSelect" v-model="editingProduct.product_name" :options="products" optionLabel="product_name" placeholder="Ürün Seçin" />-->
                        </div>

                        <div class="p-field">
                            <label for="quantity">Miktar</label>
                            <InputNumber v-model="editingProduct.pivot.quantity" mode="decimal" />
                        </div>
                    </div>

                    <template #footer>
                        <Button label="İptal" icon="pi pi-times" class="p-button-text" @click="isEditDialogVisible = false" />
                        <Button label="Kaydet" icon="pi pi-check" class="p-button-text" @click="saveEdit" />
                    </template>
                </Dialog>
            </div>
        </Dialog>

        <Dialog v-model:visible="addOrderDialog" maximizable modal header="Yeni Sipariş Ekle" :style="{ width: '50rem' }" :breakpoints="{ '1199px': '75vw', '575px': '90vw' }">
            <div class="p-fluid">
                <!-- Müşteri Bilgileri -->
                <h3>Müşteri Bilgileri</h3>
                <div class="p-field">
                    <label for="customerSelect">Müşteri Seç:</label>
                    <Dropdown id="customerSelect" v-model="selectedCustomer" :options="customers" optionLabel="name" placeholder="Müşteri Seçin" />
                </div>

                <div class="p-field">
                    <label for="orderDate">Sipariş Tarihi:</label>
                    <Calendar id="orderDate" v-model="orderDate" required :invalid="submitted && !orderDate" />
                </div>

                <div class="p-field">
                    <label for="orderStatus">Sipariş Durumu:</label>
                    <Dropdown id="orderStatus" v-model="selectedStatus" :options="orderStatusOptions" optionLabel="label" placeholder="Sipariş Durumu Seçin" />
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
                        <InputText id="productQuantity" v-model.number="productQuantity" type="number" min="1" required />
                    </div>
                    <Button label="Ekle" @click="addProductToOrder" />
                </div>

                <!-- Sipariş Ürünleri Tablosu -->
                <h3>Sipariş Ürünleri</h3>
                <DataTable :value="orderProducts" :paginator="true" :rows="5">
                    <Column field="product_name" header="Ürün Adı" sortable></Column>
                    <Column field="product_type" header="Ürün Türü" sortable></Column>
                    <Column field="pivot.quantity" header="Miktar" sortable></Column>
                </DataTable>

                <div class="p-field" style="text-align: right; margin-top: 20px;">
                    <Button label="Kaydet" @click="saveOrder" />
                </div>
            </div>
        </Dialog>

        <Dialog v-model:visible="deleteOrderDialog" :style="{width: '450px'}" header="Onayla" :modal="true">
            <div class="confirmation-content">
                <i class="pi pi-exclamation-triangle mr-3" style="font-size: 2rem" />
                <span v-if="order">Silmek istediğinize emin misiniz <b>{{ order.customer_name }}</b>?</span>
            </div>
            <template #footer>
                <Button label="Hayır" icon="pi pi-times" text @click="deleteOrderDialog = false" />
                <Button label="Evet" icon="pi pi-check" text @click="deleteOrder" />
            </template>
        </Dialog>

        <Dialog v-model:visible="deleteOrdersDialog" :style="{width: '450px'}" header="Onayla" :modal="true">
            <div class="confirmation-content">
                <i class="pi pi-exclamation-triangle mr-3" style="font-size: 2rem" />
                <span>Seçilen siparişleri silmek istediğinize emin misiniz?</span>
            </div>
            <template #footer>
                <Button label="Hayır" icon="pi pi-times" text @click="deleteOrdersDialog = false" />
                <Button label="Evet" icon="pi pi-check" text @click="deleteSelectedOrders" />
            </template>
        </Dialog>

        <Dialog v-model:visible="detailOrderDialog" maximizable modal header="Sipariş Detayları" :style="{ width: '50rem' }" :breakpoints="{ '1199px': '75vw', '575px': '90vw' }">
            <div class="p-fluid">
                <h3>Müşteri Bilgileri</h3>
                <div class="p-field">
                    <label for="customerName">Ad:</label>
                    <InputText id="customerName" v-model="selectedOrders.customer.name" readonly />
                </div>
                <div class="p-field">
                    <label for="customerEmail">Email:</label>
                    <InputText id="customerEmail" v-model="selectedOrders.customer.email" readonly />
                </div>
                <div class="p-field">
                    <label for="customerPhone">Telefon:</label>
                    <InputText id="customerPhone" v-model="selectedOrders.customer.phone" readonly />
                </div>
                <div class="p-field">
                    <label for="customerAddress">Adres:</label>
                    <InputText id="customerAddress" v-model="selectedOrders.customer.address" readonly />
                </div>

                <div class="p-field">
                    <label for="orderDate">Sipariş Tarihi:</label>
                    <InputText id="orderDate" v-model="selectedOrders.order_date" readonly />
                </div>

                <h3>Sipariş Ürünleri</h3>
                <DataTable :value="selectedOrders.products" :paginator="true" :rows="5">
                    <Column field="product_name" header="Ürün Adı" sortable></Column>
                    <Column field="product_type" header="Ürün Türü" sortable></Column>
                    <Column field="pivot.quantity" header="Miktar" sortable></Column>
                </DataTable>

            </div>
        </Dialog>

        <Dialog
            v-model:visible="printOrderDialog"
            modal
            header="Sipariş Yazdırma"
            :style="{ width: '60vw', height: '80vh' }"
            :breakpoints="{ '1199px': '75vw', '575px': '90vw' }"
            :maximizable="true"
        >
            <template #header>
                <div class="flex justify-content-between align-items-center w-full">
                    <span>Sipariş Yazdırma Önizleme</span>
                    <Button
                        label="Yazdır"
                        icon="pi pi-print"
                        @click="printOrderDocument"
                        class="p-button-success"
                    />
                </div>
            </template>

            <div id="orderPrintArea" class="thermal-print-container">
                <div class="thermal-receipt">
                    <!-- Header -->
                    <div class="receipt-header">
                        <h2>ÖZ ERGÜL PLASTİK</h2>
                        <p>SİPARİŞ FİŞİ</p>
                        <div class="divider">================================</div>
                    </div>

                    <!-- Sipariş Bilgileri -->
                    <div class="order-info">
                        <div class="info-row">
                            <span>Sipariş No:</span>
                            <span>#{{ selectedPrintOrder.id }}</span>
                        </div>
                        <div class="info-row">
                            <span>Tarih:</span>
                            <span>{{ formatOrderDate(selectedPrintOrder.order_date) }}</span>
                        </div>
                        <div class="info-row">
                            <span>Kime:</span>
                            <span>{{ selectedPrintOrder.customer.name }}</span>
                        </div>
                        <div class="divider">================================</div>
                    </div>

                    <!-- Ürün Listesi -->
                    <div class="products-list">
                        <!-- Tablo Başlık -->
                        <div class="product-header">
                            <span class="header-product">ÜRÜN</span>
                            <span class="header-quantity">MİKTAR(KG)</span>
                        </div>
                        <div class="divider">--------------------------------</div>

                        <!-- Ürün Satırları -->
                        <div
                            v-for="(product, index) in selectedPrintOrder.products"
                            :key="index"
                            class="product-row"
                        >
                            <span class="product-name">{{ product.product_name }}</span>
                            <span class="product-quantity">{{ product.pivot.quantity }}</span>
                        </div>

                        <div class="divider">================================</div>
                    </div>

                    <!-- Footer -->
                    <div class="receipt-footer">
                        <p>Yazdırma Tarihi: {{ getCurrentDateTime() }}</p>
                        <p>Teşekkür ederiz!</p>
                        <div class="divider">================================</div>
                        <p class="footer-note">Bu fiş sipariş bilgilerinizi içerir</p>
                    </div>
                </div>
            </div>
        </Dialog>




    </div>
</template>

<script setup>
import {onMounted, ref} from 'vue';
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
const orders = ref([]);  // sales -> orders olarak değiştirildi
const orderDialog = ref(false);
const addOrderDialog = ref(false);  // saleDialog -> orderDialog
const updateOrderDialog = ref(false);  // updateSaleDialog -> updateOrderDialog
const detailOrderDialog = ref(false);  // detailSaleDialog -> detailOrderDialog
const deleteOrderDialog = ref(false);  // deleteSaleDialog -> deleteOrderDialog
const deleteOrdersDialog = ref(false);  // deleteSalesDialog -> deleteOrdersDialog
const order = ref({});
const selectedOrders = ref([]);
const submitted = ref(false);
const isEditDialogVisible = ref(false);
const editingProduct = ref({});
const selectedCustomer = ref(null);
const selectedProduct = ref(null);
const productQuantity = ref(1);
const productPrice = ref();
const orderProducts = ref([]);
const orderDate = ref('');
const selectedStatus = ref(null);
const orderStatusOptions = [
    { label: 'Sipariş Alındı', value: 'sipariş alındı' },
    { label: 'Hazırlanıyor', value: 'hazırlanıyor' },
    { label: 'Teslim Edildi', value: 'teslim edildi' },
];
// Sipariş yazdırma için yeni ref'ler ekleyin
const printOrderDialog = ref(false);
const selectedPrintOrder = ref({});

// Yazdırma dialog'unu açan fonksiyon
const openOrderPrintDialog = (orderData) => {
    selectedPrintOrder.value = orderData;
    printOrderDialog.value = true;
};

// Yazdırma fonksiyonu
const printOrderDocument = () => {
    setTimeout(() => {
        window.print();
    }, 100);
};

// Tarih formatlama fonksiyonu
const formatOrderDate = (date) => {
    if (!date) return '';
    return new Date(date).toLocaleDateString('tr-TR');
};

// Güncel tarih ve saat
const getCurrentDateTime = () => {
    return new Date().toLocaleString('tr-TR');
};

// Toplam ürün sayısını hesaplayan fonksiyon
const getTotalProductCount = () => {
    if (!selectedPrintOrder.value.products) return 0;
    return selectedPrintOrder.value.products.reduce((total, product) => {
        return total + (product.pivot.quantity || 0);
    }, 0);
};

onMounted(() => {
    fetchOrders();
    fetchCustomers();
    fetchProducts();
});

const fetchOrders = () => {  // fetchSales -> fetchOrders
    axios.get('/api/orders')  // /api/sales -> /api/orders
        .then(response => {
            orders.value = response.data.reverse();
        })
        .catch(error => {
            console.error("Siparişleri getirirken hata:", error);
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

const openNew = () => {
    order.value = {};  // sale -> order
    selectedCustomer.value = null;
    orderDate.value = '';  // saleDate -> orderDate
    orderProducts.value = [];  // saleProducts -> orderProducts
    productQuantity.value = 1;
    productPrice.value = '';
    submitted.value = false;
    orderDialog.value = true;
    addOrderDialog.value = true;
};

const openOrderDetailDialog = (data) => {
    selectedOrders.value = data;
    detailOrderDialog.value = true;
};

const openUpdateOrderDialog = (data) => {
    selectedOrders.value = data;
    orderDate.value = data.order_date;
    selectedCustomer.value = data.customer;
    orderProducts.value = data.products.map(product => ({
        ...product,
    }));
    updateOrderDialog.value = true;
};

const openEditDialog = (product) => {
    editingProduct.value = { ...product };
    editingProduct.value.price = parseFloat(editingProduct.value.price);
    isEditDialogVisible.value = true;
};

const saveEdit = () => {
    const index = orderProducts.value.findIndex((p) => p.id === editingProduct.value.id);
    if (index !== -1) {
        orderProducts.value[index] = { ...editingProduct.value };
    }
    isEditDialogVisible.value = false;
};

const addUpdatingProductToOrder = () => {
    if (!orderProducts.value) {
        orderProducts.value = [];  // Eğer orderProducts boşsa, başlatıyoruz.
    }

    if (!selectedProduct.value || productQuantity.value <= 0) {
        toast.value.add({ severity: 'warn', summary: 'Uyarı', detail: 'Lütfen tüm alanları doldurun.', life: 3000 });
        return;
    }

    const existingProductIndex = orderProducts.value.findIndex(product => product.id === selectedProduct.value.id);

    if (existingProductIndex !== -1) {
        const currentProduct = orderProducts.value[existingProductIndex];
        currentProduct.pivot.quantity = currentProduct.pivot.quantity + productQuantity.value;
    } else {
        const product = {
            ...selectedProduct.value,
            pivot: {
                quantity: productQuantity.value,
            },
        };
        orderProducts.value.push(product);
    }

    selectedProduct.value = null;
    productQuantity.value = 1;
};


const addProductToOrder = () => {
    if (selectedProduct.value && productQuantity.value > 0) {
        // Stok kontrolü kaldırıldı

        const existingProductIndex = orderProducts.value.findIndex(product => product.id === selectedProduct.value.id);

        if (existingProductIndex !== -1) {
            orderProducts.value[existingProductIndex].pivot.quantity += productQuantity.value;
        } else {
            const product = {
                ...selectedProduct.value,
                pivot: {
                    quantity: productQuantity.value,
                    price: parseFloat(productPrice.value),
                },
            };
            orderProducts.value.push(product);
        }
        selectedProduct.value = null;
        productQuantity.value = 1;
        productPrice.value = '';
    } else {
        toast.value.add({ severity: 'warn', summary: 'Uyarı', detail: 'Lütfen tüm alanları doldurun.', life: 3000 });
    }
};

const removeProductFromOrder = (product) => {
    orderProducts.value = orderProducts.value.filter(item => item.id !== product.id);
    toast.value.add({ severity: 'info', summary: 'Ürün Silindi', detail: `${product.product_name} başarıyla silindi`, life: 3000 });
};

const saveOrder = () => {
    submitted.value = true;
    if (selectedCustomer && orderProducts.value.length > 0 && selectedStatus) {
        const orderData = {
            customer_id: selectedCustomer.value.id,
            order_date: orderDate.value,
            products: orderProducts.value,
            status: selectedStatus.value,
        };

        axios.post('/api/orders', orderData)
            .then(() => {
                toast.value.add({ severity: 'success', summary: 'Başarılı', detail: 'Sipariş başarıyla eklendi', life: 3000 });
                fetchOrders();
            })
            .catch((error) => {
                toast.value.add({ severity: 'error', summary: 'Başarısız', detail: 'Sipariş eklenirken bir hata oluştu.', life: 3000 });
            });

        addOrderDialog.value = false;
    } else {
        toast.value.add({ severity: 'error', summary: 'Başarısız', detail: 'Lütfen tüm alanları doldurun', life: 3000 });
    }
};


const updateOrder = () => {
    if (selectedCustomer && orderProducts.value.length > 0) {
        const orderData = {
            customer_id: selectedCustomer.value.id,
            order_date: orderDate.value,
            products: orderProducts.value,
            status: selectedStatus.value,
        };

        axios.put(`/api/orders/${selectedOrders.value.id}`, orderData)
            .then(() => {
                toast.value.add({ severity: 'success', summary: 'Başarılı', detail: 'Sipariş başarıyla güncellendi', life: 3000 });
                fetchOrders();
                order.value = {};
                selectedCustomer.value = null;
                orderDate.value = '';
                orderProducts.value = [];
                productQuantity.value = 1;
                productPrice.value = '';
            })
            .catch(error => {
                toast.value.add({ severity: 'error', summary: 'Hata', detail: 'Güncelleme başarısız.', life: 3000 });
            });
        updateOrderDialog.value = false;
    }
};

const confirmDeleteOrder = (orderToDelete) => {
    order.value = { ...orderToDelete };
    deleteOrderDialog.value = true;
};

const deleteOrder = () => {
    axios.delete(`/api/orders/${order.value.id}`)
        .then(() => {
            toast.value.add({ severity: 'success', summary: 'Başarılı', detail: 'Sipariş başarıyla silindi', life: 3000 });
            fetchOrders();
        });

    deleteOrderDialog.value = false;
    order.value = {};
};

const confirmDeleteSelected = () => {
    deleteOrdersDialog.value = true;
};

const deleteSelectedOrders = () => {
    const ids = selectedOrders.value.map(order => order.id);
    axios.delete('/api/orders', { data: { ids } })
        .then(() => {
            toast.value.add({ severity: 'success', summary: 'Başarılı', detail: 'Seçilen siparişler başarıyla silindi', life: 3000 });
            fetchOrders();
        });

    deleteOrdersDialog.value = false;
};



</script>


<style scoped>
/* Termal Yazıcı Stilleri */
.thermal-print-container {
    background: white;
    color: black;
    font-family: 'Courier New', monospace;
    font-size: 12px;
    line-height: 1.2;
    padding: 10px;
    max-width: 300px;
    margin: 0 auto;
}

.thermal-receipt {
    width: 100%;
    background: white;
    color: black;
}

/* Header */
.receipt-header {
    text-align: center;
    margin-bottom: 15px;
}

.receipt-header h2 {
    margin: 0;
    font-size: 16px;
    font-weight: bold;
    letter-spacing: 1px;
}

.receipt-header p {
    margin: 5px 0;
    font-size: 14px;
    font-weight: bold;
}

/* Divider */
.divider {
    text-align: center;
    margin: 8px 0;
    font-size: 10px;
    color: black;
}

/* Sipariş Bilgileri */
.order-info {
    margin-bottom: 15px;
}

.info-row {
    display: flex;
    justify-content: space-between;
    margin: 3px 0;
    font-size: 11px;
}

.info-row span:first-child {
    font-weight: bold;
}

/* Müşteri Bilgileri */
.customer-info h3 {
    text-align: center;
    margin: 10px 0 8px 0;
    font-size: 12px;
    font-weight: bold;
}

.customer-details p {
    margin: 2px 0;
    font-size: 11px;
    text-align: center;
}

/* Ürün Listesi - Tablo Formatı */
.products-list {
    margin-bottom: 15px;
}

.product-header {
    display: flex;
    justify-content: space-between;
    font-weight: bold;
    font-size: 11px;
    margin-bottom: 5px;
    padding: 0 2px;
}

.header-product {
    flex: 1;
    text-align: left;
}

.header-quantity {
    flex: 0 0 80px;
    text-align: right;
}

.product-row {
    display: flex;
    justify-content: space-between;
    margin: 3px 0;
    font-size: 10px;
    padding: 0 2px;
}

.product-name {
    flex: 1;
    text-align: left;
    font-weight: normal;
    /* Uzun ürün isimlerini kes */
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    padding-right: 5px;
}

.product-quantity {
    flex: 0 0 80px;
    text-align: right;
    font-weight: bold;
}

/* Özet */
.order-summary {
    margin: 15px 0;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    margin: 5px 0;
    font-size: 11px;
    font-weight: bold;
}

/* Footer */
.receipt-footer {
    text-align: center;
    margin-top: 15px;
}

.receipt-footer p {
    margin: 3px 0;
    font-size: 10px;
}

.footer-note {
    font-style: italic;
    color: #666;
    font-size: 9px !important;
}

/* Yazdırma Stilleri */
@media print {
    /* Global ayarlar */
    * {
        -webkit-print-color-adjust: exact !important;
        color-adjust: exact !important;
        print-color-adjust: exact !important;
    }

    /* Sayfa ayarları - Termal yazıcı için */
    @page {
        size: 80mm auto; /* Termal yazıcı genişliği */
        margin: 0 !important;
        padding: 0 !important;
    }

    /* Sadece print alanını göster */
    body * {
        visibility: hidden;
    }

    #orderPrintArea,
    #orderPrintArea * {
        visibility: visible;
    }

    #orderPrintArea {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        height: auto;
    }

    /* UI elementlerini gizle */
    .p-dialog,
    .p-dialog-mask,
    .p-button,
    .card,
    .p-toolbar {
        display: none !important;
    }

    /* Termal receipt */
    .thermal-print-container {
        width: 80mm !important;
        max-width: 80mm !important;
        margin: 0 !important;
        padding: 5mm !important;
        background: white !important;
        color: black !important;
        font-family: 'Courier New', monospace !important;
        font-size: 10px !important;
    }

    .thermal-receipt {
        width: 100% !important;
        background: white !important;
        color: black !important;
    }

    /* Header yazdırma */
    .receipt-header h2 {
        font-size: 14px !important;
        color: black !important;
        margin: 0 !important;
    }

    .receipt-header p {
        font-size: 12px !important;
        color: black !important;
        margin: 2mm 0 !important;
    }

    /* Divider */
    .divider {
        font-size: 8px !important;
        color: black !important;
        margin: 3mm 0 !important;
    }

    /* Info rows */
    .info-row {
        font-size: 9px !important;
        color: black !important;
        margin: 1mm 0 !important;
    }

    /* Başlıklar */
    .customer-info h3,
    .products-list h3 {
        /* Kaldırıldı - artık h3 yok */
    }

    /* Customer details kaldırıldı */

    /* Ürün listesi yazdırma */
    .product-header {
        font-size: 9px !important;
        color: black !important;
        margin: 2mm 0 !important;
        font-weight: bold !important;
    }

    .product-row {
        font-size: 8px !important;
        color: black !important;
        margin: 1mm 0 !important;
    }

    .product-name {
        color: black !important;
        font-weight: normal !important;
    }

    .product-quantity {
        color: black !important;
        font-weight: bold !important;
    }

    /* Özet kaldırıldı */

    /* Footer */
    .receipt-footer p {
        font-size: 8px !important;
        color: black !important;
        margin: 1mm 0 !important;
    }

    .footer-note {
        font-size: 7px !important;
        color: black !important;
    }

    /* Tüm text'leri siyah yap */
    .thermal-print-container *,
    .thermal-receipt * {
        color: black !important;
    }

    /* Boşluk optimizasyonu */
    .products-list {
        margin-bottom: 2mm !important;
    }

    .receipt-footer {
        margin-top: 3mm !important;
    }
}

/* Responsive - Küçük ekranlar için */
@media (max-width: 768px) {
    .thermal-print-container {
        max-width: 250px;
        font-size: 11px;
    }

    .receipt-header h2 {
        font-size: 14px;
    }

    .info-row,
    .product-row {
        font-size: 10px;
    }
}
</style>
