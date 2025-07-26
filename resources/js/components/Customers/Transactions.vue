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
                            <Button icon="pi pi-print" outlined rounded  severity="info"   @click="openPrintDialog(slotProps.data)" />
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
                        <p v-if="lastPayment(customerTransactions)"><strong>Son Ödeme:</strong> {{ formatAmount(lastPayment(customerTransactions).amount) }} TL <span v-if="lastPayment(customerTransactions).date">({{ lastPayment(customerTransactions).date }})</span></p>
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

            <Dialog v-model:visible="addCustomerTransactionDialog" maximizable modal header="Yeni İşlem Ekle" :style="{ width: '50rem' }" :breakpoints="{ '1199px': '75vw', '575px': '90vw' }">
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
                            :invalid="submitted && !newTransactionDate"
                        />
                    </div>

                    <h3>İşlem Detayları</h3>
                    <div class="p-field">
                        <label for="transactionType">İşlem Türü:</label>
                        <Dropdown
                            id="newTransactionType"
                            v-model="newTransactionType"
                            :options="transactionTypes"
                            optionLabel="label"
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
                        <InputNumber
                            id="newTransactionAmount"
                            v-model.number="newTransactionAmount"
                            type="number"
                            min="1"
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
                        <Column field="amount" header="Miktar(TL)" sortable>
                            <template #body="slotProps">
                                {{ formatAmount(slotProps.data.amount) }}
                            </template>
                        </Column>
                    </DataTable>

                    <div class="total-summary" style="text-align: right; margin-top: 20px;">
                        <p><strong>Toplam Borç:</strong> {{ formatAmount(totalDebt(selectedCustomerTransaction.transactions)) }} TL</p>
                        <p><strong>Toplam Ödeme:</strong> {{ formatAmount(totalCredit(selectedCustomerTransaction.transactions)) }} TL</p>
                        <p v-if="lastPayment(selectedCustomerTransaction.transactions)"><strong>Son Ödeme:</strong> {{ formatAmount(lastPayment(selectedCustomerTransaction.transactions).amount) }} TL <span v-if="lastPayment(selectedCustomerTransaction.transactions).date">({{ lastPayment(selectedCustomerTransaction.transactions).date }})</span></p>
                        <p><strong style="font-size: 1.5em; font-weight: bold;">Genel Toplam:</strong> {{ formatSignedTotal(calculateTotalAmount(selectedCustomerTransaction.transactions)) }} TL</p>
                    </div>
                </div>
            </Dialog>

            <!-- YENİ YAZDIRMA DIALOG'U - SON 15 İŞLEM -->
            <Dialog
                v-model:visible="printTransaction"
                modal
                header="Yazdırma Önizleme"
                :style="{ width: '90vw', height: '90vh' }"
                :breakpoints="{ '1199px': '95vw', '575px': '98vw' }"
                :maximizable="true"
            >
                <template #header>
                    <div class="flex justify-content-between align-items-center w-full">
                        <span>Yazdırma Önizleme - Son 15 İşlem</span>
                        <Button
                            label="Yazdır"
                            icon="pi pi-print"
                            @click="printDocument"
                            class="p-button-success"
                        />
                    </div>
                </template>

                <div id="printArea" class="print-container">
                    <!-- TEK SAYFA - SON 15 İŞLEM -->
                    <div class="print-page-wrapper">
                        <div class="print-page">
                            <!-- Header -->
                            <div class="print-header">
                                <div class="company-info">
                                    <img src="../../../../public/Logo.png" alt="Logo" class="company-logo" />
                                    <div class="company-details">
                                        <h2>ÖZ ERGÜL PLASTİK</h2>
                                        <p>Müşteri İşlem Raporu - Son 15 İşlem</p>
                                        <p>Tarih: {{ getCurrentDate() }}</p>
                                    </div>
                                </div>

                                <div class="customer-info">
                                    <h3>Müşteri Bilgileri</h3>
                                    <p><strong>Ad:</strong> {{ selectedPrintCustomerTransaction.name }}</p>
                                    <p><strong>Adres:</strong> {{ selectedPrintCustomerTransaction.address }}</p>
                                    <p><strong>E-Posta:</strong> {{ selectedPrintCustomerTransaction.email }}</p>
                                    <p><strong>Telefon:</strong> {{ selectedPrintCustomerTransaction.phone }}</p>
                                </div>
                            </div>

                            <!-- İşlem Geçmişi Başlık -->
                            <h3 class="section-title">Son 15 İşlem</h3>

                            <!-- Tablo -->
                            <table class="print-table">
                                <thead>
                                <tr>
                                    <th>İşlem Tarihi</th>
                                    <th>Açıklama</th>
                                    <th>Tür</th>
                                    <th>Miktar (TL)</th>
                                </tr>
                                </thead>
                                <tbody>
                                <!-- Son 15 işlem -->
                                <tr
                                    v-for="(transaction, index) in getLast15Transactions()"
                                    :key="`last15-${index}`"
                                    class="table-row"
                                >
                                    <td>{{ formatDate(transaction.date) }}</td>
                                    <td>{{ transaction.description }}</td>
                                    <td>{{ transaction.type }}</td>
                                    <td class="amount-cell">{{ formatAmount(transaction.amount) }}</td>
                                </tr>
                                <!-- Boş satırlar ekle (15 satıra tamamlamak için) -->
                                <tr
                                    v-for="n in (15 - getLast15Transactions().length)"
                                    :key="`empty-${n}`"
                                    class="empty-row"
                                    v-if="getLast15Transactions().length < 15"
                                >
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                </tr>
                                </tbody>
                            </table>

                            <!-- HESAP ÖZETİ (TÜM İŞLEMLERDEN HESAPLANAN) -->
                            <div class="total-summary-print">
                                <div class="summary-header">
                                    <h4>Hesap Özeti</h4>
                                    <p class="summary-note">(Tüm işlemler dahil)</p>
                                </div>

                                <!-- Toplam Borç (Sadece borçlu ise göster) -->
                                <div
                                    class="summary-row"
                                    v-if="calculateTotalAmount(selectedPrintCustomerTransaction.transactions) > 0"
                                >
                                    <span><strong>Toplam Borç:</strong></span>
                                    <span class="debt-amount"><strong>{{ formatAmount(calculateTotalAmount(selectedPrintCustomerTransaction.transactions)) }} TL</strong></span>
                                </div>

                                <!-- Toplam Alacak (Sadece alacaklı ise göster) -->
                                <div
                                    class="summary-row receivable-row"
                                    v-if="calculateTotalAmount(selectedPrintCustomerTransaction.transactions) < 0"
                                >
                                    <span><strong>Toplam Alacak:</strong></span>
                                    <span class="receivable-amount"><strong>{{ formatAmount(Math.abs(calculateTotalAmount(selectedPrintCustomerTransaction.transactions))) }} TL</strong></span>
                                </div>

                                <!-- Son Ödeme -->
                                <div class="summary-row" v-if="lastPayment(selectedPrintCustomerTransaction.transactions)">
                                    <span><strong>Son Ödeme:</strong></span>
                                    <span>
                            <strong>{{ formatAmount(lastPayment(selectedPrintCustomerTransaction.transactions).amount) }} TL</strong>
                            <span class="last-payment-date"> - {{ formatDate(lastPayment(selectedPrintCustomerTransaction.transactions).date) }}</span>
                        </span>
                                </div>

                                <!-- Güncel Bakiye -->
                                <div class="summary-row total-row">
                                    <span><strong>GÜNCEL BAKİYE:</strong></span>
                                    <span class="balance-amount">
                            <strong>
                                <span v-if="calculateTotalAmount(selectedPrintCustomerTransaction.transactions) > 0" class="debt-balance">
                                    {{ formatAmount(calculateTotalAmount(selectedPrintCustomerTransaction.transactions)) }} TL (Borç)
                                </span>
                                <span v-else-if="calculateTotalAmount(selectedPrintCustomerTransaction.transactions) < 0" class="credit-balance">
                                    {{ formatAmount(Math.abs(calculateTotalAmount(selectedPrintCustomerTransaction.transactions))) }} TL (Alacak)
                                </span>
                                <span v-else class="zero-balance">
                                    0 TL (Hesap Kapalı)
                                </span>
                            </strong>
                        </span>
                                </div>
                            </div>
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
    const transactionTypes = ref([
        { label: "Borç", value: "borç" },
        { label: "Ödeme", value: "ödeme" }
    ]);
    const selectedPrintCustomerTransaction = ref([]);

    // Yazdırma için sabit değerler
    const ROWS_PER_PAGE = 15; // Her sayfada maksimum satır sayısı

    onMounted(() => {
        fetchCustomers();
    });

    const fetchCustomers = () => {
        axios.get('/api/customers')
            .then(response => {
                customers.value = response.data;
            })
            .catch(error => {
                toast.value.add({ severity: 'error', summary: 'İşlem Başarısız', detail: error.data, life: 3000 });
            });
    };

    // SON 15 İŞLEM YAZDIRMA FONKSİYONLARI - EKLENEN
    // Son 15 işlemi al ve tarihe göre sırala (en yeni en üstte)
    const getLast15Transactions = () => {
        const transactions = selectedPrintCustomerTransaction.value.transactions || [];
        return transactions
            .sort((a, b) => new Date(b.date) - new Date(a.date)) // En yeni en üstte
            .slice(0, 15); // Son 15 işlem
    };

    // Toplam sayfa sayısını hesapla (maksimum 1 sayfa - çünkü sadece 15 işlem)
    const getTotalPagesForLast15 = () => {
        const totalTransactions = getLast15Transactions().length;
        return Math.max(1, Math.ceil(totalTransactions / ROWS_PER_PAGE));
    };

    // İlk sayfa işlemlerini getir (en fazla 15)
    const getFirstPageTransactionsLast15 = () => {
        return getLast15Transactions();
    };

    // Son 15 işlem için boş satır sayısı
    const getEmptyRowsForLast15 = () => {
        const transactionCount = getLast15Transactions().length;
        const emptyRows = ROWS_PER_PAGE - transactionCount;
        return emptyRows > 0 ? emptyRows : 0;
    };

    // Yazdırma fonksiyonları
    const openPrintDialog = (data) => {
        selectedPrintCustomerTransaction.value = data;
        printTransaction.value = true;
    };

    const printDocument = () => {
        setTimeout(() => {
            window.print();
        }, 100);
    };

    const getCurrentDate = () => {
        return new Date().toLocaleDateString('tr-TR');
    };

    const formatDate = (date) => {
        if (!date) return '';
        return new Date(date).toLocaleDateString('tr-TR');
    };

    // ESKİ YAZDIRMA FONKSİYONLARI (TÜM İŞLEMLER İÇİN)
    const getTotalPages = () => {
        const totalTransactions = selectedPrintCustomerTransaction.value.transactions?.length || 0;
        return Math.max(1, Math.ceil(totalTransactions / ROWS_PER_PAGE));
    };

    const getFirstPageTransactions = () => {
        const transactions = selectedPrintCustomerTransaction.value.transactions || [];
        return transactions.slice(0, ROWS_PER_PAGE);
    };

    const getPageTransactions = (pageNumber) => {
        const transactions = selectedPrintCustomerTransaction.value.transactions || [];
        const startIndex = (pageNumber - 1) * ROWS_PER_PAGE;
        const endIndex = startIndex + ROWS_PER_PAGE;
        return transactions.slice(startIndex, endIndex);
    };

    const getAdditionalPages = () => {
        const totalPages = getTotalPages();
        const pages = [];
        for (let i = 2; i <= totalPages; i++) {
            pages.push(i);
        }
        return pages;
    };

    const getEmptyRowsForFirstPage = () => {
        const transactionCount = getFirstPageTransactions().length;
        const emptyRows = ROWS_PER_PAGE - transactionCount;
        return emptyRows > 0 ? emptyRows : 0;
    };

    const getEmptyRowsForPage = (pageNumber) => {
        const transactionCount = getPageTransactions(pageNumber).length;
        const emptyRows = ROWS_PER_PAGE - transactionCount;
        return emptyRows > 0 ? emptyRows : 0;
    };

    // Diğer fonksiyonlar
    const openNew = () => {
        selectedCustomer.value = null;
        cleanNewTransaction();
        addCustomerTransactionDialog.value = true;
        submitted.value = false;
    };

    const openCustomerTransactionsDetailDialog = (data) => {
        selectedCustomerTransaction.value = {
            ...data,
            transactions: [...data.transactions].reverse()
        };
        detailCustomerTransactionDialog.value = true;
    };

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
                amount: parseFloat(transaction.amount),
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
            customer_id: data.id,
            id: transaction.id,
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

    const cleanNewTransaction = () => {
        newTransactionDate.value = null;
        newTransactionAmount.value = null;
        newTransactionCustomer.value = null;
        newTransactionType.value = null;
        newTransactionDescription.value = null;
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
                customer_id: transaction.customer_id,
                id: transaction.id,
                type: transaction.type,
                date: transaction.date,
                amount: transaction.amount,
                description: transaction.description,
            }));
            const resp = await axios.post('/api/transactions/bulk-update', updatedTransactions);
            toast.value.add({ severity: 'success', summary: 'İşlem Başarılı', detail: resp.data.message, life: 3000 });
            updateCustomerTransactionDialog.value = false;
            updateCalculations();
            fetchCustomers();
            selectedCustomer.value = null;
        } catch (error) {
            toast.value.add({ severity: 'error', summary: 'İşlem Başarısız', detail: error.data, life: 3000 });
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

        return totalDebt;
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

    const lastPayment = (transactions) => {
        const payments = transactions
            .filter((transaction) => transaction.type.toLowerCase() === "ödeme")
            .sort((a, b) => {
                // Önce tarihe göre sırala
                const dateA = new Date(a.date);
                const dateB = new Date(b.date);
                const dateCompare = dateB.getTime() - dateA.getTime();

                // Eğer tarihler aynıysa, ID'ye göre sırala (en yüksek ID en son eklenen)
                if (dateCompare === 0) {
                    return b.id - a.id;
                }

                return dateCompare;
            });

        if (payments.length === 0) return null;
        return payments[0];
    };

    const resetTransactionForm = () => {
        selectedCustomer.value = null;
        newTransactionDate.value = null;
        newTransactionType.value = null;
        newTransactionDescription.value = "";
        newTransactionAmount.value = null;
    };

    const saveTransaction = async () => {
        try {
            const resp = await axios.post("/api/transactions", {
                customer_id: newTransactionCustomer.value.id,
                type: newTransactionType.value.value,
                date: newTransactionDate.value,
                description: newTransactionDescription.value,
                amount: newTransactionAmount.value
            });
            toast.value.add({ severity: 'success', summary: 'İşlem Başarılı', detail: 'İşlem Başarıyla Eklendi', life: 3000 });
            resetTransactionForm();
        } catch (error) {
            toast.value.add({ severity: 'error', summary: 'İşlem Başarısız', detail: 'Bir hata oluştu', life: 3000 });
        }
        addCustomerTransactionDialog.value = false;
        fetchCustomers();
    };
    </script>

    <style scoped>
    .card {
        margin: 2rem 0;
    }

    /* Print Container Stilleri */
    .print-container {
        background: white;
        color: black !important;
        font-family: 'Times New Roman', serif;
        font-size: 12px;
        line-height: 1.4;
    }

    /* SAYFA WRAPPER */
    .print-page-wrapper {
        width: 100%;
        background: white;
        margin-bottom: 30px;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
        position: relative;
    }

    .print-page {
        width: 100%;
        min-height: 250mm;
        padding: 20mm;
        background: white;
        position: relative;
    }

    /* Header Stilleri */
    .print-header {
        display: flex;
        justify-content: space-between;
        margin-bottom: 30px;
        border-bottom: 2px solid #333;
        padding-bottom: 20px;
    }

    .company-info {
        flex: 1;
    }

    .company-logo {
        width: 80px;
        height: 80px;
        margin-bottom: 10px;
    }

    .company-details h2 {
        margin: 0;
        font-size: 18px;
        font-weight: bold;
        color: #2c3e50;
    }

    .company-details p {
        margin: 5px 0;
        font-size: 12px;
        color: #666;
    }

    .customer-info {
        flex: 1;
        text-align: right;
    }

    .customer-info h3 {
        margin: 0 0 15px 0;
        font-size: 16px;
        color: #2c3e50;
        border-bottom: 1px solid #ddd;
        padding-bottom: 5px;
    }

    .customer-info p {
        margin: 8px 0;
        font-size: 12px;
    }

    /* Section Title */
    .section-title {
        text-align: center;
        margin: 20px 0;
        font-size: 16px;
        font-weight: bold;
        color: #2c3e50;
        border-bottom: 2px solid #3498db;
        padding-bottom: 10px;
    }

    /* Tablo Stilleri */
    .print-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20px;
        background: white;
    }

    .print-table th {
        background-color: #f8f9fa !important;
        border: 1px solid #333 !important;
        padding: 12px 8px !important;
        text-align: left;
        font-weight: bold;
        font-size: 12px;
        color: #2c3e50 !important;
    }

    .print-table td {
        border: 1px solid #ddd !important;
        padding: 10px 8px !important;
        font-size: 11px;
        color: #333 !important;
    }

    .table-row:nth-child(even) {
        background-color: #f9f9f9;
    }

    .amount-cell {
        text-align: right;
        font-weight: 500;
    }

    .empty-row td {
        height: 25px;
        border-color: #f0f0f0 !important;
    }

    /* Toplam Bilgiler - DETAYLI HESAP ÖZETİ */
    .total-summary-print {
        margin-top: 30px;
        padding: 20px;
        background-color: #f8f9fa;
        border: 2px solid #3498db;
        border-radius: 8px;
    }

    .summary-header {
        text-align: center;
        margin-bottom: 15px;
        border-bottom: 1px solid #ddd;
        padding-bottom: 10px;
    }

    .summary-header h4 {
        margin: 0;
        font-size: 16px;
        color: #2c3e50;
        font-weight: bold;
    }

    .summary-note {
        margin: 5px 0 0 0;
        font-size: 11px;
        color: #666;
        font-style: italic;
    }

    .summary-row {
        display: flex;
        justify-content: space-between;
        margin: 12px 0;
        font-size: 13px;
        align-items: center;
    }

    /* Renk kodları */
    .debt-amount {
        color: #e74c3c; /* Kırmızı - Borç */
    }

    .credit-amount {
        color: #27ae60; /* Yeşil - Ödeme */
    }

    .receivable-amount {
        color: #3498db; /* Mavi - Alacak */
    }

    .last-payment-date {
        font-size: 11px;
        color: #666;
        font-weight: normal;
    }

    .receivable-row {
        background-color: #e8f4f8;
        padding: 8px;
        border-radius: 4px;
        border-left: 4px solid #3498db;
    }

    .total-row {
        border-top: 2px solid #2c3e50;
        padding-top: 15px;
        margin-top: 15px;
        font-size: 16px;
        font-weight: bold;
        color: #2c3e50;
        background-color: #ecf0f1;
        padding: 15px 10px;
        border-radius: 5px;
    }

    /* Bakiye renkleri */
    .debt-balance {
        color: #e74c3c;
        font-weight: bold;
    }

    .credit-balance {
        color: #27ae60;
        font-weight: bold;
    }

    .zero-balance {
        color: #95a5a6;
        font-weight: bold;
    }

    /* Sayfa Footer */
    .page-footer {
        position: absolute;
        bottom: 10mm;
        left: 20mm;
        right: 20mm;
        text-align: center;
        border-top: 1px solid #ddd;
        padding-top: 10px;
        font-size: 11px;
        color: #666;
        background: white;
    }

    .page-footer p {
        margin: 5px 0;
    }

    /* YAZDIRMA STİLLERİ */
    @media print {
        /* Global ayarlar */
        * {
            -webkit-print-color-adjust: exact !important;
            color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        /* Sayfa ayarları */
        @page {
            size: A4 portrait;
            margin: 15mm !important;
            padding: 0;
        }

        /* Sadece print alanını göster */
        body * {
            visibility: hidden;
        }

        #printArea,
        #printArea * {
            visibility: visible;
        }

        #printArea {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            height: auto;
        }

        /* Print container */
        .print-container {
            width: 100% !important;
            height: auto !important;
            background: white !important;
            color: black !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        /* SAYFA WRAPPER */
        .print-page-wrapper {
            width: 100% !important;
            height: auto !important;
            margin: 0 !important;
            padding: 0 !important;
            box-shadow: none !important;
            background: white !important;
            position: relative;
        }

        /* SAYFA İÇERİĞİ */
        .print-page {
            width: 100% !important;
            height: auto !important;
            min-height: auto !important;
            padding: 15mm !important;
            margin: 0 !important;
            background: white !important;
            color: black !important;
            position: relative;
            box-sizing: border-box;
        }

        /* UI elementlerini gizle */
        .p-dialog,
        .p-dialog-mask,
        .p-button,
        .card,
        .p-toolbar {
            display: none !important;
        }

        /* Header */
        .print-header {
            margin-bottom: 15mm !important;
            border-bottom: 1px solid black !important;
            padding-bottom: 5mm !important;
        }

        /* Company logo */
        .company-logo {
            width: 40px !important;
            height: 40px !important;
        }

        /* Başlıklar */
        .company-details h2 {
            font-size: 14px !important;
            color: black !important;
            margin: 0 !important;
        }

        .section-title {
            font-size: 12px !important;
            color: black !important;
            border-bottom: 1px solid black !important;
            margin: 10mm 0 !important;
            padding-bottom: 2mm !important;
        }

        /* Customer info */
        .customer-info h3 {
            font-size: 11px !important;
            color: black !important;
            border-bottom: 1px solid black !important;
        }

        .customer-info p,
        .company-details p {
            font-size: 9px !important;
            color: black !important;
            margin: 1mm 0 !important;
        }

        /* Tablo */
        .print-table {
            width: 100% !important;
            border: 1px solid black !important;
            border-collapse: collapse !important;
            margin-bottom: 5mm !important;
            font-size: 9px !important;
            background: white !important;
        }

        .print-table th {
            background: #f0f0f0 !important;
            border: 1px solid black !important;
            padding: 2mm !important;
            font-weight: bold !important;
            color: black !important;
            font-size: 9px !important;
        }

        .print-table td {
            border: 1px solid black !important;
            padding: 2mm !important;
            color: black !important;
            font-size: 8px !important;
        }

        /* Satır renkleri */
        .table-row:nth-child(even) {
            background-color: #f8f8f8 !important;
        }

        .empty-row td {
            height: 8px !important;
            border-color: #ccc !important;
        }

        /* Toplam bilgiler */
        .total-summary-print {
            background: #f5f5f5 !important;
            border: 1px solid black !important;
            border-radius: 0 !important;
            margin-top: 5mm !important;
            padding: 3mm !important;
            page-break-inside: avoid !important;
            break-inside: avoid !important;
        }

        .summary-header h4 {
            font-size: 11px !important;
            color: black !important;
            margin: 0 !important;
        }

        .summary-note {
            font-size: 8px !important;
            color: black !important;
            margin: 1mm 0 !important;
        }

        .summary-row {
            font-size: 9px !important;
            color: black !important;
            margin: 1mm 0 !important;
        }

        .last-payment-date {
            font-size: 8px !important;
            color: black !important;
        }

        .total-row {
            border-top: 1px solid black !important;
            font-size: 10px !important;
            color: black !important;
            padding-top: 2mm !important;
            font-weight: bold !important;
            background: #e0e0e0 !important;
            padding: 3mm !important;
        }

        .receivable-row {
            background: #f0f0f0 !important;
            border-left: 2px solid black !important;
            padding: 2mm !important;
        }

        /* Renk kodları yazdırmada */
        .debt-amount,
        .debt-balance {
            color: black !important;
            font-weight: bold !important;
        }

        .credit-amount,
        .credit-balance {
            color: black !important;
            font-weight: bold !important;
        }

        .receivable-amount {
            color: black !important;
            font-weight: bold !important;
        }

        .zero-balance {
            color: black !important;
            font-weight: bold !important;
        }

        /* Page footer */
        .page-footer {
            position: absolute !important;
            bottom: 5mm !important;
            left: 15mm !important;
            right: 15mm !important;
            width: calc(100% - 30mm) !important;
            text-align: center !important;
            font-size: 8px !important;
            color: black !important;
            border-top: 1px solid black !important;
            padding-top: 2mm !important;
            background: white !important;
        }

        .page-footer p {
            margin: 1mm 0 !important;
            color: black !important;
        }

        /* Tüm text'leri siyah yap */
        .print-container *,
        .print-page *,
        .print-table *,
        .total-summary-print *,
        .page-footer *,
        .print-header * {
            color: black !important;
        }
    }

    /* Responsive tasarım */
    @media (max-width: 768px) {
        .print-header {
            flex-direction: column;
            text-align: center;
        }

        .customer-info {
            text-align: center;
            margin-top: 20px;
        }
    }
    </style>
