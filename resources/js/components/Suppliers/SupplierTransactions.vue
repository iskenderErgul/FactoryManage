    <template>
        <div>
            <div class="card">
                <Toolbar class="mb-4">
                    <template #start>
                        <Button label="Yeni İşlem Ekle" icon="pi pi-plus" severity="success" class="mr-2" @click="openNew" />
                        <Button label="Sil" icon="pi pi-trash" severity="danger"  :disabled="!selectedSupplierTransaction || !selectedSupplierTransaction.length" />
                    </template>
                    <template #end>
                        <Button label="Dışa Aktar" icon="pi pi-upload" severity="help"/>
                    </template>
                </Toolbar>

                <DataTable
                    :value="suppliers"
                    v-model:selection="selectedSupplierTransaction"
                    dataKey="id"
                    :paginator="true"
                    :rows="10"
                    :rowsPerPageOptions="[5, 10, 25, 50]"
                    currentPageReportTemplate="Mevcut {first} ile {last} arasında, toplam {totalRecords} tedarikçi"
                    :globalFilterFields="['supplier_name', 'supplier_email', 'supplier_phone', 'supplier_address']"
                    v-model:filters="filters"
                    filterDisplay="menu">
                    <template #header>
                        <div class="flex justify-content-between">
                            <Button type="button" icon="pi pi-filter-slash" label="Filtreleri Temizle" outlined @click="clearFilter()" />
                            <span class="p-input-icon-left">
                                <i class="pi pi-search" />
                                <InputText v-model="filters['global'].value" placeholder="Tedarikçi ara..." />
                            </span>
                        </div>
                    </template>
                    <Column selectionMode="multiple" style="width: 2rem" :exportable="false"></Column>
                    <Column field="supplier_name" header="Tedarikçi Adı" sortable style="min-width:10rem" :showFilterMatchModes="false">
                        <template #filter="{ filterModel }">
                            <InputText type="text" v-model="filterModel.value" class="p-column-filter" placeholder="Ada göre ara" />
                        </template>
                    </Column>
                    <Column field="supplier_email" header="E-posta" sortable style="min-width:12rem" :showFilterMatchModes="false">
                        <template #filter="{ filterModel }">
                            <InputText type="text" v-model="filterModel.value" class="p-column-filter" placeholder="E-posta ara" />
                        </template>
                    </Column>
                    <Column field="supplier_phone" header="Telefon" sortable style="min-width:10rem" :showFilterMatchModes="false">
                        <template #filter="{ filterModel }">
                            <InputText type="text" v-model="filterModel.value" class="p-column-filter" placeholder="Telefon ara" />
                        </template>
                    </Column>
                    <Column field="display_debt" header="Net Bakiye (TL)" sortable style="min-width:8rem">
                        <template #body="slotProps">
                            <span :class="slotProps.data.display_debt > 0 ? 'text-red-400 font-bold' : slotProps.data.display_debt < 0 ? 'text-green-400 font-bold' : 'text-gray-400 font-bold'">
                                {{ slotProps.data.display_debt > 0 ? '+' : slotProps.data.display_debt < 0 ? '' : '' }}{{ formatAmount(slotProps.data.display_debt) }} TL
                            </span>
                        </template>
                    </Column>
                    <Column :exportable="false" style="min-width:8rem">
                        <template #body="slotProps">
                            <Button icon="pi pi-info-circle" outlined rounded class="mr-2" @click="openSupplierTransactionsDetailDialog(slotProps.data)" />
                            <Button icon="pi pi-pencil" outlined rounded class="mr-2" @click="openUpdateSupplierTransactionDialog(slotProps.data)" />
                            <Button icon="pi pi-print" outlined rounded  severity="info"   @click="openPrintDialog(slotProps.data)" />
                        </template>
                    </Column>
                </DataTable>

            </div>
            <Toast ref="toast" />

            <Dialog v-model:visible="updateSupplierTransactionDialog" maximizable modal header="Tedarikçi İşlemleri Düzenle" :style="{ width: '50rem' }" :breakpoints="{ '1199px': '75vw', '575px': '90vw' }">
                <div class="p-fluid">
                    <h3 class="text-center">Tedarikçi Bilgileri</h3>
                    <div class="p-field">
                        <label for="supplierName">Ad:</label>
                        <InputText id="supplierName" v-model="selectedSupplierTransaction.supplier_name" readonly />
                    </div>
                    <div class="p-field">
                        <label for="supplierEmail">Email:</label>
                        <InputText id="supplierEmail" v-model="selectedSupplierTransaction.supplier_email" readonly />
                    </div>
                    <div class="p-field">
                        <label for="supplierPhone">Telefon:</label>
                        <InputText id="supplierPhone" v-model="selectedSupplierTransaction.supplier_phone" readonly />
                    </div>
                    <div class="p-field">
                        <label for="supplierAddress">Adres:</label>
                        <InputText id="supplierAddress" v-model="selectedSupplierTransaction.supplier_address" readonly />
                    </div>

                    <h3 class="text-center">İşlem Geçmişi</h3>
                    <DataTable :value="supplierTransactions">
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
                        <div class="flex justify-content-end align-items-center mb-3">
                            <label for="detailPeriodSelect" class="mr-2 font-semibold">Hesaplama Dönemi:</label>
                            <Dropdown 
                                id="detailPeriodSelect"
                                v-model="selectedPeriod" 
                                :options="periodOptions" 
                                optionLabel="label" 
                                placeholder="Dönem Seçin"
                                class="w-10rem"
                                @change="updateCalculations"
                            />
                        </div>
                        <p><strong>Toplam Borç ({{ selectedPeriod.label }}):</strong> {{ formatAmount(totalDebtForPeriod(supplierTransactions, selectedPeriod.value)) }} TL</p>
                        <p><strong>Toplam Ödeme ({{ selectedPeriod.label }}):</strong> {{ formatAmount(totalCreditForPeriod(supplierTransactions, selectedPeriod.value)) }} TL</p>
                        <p v-if="lastPayment(supplierTransactions)"><strong>Son Ödeme:</strong> {{ formatAmount(lastPayment(supplierTransactions).amount) }} TL <span v-if="lastPayment(supplierTransactions).date">({{ lastPayment(supplierTransactions).date }})</span></p>
                        <hr style="margin: 15px 0; border: 1px solid var(--surface-border);">
                        <p><strong style="font-size: 1.5em; font-weight: bold; color: var(--text-color);">Net Bakiye (Tüm Geçmiş):</strong> 
                            <span :style="calculateTotalAmount(supplierTransactions, 0) > 0 ? 'color: #f87171;' : calculateTotalAmount(supplierTransactions, 0) < 0 ? 'color: #4ade80;' : 'color: #9ca3af;'">
                                {{ calculateTotalAmount(supplierTransactions, 0) > 0 ? '+' : '' }}{{ formatAmount(calculateTotalAmount(supplierTransactions, 0)) }} TL
                            </span>
                        </p>
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

            <Dialog v-model:visible="addSupplierTransactionDialog" maximizable modal header="Yeni İşlem Ekle" :style="{ width: '50rem' }" :breakpoints="{ '1199px': '75vw', '575px': '90vw' }">
                <div class="p-fluid">
                    <h3>Tedarikçi Bilgileri</h3>
                    <div class="p-field">
                        <label for="supplierSelect">Tedarikçi Seç:</label>
                        <Dropdown
                            id="newTransactionSupplier"
                            v-model="newTransactionSupplier"
                            :options="suppliers"
                            optionLabel="supplier_name"
                            placeholder="Tedarikçi Seçin"
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

            <Dialog v-model:visible="detailSupplierTransactionDialog" maximizable modal header="İşlem Detayları" :style="{ width: '70rem' }" :breakpoints="{ '1199px': '75vw', '575px': '90vw' }">
                <div class="p-fluid">
                    <h3>Tedarikçi Bilgileri</h3>
                    <div class="p-field">
                        <label for="supplierName">Ad:</label>
                        <InputText id="supplierName" v-model="selectedSupplierTransaction.supplier_name" readonly />
                    </div>
                    <div class="p-field">
                        <label for="supplierEmail">Email:</label>
                        <InputText id="supplierEmail" v-model="selectedSupplierTransaction.supplier_email" readonly />
                    </div>
                    <div class="p-field">
                        <label for="supplierPhone">Telefon:</label>
                        <InputText id="supplierPhone" v-model="selectedSupplierTransaction.supplier_phone" readonly />
                    </div>
                    <div class="p-field">
                        <label for="supplierAddress">Adres:</label>
                        <InputText id="supplierAddress" v-model="selectedSupplierTransaction.supplier_address" readonly />
                    </div>

                    <h3>İşlem Geçmişi</h3>
                    <DataTable :value="selectedSupplierTransaction.transactions" :paginator="true" :rows="5">
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
                        <div class="flex justify-content-end align-items-center mb-3">
                            <label for="readOnlyPeriodSelect" class="mr-2 font-semibold">Hesaplama Dönemi:</label>
                            <Dropdown 
                                id="readOnlyPeriodSelect"
                                v-model="selectedPeriod" 
                                :options="periodOptions" 
                                optionLabel="label" 
                                placeholder="Dönem Seçin"
                                class="w-10rem"
                            />
                        </div>
                        <p><strong>Toplam Borç ({{ selectedPeriod.label }}):</strong> {{ formatAmount(totalDebtForPeriod(selectedSupplierTransaction.transactions, selectedPeriod.value)) }} TL</p>
                        <p><strong>Toplam Ödeme ({{ selectedPeriod.label }}):</strong> {{ formatAmount(totalCreditForPeriod(selectedSupplierTransaction.transactions, selectedPeriod.value)) }} TL</p>
                        <p v-if="lastPayment(selectedSupplierTransaction.transactions)"><strong>Son Ödeme:</strong> {{ formatAmount(lastPayment(selectedSupplierTransaction.transactions).amount) }} TL <span v-if="lastPayment(selectedSupplierTransaction.transactions).date">({{ lastPayment(selectedSupplierTransaction.transactions).date }})</span></p>
                        <hr style="margin: 15px 0; border: 1px solid var(--surface-border);">
                        <p><strong style="font-size: 1.5em; font-weight: bold; color: var(--text-color);">Net Bakiye (Tüm Geçmiş):</strong> 
                            <span :style="calculateTotalAmount(selectedSupplierTransaction.transactions, 0) > 0 ? 'color: #f87171;' : calculateTotalAmount(selectedSupplierTransaction.transactions, 0) < 0 ? 'color: #4ade80;' : 'color: #9ca3af;'">
                                {{ calculateTotalAmount(selectedSupplierTransaction.transactions, 0) > 0 ? '+' : '' }}{{ formatAmount(calculateTotalAmount(selectedSupplierTransaction.transactions, 0)) }} TL
                            </span>
                        </p>
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
                                        <p>Tedarikçi İşlem Raporu - Son 15 İşlem</p>
                                        <p>Tarih: {{ getCurrentDate() }}</p>
                                    </div>
                                </div>

                                <div class="supplier-info">
                                    <h3>Tedarikçi Bilgileri</h3>
                                    <p><strong>Ad:</strong> {{ selectedPrintSupplierTransaction.supplier_name }}</p>
                                    <p><strong>Adres:</strong> {{ selectedPrintSupplierTransaction.supplier_address }}</p>
                                    <p><strong>E-Posta:</strong> {{ selectedPrintSupplierTransaction.supplier_email }}</p>
                                    <p><strong>Telefon:</strong> {{ selectedPrintSupplierTransaction.supplier_phone }}</p>
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
                                <template v-if="getLast15Transactions().length < 15">
                                    <tr
                                        v-for="n in (15 - getLast15Transactions().length)"
                                        :key="`empty-${n}`"
                                        class="empty-row"
                                    >
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                    </tr>
                                </template>
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
                                    v-if="calculateTotalAmount(selectedPrintSupplierTransaction.transactions) > 0"
                                >
                                    <span><strong>Toplam Borç:</strong></span>
                                    <span class="debt-amount"><strong>{{ formatAmount(calculateTotalAmount(selectedPrintSupplierTransaction.transactions)) }} TL</strong></span>
                                </div>

                                <!-- Toplam Alacak (Sadece alacaklı ise göster) -->
                                <div
                                    class="summary-row receivable-row"
                                    v-if="calculateTotalAmount(selectedPrintSupplierTransaction.transactions) < 0"
                                >
                                    <span><strong>Toplam Alacak:</strong></span>
                                    <span class="receivable-amount"><strong>{{ formatAmount(Math.abs(calculateTotalAmount(selectedPrintSupplierTransaction.transactions))) }} TL</strong></span>
                                </div>

                                <!-- Son Alacak -->
                                <div class="summary-row" v-if="lastPayment(selectedPrintSupplierTransaction.transactions)">
                                    <span><strong>Son Alacak:</strong></span>
                                    <span>
                            <strong>{{ formatAmount(lastPayment(selectedPrintSupplierTransaction.transactions).amount) }} TL</strong>
                            <span class="last-payment-date"> - {{ formatDate(lastPayment(selectedPrintSupplierTransaction.transactions).date) }}</span>
                        </span>
                                </div>

                                <!-- Güncel Bakiye -->
                                <div class="summary-row total-row">
                                    <span><strong>GÜNCEL BAKİYE:</strong></span>
                                    <span class="balance-amount">
                            <strong>
                                <span v-if="calculateTotalAmount(selectedPrintSupplierTransaction.transactions) > 0" class="debt-balance">
                                    {{ formatAmount(calculateTotalAmount(selectedPrintSupplierTransaction.transactions)) }} TL (Borç)
                                </span>
                                <span v-else-if="calculateTotalAmount(selectedPrintSupplierTransaction.transactions) < 0" class="credit-balance">
                                    {{ formatAmount(Math.abs(calculateTotalAmount(selectedPrintSupplierTransaction.transactions))) }} TL (Alacak)
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
    import { FilterMatchMode, FilterOperator } from 'primevue/api';

    const suppliers = ref([]);
    const toast = ref(null);
    const addSupplierTransactionDialog = ref(false);
    const updateSupplierTransactionDialog = ref(false);
    const detailSupplierTransactionDialog = ref(false);
    const selectedSupplierTransaction = ref([]);
    const submitted = ref(false);
    const isEditDialogVisible = ref(false);
    const editingTransaction = ref([]);
    const selectedSupplier = ref(null);
    const newTransactionSupplier = ref(null);
    const newTransactionDate = ref(null);
    const newTransactionType = ref(null);
    const newTransactionDescription = ref("");
    const newTransactionAmount = ref(null);
    const supplierTransactions = ref([]);
    const printTransaction = ref(false);
    const transactionTypes = ref([
        { label: "Borç", value: "borç" },
        { label: "Ödeme", value: "ödeme" }
    ]);
    const selectedPrintSupplierTransaction = ref([]);

    // Dönem seçimi için state
    const selectedPeriod = ref({ label: 'Son 3 Ay', value: 3 });
    const periodOptions = ref([
        { label: 'Son 3 Ay', value: 3 },
        { label: 'Son 6 Ay', value: 6 },
        { label: 'Son 1 Yıl', value: 12 },
        { label: 'Son 3 Yıl', value: 36 },
        { label: 'Tüm Geçmiş', value: 0 }
    ]);

    // Filtreleme için state
    const filters = ref({
        'global': { value: null, matchMode: FilterMatchMode.CONTAINS },
        'supplier_name': { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.STARTS_WITH }] },
        'supplier_email': { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.CONTAINS }] },
        'supplier_phone': { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.CONTAINS }] }
    });

    // Yazdırma için sabit değerler
    const ROWS_PER_PAGE = 15; // Her sayfada maksimum satır sayısı

    onMounted(() => {
        fetchSuppliers();
    });

    // Filtreleri temizleme fonksiyonu
    const clearFilter = () => {
        filters.value = {
            'global': { value: null, matchMode: FilterMatchMode.CONTAINS },
            'supplier_name': { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.STARTS_WITH }] },
            'supplier_email': { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.CONTAINS }] },
            'supplier_phone': { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.CONTAINS }] }
        };
    };

    const fetchSuppliers = () => {
        // Ana ekranda her zaman varsayılan (Son 3 Ay) göster
        const params = { period_months: 3 };
        
        axios.get('/api/suppliers', { params })
            .then(response => {
                suppliers.value = response.data.map(supplier => ({
                    ...supplier,
                    // Dönemsel borç varsa onu kullan, yoksa calculated_debt kullan
                    display_debt: supplier.period_debt !== undefined ? supplier.period_debt : supplier.calculated_debt
                }));
            })
            .catch(error => {
                toast.value.add({ severity: 'error', summary: 'İşlem Başarısız', detail: error.data, life: 3000 });
            });
    };

    // SON 15 İŞLEM YAZDIRMA FONKSİYONLARI - EKLENEN
    // Son 15 işlemi al ve tarihe göre sırala (en yeni en üstte)
    const getLast15Transactions = () => {
        const transactions = selectedPrintSupplierTransaction.value.transactions || [];
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
        selectedPrintSupplierTransaction.value = data;
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
        try {
            const dateObj = new Date(date);
            // Geçersiz tarih kontrolü
            if (isNaN(dateObj.getTime())) return '';

            return dateObj.toLocaleDateString('tr-TR', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric'
            });
        } catch (error) {
            return '';
        }
    };

    // ESKİ YAZDIRMA FONKSİYONLARI (TÜM İŞLEMLER İÇİN)
    const getTotalPages = () => {
        const totalTransactions = selectedPrintSupplierTransaction.value.transactions?.length || 0;
        return Math.max(1, Math.ceil(totalTransactions / ROWS_PER_PAGE));
    };

    const getFirstPageTransactions = () => {
        const transactions = selectedPrintSupplierTransaction.value.transactions || [];
        return transactions.slice(0, ROWS_PER_PAGE);
    };

    const getPageTransactions = (pageNumber) => {
        const transactions = selectedPrintSupplierTransaction.value.transactions || [];
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
        selectedSupplier.value = null;
        cleanNewTransaction();
        addSupplierTransactionDialog.value = true;
        submitted.value = false;
    };

    const openSupplierTransactionsDetailDialog = (data) => {
        selectedSupplierTransaction.value = {
            ...data,
            transactions: [...data.transactions].reverse()
        };
        detailSupplierTransactionDialog.value = true;
    };

    const openUpdateSupplierTransactionDialog = (data) => {
        selectedSupplierTransaction.value = {
            id: data.id,
            supplier_name: data.supplier_name,
            supplier_email: data.supplier_email,
            supplier_phone: data.supplier_phone,
            supplier_address: data.supplier_address,
            debt: parseFloat(data.debt),
            transactions: data.transactions.map(transaction => ({
                ...transaction,
                amount: parseFloat(transaction.amount),
            })),
        };
        selectedSupplier.value = {
            id: data.id,
            supplier_name: data.supplier_name,
            supplier_email: data.supplier_email,
            supplier_phone: data.supplier_phone,
            supplier_address: data.supplier_address,
        };
        supplierTransactions.value = data.transactions.map(transaction => ({
            supplier_id: data.id,
            id: transaction.id,
            description: transaction.description,
            type: transaction.type === 'borç' ? 'Borç' : 'Alacak',
            amount: transaction.amount,
            date: transaction.date,
        }));

        updateSupplierTransactionDialog.value = true;
    };

    const openEditDialog = (transaction) => {
        editingTransaction.value = {
            ...transaction,
            // Type'ı dropdown'daki value formatına çevir
            type: transaction.type.toLowerCase() === 'borç' ? 'borç' : 'ödeme',
            // supplier_id'nin kesinlikle mevcut olmasını sağla
            supplier_id: transaction.supplier_id || selectedSupplier.value?.id
        };
        isEditDialogVisible.value = true;
    };

    const cleanNewTransaction = () => {
        newTransactionDate.value = null;
        newTransactionAmount.value = null;
        newTransactionSupplier.value = null;
        newTransactionType.value = null;
        newTransactionDescription.value = null;
    };

    const saveEdit = () => {
        // Tarihi doğru formatta hazırla (timezone sorununu önlemek için)
        let formattedDate = editingTransaction.value.date;
        if (formattedDate instanceof Date) {
            const year = formattedDate.getFullYear();
            const month = String(formattedDate.getMonth() + 1).padStart(2, '0');
            const day = String(formattedDate.getDate()).padStart(2, '0');
            formattedDate = `${year}-${month}-${day}`;
        } else if (typeof formattedDate === 'string' && formattedDate.includes('T')) {
            formattedDate = formattedDate.split('T')[0];
        }

        const updatedTransaction = {
            id: editingTransaction.value.id,
            supplier_id: editingTransaction.value.supplier_id, // supplier_id'yi koruyalım
            type: editingTransaction.value.type === 'borç' ? 'Borç' : 'Ödeme', // Tablo görünümü için büyük harfle
            date: formattedDate,
            amount: editingTransaction.value.amount,
            description: editingTransaction.value.description,
        };

        supplierTransactions.value = supplierTransactions.value.map((transaction) =>
            transaction.id === updatedTransaction.id ? { ...transaction, ...updatedTransaction } : transaction
        );

        updateCalculations();
        isEditDialogVisible.value = false;
    };

    const saveAllTransactions = async () => {
        try {
            const updatedTransactions = supplierTransactions.value.map(transaction => {
                // Tarihi doğru formatta hazırla (timezone sorununu önlemek için)
                let formattedDate = transaction.date;
                if (formattedDate instanceof Date) {
                    const year = formattedDate.getFullYear();
                    const month = String(formattedDate.getMonth() + 1).padStart(2, '0');
                    const day = String(formattedDate.getDate()).padStart(2, '0');
                    formattedDate = `${year}-${month}-${day}`;
                } else if (typeof formattedDate === 'string' && formattedDate.includes('T')) {
                    formattedDate = formattedDate.split('T')[0];
                }

                return {
                    supplier_id: transaction.supplier_id,
                    id: transaction.id,
                    type: transaction.type.toLowerCase() === 'borç' ? 'borç' : 'ödeme', // Backend için küçük harfle
                    date: formattedDate,
                    amount: transaction.amount,
                    description: transaction.description,
                };
            });

            // Eğer transaction'lar boşsa, supplier_id'yi ayrı olarak gönder
            let requestData;
            if (updatedTransactions.length === 0) {
                // Boş transaction array'i durumunda sadece supplier_id gönder
                const supplierId = selectedSupplier.value?.id || selectedSupplierTransaction.value?.id;
                if (!supplierId) {
                    toast.value.add({ severity: 'error', summary: 'Hata', detail: 'Tedarikçi ID bulunamadı', life: 3000 });
                    return;
                }
                requestData = { supplier_id: supplierId };
            } else {
                requestData = updatedTransactions;
            }

            console.log('Gönderilen veri:', requestData); // Debug için
            const resp = await axios.put('/api/suppliers/transactions/bulk', requestData);
            toast.value.add({ severity: 'success', summary: 'İşlem Başarılı', detail: resp.data.message, life: 3000 });
            updateSupplierTransactionDialog.value = false;
            updateCalculations();
            fetchSuppliers();
            selectedSupplier.value = null;
        } catch (error) {
            toast.value.add({ severity: 'error', summary: 'İşlem Başarısız', detail: error.data, life: 3000 });
        }
    };

    const updateCalculations = () => {
        // Bu fonksiyon artık sadece reactive olarak çalışıyor
        // Dropdown değiştiğinde otomatik olarak hesaplamalar güncelleniyor
    };

    const removeTransaction = (transaction) => {
        supplierTransactions.value = supplierTransactions.value.filter(t => t.id !== transaction.id);
    };

    const calculateTotalAmount = (transactions, periodMonths = null) => {
        let totalDebt = 0;
        let filteredTransactions = transactions;

        // Eğer dönem belirtilmişse, sadece o döneme ait işlemleri al
        if (periodMonths && periodMonths > 0) {
            const startDate = new Date();
            startDate.setMonth(startDate.getMonth() - periodMonths);
            
            filteredTransactions = transactions.filter(transaction => {
                const transactionDate = new Date(transaction.date);
                return transactionDate >= startDate;
            });
        }

        filteredTransactions.forEach((transaction) => {
            const type = transaction.type.toLowerCase();
            if (type === "borç") {
                totalDebt += parseFloat(transaction.amount);  // Tedarik aldık, borç arttı
            } else if (type === "ödeme") {
                totalDebt -= parseFloat(transaction.amount);  // Ödeme yaptık, borç azaldı
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

    // Dönemsel borç hesaplama fonksiyonları
    const totalDebtForPeriod = (transactions, periodMonths) => {
        if (!transactions) return 0;
        
        let filteredTransactions = transactions;
        
        if (periodMonths && periodMonths > 0) {
            const startDate = new Date();
            startDate.setMonth(startDate.getMonth() - periodMonths);
            
            filteredTransactions = transactions.filter(transaction => {
                const transactionDate = new Date(transaction.date);
                return transactionDate >= startDate;
            });
        }
        
        return filteredTransactions
            .filter((transaction) => transaction.type.toLowerCase() === "borç")
            .reduce((sum, transaction) => sum + parseFloat(transaction.amount), 0);
    };

    const totalCreditForPeriod = (transactions, periodMonths) => {
        if (!transactions) return 0;
        
        let filteredTransactions = transactions;
        
        if (periodMonths && periodMonths > 0) {
            const startDate = new Date();
            startDate.setMonth(startDate.getMonth() - periodMonths);
            
            filteredTransactions = transactions.filter(transaction => {
                const transactionDate = new Date(transaction.date);
                return transactionDate >= startDate;
            });
        }
        
        return filteredTransactions
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
        selectedSupplier.value = null;
        newTransactionDate.value = null;
        newTransactionType.value = null;
        newTransactionDescription.value = "";
        newTransactionAmount.value = null;
    };

    const saveTransaction = async () => {
        try {
            // Tarihi doğru formatta hazırla (timezone sorununu önlemek için)
            let formattedDate = newTransactionDate.value;
            if (formattedDate instanceof Date) {
                const year = formattedDate.getFullYear();
                const month = String(formattedDate.getMonth() + 1).padStart(2, '0');
                const day = String(formattedDate.getDate()).padStart(2, '0');
                formattedDate = `${year}-${month}-${day}`;
            } else if (typeof formattedDate === 'string' && formattedDate.includes('T')) {
                formattedDate = formattedDate.split('T')[0];
            }

            const resp = await axios.post("/api/suppliers/transactions", {
                supplier_id: newTransactionSupplier.value.id,
                type: newTransactionType.value.value,
                date: formattedDate,
                description: newTransactionDescription.value,
                amount: newTransactionAmount.value
            });
            toast.value.add({ severity: 'success', summary: 'İşlem Başarılı', detail: 'İşlem Başarıyla Eklendi', life: 3000 });
            resetTransactionForm();
        } catch (error) {
            toast.value.add({ severity: 'error', summary: 'İşlem Başarısız', detail: 'Bir hata oluştu', life: 3000 });
        }
        addSupplierTransactionDialog.value = false;
        fetchSuppliers();
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

    .supplier-info {
        flex: 1;
        text-align: right;
    }

    .supplier-info h3 {
        margin: 0 0 15px 0;
        font-size: 16px;
        color: #2c3e50;
        border-bottom: 1px solid #ddd;
        padding-bottom: 5px;
    }

    .supplier-info p {
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
        color: #27ae60; /* Yeşil - Alacak */
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

        /* Supplier info */
        .supplier-info h3 {
            font-size: 11px !important;
            color: black !important;
            border-bottom: 1px solid black !important;
        }

        .supplier-info p,
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

        .supplier-info {
            text-align: center;
            margin-top: 20px;
        }
    }
    </style>
