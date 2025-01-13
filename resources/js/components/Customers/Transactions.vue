<template>
    <div>
        <div class="card">
            <Toolbar class="mb-4">
                <template #start>
                    <Button label="Yeni İşlem Ekle" icon="pi pi-plus" severity="success" class="mr-2" @click="openNew" />
                    <Button label="Sil" icon="pi pi-trash" severity="danger"  :disabled="!selectedCustomerTransaction || !selectedCustomerTransaction.length" />
                </template>
                <template #end>
                    <Button label="Dışa Aktar" icon="pi pi-upload" severity="help"/>
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
                        <Button icon="pi pi-info-circle" outlined rounded class="mr-2" @click="openCustomerTransactionsDetailDialog(slotProps.data)" />
                        <Button icon="pi pi-pencil" outlined rounded class="mr-2" @click="openUpdateCustomerTransactionDialog(slotProps.data)" />
                        <Button icon="pi pi-print" outlined rounded  severity="info"   @click="openPrintDailog(slotProps.data)" />
                    </template>
                </Column>
            </DataTable>

        </div>
        <Toast ref="toast" />

        <Dialog v-model:visible="updateCustomerTransactionDialog" maximizable modal header="Müşteri İşlemleri Düzenle" :style="{ width: '50rem' }" :breakpoints="{ '1199px': '75vw', '575px': '90vw' }">
            <div class="p-fluid">
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
                    <Button label="Kaydet" @click="saveAllTransactions" />
                </div>
            </div>
        </Dialog>
        <Dialog header="İşlem Düzenle" v-model:visible="isEditDialogVisible" :modal="true" :closable="false">
            <div class="p-fluid">

                <div class="p-field">
                    <label for="type">İşlem Türü</label>
                    <Dropdown
                        id="type"
                        v-model="editingTransaction.type"
                        :options="[ { label: 'Borç', value: 'borç' }, { label: 'Ödeme', value: 'ödeme' } ]"
                        optionLabel="label"
                        optionValue="value"
                        placeholder="İşlem Türü Seçin"
                    />
                </div>


                <div class="p-field">
                    <label for="date">Tarih</label>
                    <Calendar id="date" v-model="editingTransaction.date" />
                </div>


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


                <div class="p-field">
                    <label for="description">Açıklama</label>
                    <InputText id="description" v-model="editingTransaction.description" />
                </div>
            </div>


            <template #footer>
                <Button label="İptal" icon="pi pi-times" class="p-button-text" @click="isEditDialogVisible = false" />
                <Button label="Kaydet" icon="pi pi-check" class="p-button-text" @click="saveEdit" />
            </template>
        </Dialog>
        <Dialog v-model:visible="addCustomerTransactionDialog" maximizable modal header="Yeni İşlem Ekle" :style="{ width: '50rem' }" :breakpoints="{ '1199px': '75vw', '575px': '90vw' }"
        >
            <div class="p-fluid">
                <h3>Müşteri Bilgileri</h3>
                <div class="p-field">
                    <label for="customerSelect">Müşteri Seç:</label>
                    <Dropdown
                        id="newTransactionCustomer"
                        v-model="newTransactionCustomer"
                        :options="customers"
                        optionLabel="name"
                        placeholder="Müşteri Seçin"
                    />
                </div>

                <div class="p-field">
                    <label for="transactionDate">İşlem Tarihi:</label>
                    <Calendar
                        id="newTransactionDate"
                        v-model="newTransactionDate"
                        required
                        :invalid="submitted && !transactionDate"
                    />
                </div>

                <h3>İşlem Detayları</h3>
                <div class="p-field">
                    <label for="transactionType">İşlem Türü:</label>
                    <Dropdown
                        id="newTransactionType"
                        v-model="newTransactionType"
                        :options="transactionTypes"
                        option-label="label"
                        placeholder="İşlem Türü Seçin"
                    />
                </div>
                <div class="p-field">
                    <label for="transactionDescription">Açıklama:</label>
                    <InputText
                        id="newTransactionDescription"
                        v-model="newTransactionDescription"
                        placeholder="Açıklama Girin"
                    />
                </div>
                <div class="p-field">
                    <label for="transactionAmount">Miktar (TL):</label>
                    <InputText
                        id="newTransactionAmount"
                        v-model.number="newTransactionAmount"
                        type="number"
                        min="0"
                        placeholder="Miktar Girin"
                    />
                </div>

                <div class="p-field" style="text-align: right; margin-top: 20px;">
                    <Button label="Kaydet" @click="saveTransaction" />
                </div>
            </div>
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
        <Dialog v-model:visible="printTransaction" maximizable modal :style="{ width: '70rem' }" :breakpoints="{ '1199px': '75vw', '575px': '90vw' }">

            <div class="p-fluid">
                <div class="flex justify-content-between items-center mb-5">
                    <div>
                        <img src="../../../../public/Logo.png" alt="Logo" class="w-6 h-6" />
                    </div>
                    <div class="">
                        <h3 >Müşteri Bilgileri</h3>
                        <p class="text-gray-400">Ad: {{ selectedPrintCustomerTransaction.name }}</p>
                        <p class="text-gray-400">Adres: {{ selectedPrintCustomerTransaction.address }}</p>
                        <p class="text-gray-400">E-Posta: {{ selectedPrintCustomerTransaction.email }}</p>
                        <p class="text-gray-400">Telefon: {{ selectedPrintCustomerTransaction.phone }}</p>
                    </div>
                </div>


                <!-- İşlem Geçmişi -->
                <h3 class="text-center">İşlem Geçmişi</h3>
                <DataTable :value="selectedPrintCustomerTransaction.transactions" >
                    <Column field="date" header="İşlem Tarihi" sortable></Column>
                    <Column field="description" header="Açıklama" sortable></Column>
                    <Column field="type" header="Tür" sortable></Column>
                    <Column field="amount" header="Miktar (TL)" :body="formatAmount" sortable></Column>

                </DataTable>

                <!-- Toplam Bilgiler -->
                <div class="total-summary" style="text-align: right; margin-top: 20px;">
                    <p><strong>Toplam Borç:</strong> {{ formatAmount(totalDebt(selectedPrintCustomerTransaction.transactions)) }} TL</p>
                    <p><strong>Toplam Ödeme:</strong> {{ formatAmount(totalCredit(selectedPrintCustomerTransaction.transactions)) }} TL</p>
                    <p><strong style="font-size: 1.5em; font-weight: bold;">Genel Toplam:</strong> {{ formatSignedTotal(calculateTotalAmount(selectedPrintCustomerTransaction.transactions)) }} TL</p>
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
const toast = ref(null);
const addCustomerTransactionDialog = ref(false);
const updateCustomerTransactionDialog = ref(false);
const detailCustomerTransactionDialog = ref(false);
const selectedCustomerTransaction = ref([]);
const submitted = ref(false);
const isEditDialogVisible = ref(false);
const editingTransaction = ref([]);
const selectedCustomer = ref(null);
const newTransactionCustomer = ref(null);
const newTransactionDate = ref(null);
const newTransactionType = ref(null);
const newTransactionDescription = ref("");
const newTransactionAmount = ref(null);
const customerTransactions = ref([]);
const printTransaction = ref(false);
const transactionTypes = ref([  { label: "Borç", value: "borç" },
    { label: "Ödeme", value: "ödeme" }])
const selectedPrintCustomerTransaction = ref([]);


onMounted(() => {
    fetchCustomers();
});
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
    selectedPrintCustomerTransaction.value = data;
    console.log(selectedPrintCustomerTransaction.value);
    printTransaction.value=true;
}
const openNew = () => {
    selectedCustomer.value = null;
    addCustomerTransactionDialog.value=true
    submitted.value = false;
};
const openCustomerTransactionsDetailDialog = (data) => {
    selectedCustomerTransaction.value = data;
    detailCustomerTransactionDialog.value = true;
}
const openUpdateCustomerTransactionDialog = (data) => {

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
    selectedCustomer.value = {
        id: data.id,
        name: data.name,
        email: data.email,
        phone: data.phone,
        address: data.address,
    };
    customerTransactions.value = data.transactions.map(transaction => ({
        id : transaction.id,
        description: transaction.description,
        type: transaction.type === 'borç' ? 'Borç' : 'Ödeme',
        amount: transaction.amount,
        date: transaction.date,
    }));

    updateCustomerTransactionDialog.value = true;
};
const openEditDialog = (transaction) => {
    editingTransaction.value = { ...transaction };
    isEditDialogVisible.value = true;

};
const saveEdit = () => {

    const updatedTransaction = {
        id: editingTransaction.value.id,
        type: editingTransaction.value.type,
        date: editingTransaction.value.date,
        amount: editingTransaction.value.amount,
        description: editingTransaction.value.description,
    };

    customerTransactions.value = customerTransactions.value.map((transaction) =>
        transaction.id === updatedTransaction.id ? updatedTransaction : transaction
    );

    updateCalculations();
    isEditDialogVisible.value = false;
};
const saveAllTransactions = async () => {
    try {
        const updatedTransactions = customerTransactions.value.map(transaction => ({
            id: transaction.id,
            type: transaction.type,
            date: transaction.date,
            amount: transaction.amount,
            description: transaction.description,
        }));


        const resp =await axios.post('/api/transactions/bulk-update', updatedTransactions);
        toast.value.add({ severity: 'success', summary: 'İşlem Başarılı', detail: resp.data.message, life: 3000 });
        updateCustomerTransactionDialog.value=false
        updateCalculations();
        fetchCustomers();

    } catch (error) {
        toast.value.add({ severity: 'errorr', summary: 'İşlem Başarısız', detail: error.data, life: 3000 });
    }
};
const updateCalculations = () => {

    const totalDebtAmount = totalDebt(customerTransactions.value);
    const totalCreditAmount = totalCredit(customerTransactions.value);
    const totalAmount = calculateTotalAmount(customerTransactions.value);


};
const removeTransaction = (transaction) => {
    customerTransactions.value = customerTransactions.value.filter(t => t.id !== transaction.id);
};
const calculateTotalAmount = (transactions) => {
    let totalDebt = 0;

    transactions.forEach((transaction) => {
        const type = transaction.type.toLowerCase();
        if (type === "borç") {
            totalDebt += parseFloat(transaction.amount);
        } else if (type === "ödeme") {
            totalDebt -= parseFloat(transaction.amount);
        }
    });

    return totalDebt; // Genel toplamı döndür
};
const formatSignedTotal = (value) => {
    const formattedValue = formatAmount(Math.abs(value));
    return value > 0 ? `-${formattedValue}` : `+${formattedValue}`;
};
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
const resetTransactionForm = () => {
    selectedCustomer.value = null;
    transactionDate.value = null;
    transactionType.value = null;
    transactionDescription.value = "";
    transactionAmount.value = null;
};
const saveTransaction = async () => {
        try {
            const resp =await axios.post("/api/transactions", {
                customer_id : newTransactionCustomer.value.id,
                type : newTransactionType.value.value,
                date : newTransactionDate.value,
                description : newTransactionDescription.value,
                amount : newTransactionAmount.value
            });
            toast.value.add({ severity: 'success', summary: 'İşlem Başarılı', detail: resp.data, life: 3000 });
            resetTransactionForm();

        } catch (error) {
            toast.value.add({ severity: 'errorr', summary: 'İşlem Başarısız', detail: error.data, life: 3000 });

        }
        addCustomerTransactionDialog.value = false;
        fetchCustomers();
    };



</script>

<style scoped>
.card {
    margin: 2rem 0;
}

</style>
