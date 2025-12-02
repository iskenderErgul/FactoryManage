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
                    :rowsPerPageOptions="[5, 10, 25, 50]"
                    currentPageReportTemplate="Mevcut {first} ile {last} arasında, toplam {totalRecords} müşteri"
                    :globalFilterFields="['name', 'email', 'phone', 'address']"
                    v-model:filters="filters"
                    filterDisplay="menu">
                    <template #header>
                        <div class="flex justify-content-between">
                            <Button type="button" icon="pi pi-filter-slash" label="Filtreleri Temizle" outlined @click="clearFilter()" />
                            <span class="p-input-icon-left">
                                <i class="pi pi-search" />
                                <InputText v-model="filters['global'].value" placeholder="Müşteri ara..." />
                            </span>
                        </div>
                    </template>
                    <Column selectionMode="multiple" style="width: 2rem" :exportable="false"></Column>
                    <Column field="name" header="Müşteri Adı" sortable style="min-width:10rem" :showFilterMatchModes="false">
                        <template #filter="{ filterModel }">
                            <InputText type="text" v-model="filterModel.value" class="p-column-filter" placeholder="Ada göre ara" />
                        </template>
                    </Column>
                    <Column field="email" header="E-posta" sortable style="min-width:12rem" :showFilterMatchModes="false">
                        <template #filter="{ filterModel }">
                            <InputText type="text" v-model="filterModel.value" class="p-column-filter" placeholder="E-posta ara" />
                        </template>
                    </Column>
                    <Column field="phone" header="Telefon" sortable style="min-width:10rem" :showFilterMatchModes="false">
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
                            <Button icon="pi pi-info-circle" outlined rounded class="mr-2" @click="openCustomerTransactionsDetailDialog(slotProps.data)" />
                            <Button icon="pi pi-pencil" outlined rounded class="mr-2" @click="openUpdateCustomerTransactionDialog(slotProps.data)" />
                            <Button icon="pi pi-file-pdf" outlined rounded  severity="danger" @click="openPdfDialog(slotProps.data)" />
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
                        <p><strong>Toplam Borç ({{ selectedPeriod.label }}):</strong> {{ formatAmount(totalDebtForPeriod(customerTransactions, selectedPeriod.value)) }} TL</p>
                        <p><strong>Toplam Ödeme ({{ selectedPeriod.label }}):</strong> {{ formatAmount(totalCreditForPeriod(customerTransactions, selectedPeriod.value)) }} TL</p>
                        <p v-if="lastPayment(customerTransactions)"><strong>Son Ödeme:</strong> {{ formatAmount(lastPayment(customerTransactions).amount) }} TL <span v-if="lastPayment(customerTransactions).date">({{ lastPayment(customerTransactions).date }})</span></p>
                        <hr style="margin: 15px 0; border: 1px solid var(--surface-border);">
                        <p><strong style="font-size: 1.5em; font-weight: bold; color: var(--text-color);">Net Bakiye (Tüm Geçmiş):</strong> 
                            <span :style="calculateTotalAmount(customerTransactions, 0) > 0 ? 'color: #f87171;' : calculateTotalAmount(customerTransactions, 0) < 0 ? 'color: #4ade80;' : 'color: #9ca3af;'">
                                {{ calculateTotalAmount(customerTransactions, 0) > 0 ? '+' : '' }}{{ formatAmount(calculateTotalAmount(customerTransactions, 0)) }} TL
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
                        <p><strong>Toplam Borç ({{ selectedPeriod.label }}):</strong> {{ formatAmount(totalDebtForPeriod(selectedCustomerTransaction.transactions, selectedPeriod.value)) }} TL</p>
                        <p><strong>Toplam Ödeme ({{ selectedPeriod.label }}):</strong> {{ formatAmount(totalCreditForPeriod(selectedCustomerTransaction.transactions, selectedPeriod.value)) }} TL</p>
                        <p v-if="lastPayment(selectedCustomerTransaction.transactions)"><strong>Son Ödeme:</strong> {{ formatAmount(lastPayment(selectedCustomerTransaction.transactions).amount) }} TL <span v-if="lastPayment(selectedCustomerTransaction.transactions).date">({{ lastPayment(selectedCustomerTransaction.transactions).date }})</span></p>
                        <hr style="margin: 15px 0; border: 1px solid var(--surface-border);">
                        <p><strong style="font-size: 1.5em; font-weight: bold; color: var(--text-color);">Net Bakiye (Tüm Geçmiş):</strong> 
                            <span :style="calculateTotalAmount(selectedCustomerTransaction.transactions, 0) > 0 ? 'color: #f87171;' : calculateTotalAmount(selectedCustomerTransaction.transactions, 0) < 0 ? 'color: #4ade80;' : 'color: #9ca3af;'">
                                {{ calculateTotalAmount(selectedCustomerTransaction.transactions, 0) > 0 ? '+' : '' }}{{ formatAmount(calculateTotalAmount(selectedCustomerTransaction.transactions, 0)) }} TL
                            </span>
                        </p>
                    </div>
                </div>
            </Dialog>

            <!-- YENİ PDF OLUŞTURMA DIALOG'U -->
            <Dialog
                v-model:visible="pdfDialog"
                modal
                header="Cari Ekstre Oluştur"
                :style="{ width: '40rem' }"
                :breakpoints="{ '1199px': '60vw', '575px': '90vw' }"
            >
                <div class="p-fluid">
                    <div class="p-field mb-4">
                        <label for="pdfCustomerName" class="font-semibold">Müşteri:</label>
                        <InputText 
                            id="pdfCustomerName" 
                            v-model="selectedPdfCustomer.name" 
                            readonly 
                            class="mt-2"
                        />
                    </div>

                    <div class="p-field mb-2">
                        <div class="flex align-items-center">
                            <Checkbox v-model="pdfAllHistory" :binary="true" inputId="allHistory" />
                            <label for="allHistory" class="ml-2 font-semibold">Tüm Geçmiş İşlemler</label>
                        </div>
                    </div>

                    <div class="p-field mb-4">
                        <label for="pdfDateRange" class="font-semibold" :class="{ 'text-gray-400': pdfAllHistory }">Tarih Aralığı:</label>
                        <Calendar 
                            id="pdfDateRange"
                            v-model="pdfDateRange" 
                            selectionMode="range" 
                            :manualInput="false"
                            dateFormat="dd.mm.yy"
                            placeholder="Başlangıç - Bitiş"
                            class="mt-2"
                            :maxDate="new Date()"
                            :disabled="pdfAllHistory"
                        />
                        <small class="text-gray-500 mt-1" v-if="!pdfAllHistory">
                            * Maksimum 1 yıllık tarih aralığı seçebilirsiniz
                        </small>
                    </div>

                    <div class="p-field mb-4">
                        <label class="font-semibold mb-2">Görüntüleme Modu:</label>
                        <div class="flex gap-3 mt-2">
                            <div class="flex align-items-center">
                                <RadioButton 
                                    v-model="pdfDisplayMode" 
                                    inputId="download" 
                                    value="download" 
                                />
                                <label for="download" class="ml-2">İndir</label>
                            </div>
                            <div class="flex align-items-center">
                                <RadioButton 
                                    v-model="pdfDisplayMode" 
                                    inputId="inline" 
                                    value="inline" 
                                />
                                <label for="inline" class="ml-2">Tarayıcıda Görüntüle</label>
                            </div>
                        </div>
                    </div>
                </div>

                <template #footer>
                    <Button 
                        label="İptal" 
                        icon="pi pi-times" 
                        @click="pdfDialog = false" 
                        text 
                    />
                    <Button 
                        label="PDF Oluştur" 
                        icon="pi pi-file-pdf" 
                        @click="generatePdf" 
                        :loading="pdfLoading"
                        :disabled="!pdfAllHistory && (!pdfDateRange || !pdfDateRange[0] || !pdfDateRange[1])"
                        severity="danger"
                    />
                </template>
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
    import RadioButton from 'primevue/radiobutton';
    import Checkbox from 'primevue/checkbox';
    import { FilterMatchMode, FilterOperator } from 'primevue/api';


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
    const transactionTypes = ref([
        { label: "Borç", value: "borç" },
        { label: "Ödeme", value: "ödeme" }
    ]);
    
    // PDF Generation State
    const pdfDialog = ref(false);
    const selectedPdfCustomer = ref({});
    const pdfDateRange = ref(null);
    const pdfDisplayMode = ref('download');
    const pdfLoading = ref(false);
    const pdfAllHistory = ref(false);

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
        'name': { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.STARTS_WITH }] },
        'email': { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.CONTAINS }] },
        'phone': { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.CONTAINS }] }
    });

    // Yazdırma için sabit değerler
    const ROWS_PER_PAGE = 15; // Her sayfada maksimum satır sayısı

    onMounted(() => {
        fetchCustomers();
    });

    // Filtreleri temizleme fonksiyonu
    const clearFilter = () => {
        filters.value = {
            'global': { value: null, matchMode: FilterMatchMode.CONTAINS },
            'name': { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.STARTS_WITH }] },
            'email': { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.CONTAINS }] },
            'phone': { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.CONTAINS }] }
        };
    };

    const fetchCustomers = () => {
        // Ana ekranda her zaman varsayılan (Son 3 Ay) göster
        const params = { period_months: 3 };
        
        axios.get('/api/customers', { params })
            .then(response => {
                customers.value = response.data.map(customer => ({
                    ...customer,
                    // Dönemsel borç varsa onu kullan, yoksa calculated_debt kullan
                    display_debt: customer.period_debt !== undefined ? customer.period_debt : customer.calculated_debt
                }));
            })
            .catch(error => {
                toast.value.add({ severity: 'error', summary: 'İşlem Başarısız', detail: error.data, life: 3000 });
            });
    };

    // PDF Generation Functions
    const openPdfDialog = (customer) => {
        selectedPdfCustomer.value = customer;
        
        // Varsayılan olarak son 3 ay
        const endDate = new Date();
        const startDate = new Date();
        startDate.setMonth(startDate.getMonth() - 3);
        
        pdfDateRange.value = [startDate, endDate];
        pdfDisplayMode.value = 'download';
        pdfAllHistory.value = false;
        pdfDialog.value = true;
    };

    const generatePdf = async () => {
        // Tarih kontrolü (Eğer tüm geçmiş seçili değilse)
        if (!pdfAllHistory.value && (!pdfDateRange.value || !pdfDateRange.value[0] || !pdfDateRange.value[1])) {
            toast.value.add({ 
                severity: 'warn', 
                summary: 'Uyarı', 
                detail: 'Lütfen tarih aralığı seçin veya Tüm Geçmiş seçeneğini işaretleyin', 
                life: 3000 
            });
            return;
        }

        pdfLoading.value = true;

        try {
            let payload = {
                display: pdfDisplayMode.value,
                all_history: pdfAllHistory.value
            };

            // Eğer tüm geçmiş seçili değilse tarihleri ekle
            if (!pdfAllHistory.value) {
                payload.start_date = formatDateForApi(pdfDateRange.value[0]);
                payload.end_date = formatDateForApi(pdfDateRange.value[1]);
            }

            const response = await axios.post(
                `/api/customers/${selectedPdfCustomer.value.id}/transactions/pdf`,
                payload,
                {
                    responseType: 'blob'
                }
            );

            // PDF'i işle
            const blob = new Blob([response.data], { type: 'application/pdf' });
            const url = window.URL.createObjectURL(blob);
            
            // Dosya adı oluştur
            let fileName = '';
            if (pdfAllHistory.value) {
                fileName = `ekstre_${selectedPdfCustomer.value.name}_tum_gecmis.pdf`;
            } else {
                fileName = `ekstre_${selectedPdfCustomer.value.name}_${payload.start_date}_${payload.end_date}.pdf`;
            }

            if (pdfDisplayMode.value === 'inline') {
                // Yeni sekmede aç
                window.open(url, '_blank');
            } else {
                // İndir
                const link = document.createElement('a');
                link.href = url;
                link.download = fileName;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            }

            // URL'i temizle
            window.URL.revokeObjectURL(url);

            toast.value.add({ 
                severity: 'success', 
                summary: 'Başarılı', 
                detail: 'Cari Ekstre başarıyla oluşturuldu', 
                life: 3000 
            });

            pdfDialog.value = false;

        } catch (error) {
            console.error('PDF oluşturma hatası:', error);
            
            let errorMessage = 'PDF oluşturulurken bir hata oluştu';
            
            if (error.response) {
                if (error.response.status === 429) {
                    errorMessage = 'Çok fazla istek gönderdiniz. Lütfen bir süre bekleyin.';
                } else if (error.response.status === 404) {
                    errorMessage = 'Müşteri bulunamadı.';
                } else if (error.response.data && error.response.data.message) {
                    errorMessage = error.response.data.message;
                }
            }
            
            toast.value.add({ 
                severity: 'error', 
                summary: 'Hata', 
                detail: errorMessage, 
                life: 5000 
            });
        } finally {
            pdfLoading.value = false;
        }
    };

    const formatDateForApi = (date) => {
        if (!date) return '';
        const d = new Date(date);
        const year = d.getFullYear();
        const month = String(d.getMonth() + 1).padStart(2, '0');
        const day = String(d.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
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
        editingTransaction.value = { 
            ...transaction,
            // Type'ı dropdown'daki value formatına çevir
            type: transaction.type.toLowerCase() === 'borç' ? 'borç' : 'ödeme',
            // customer_id'nin kesinlikle mevcut olmasını sağla
            customer_id: transaction.customer_id || selectedCustomer.value?.id
        };
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
        // Basit ve güvenilir tarih formatlaması
        let formattedDate = editingTransaction.value.date;
        if (formattedDate instanceof Date) {
            // Local tarih olarak formatla (timezone problemlerini önlemek için)
            const year = formattedDate.getFullYear();
            const month = String(formattedDate.getMonth() + 1).padStart(2, '0');
            const day = String(formattedDate.getDate()).padStart(2, '0');
            formattedDate = `${year}-${month}-${day}`;
        } else if (typeof formattedDate === 'string' && formattedDate.includes('T')) {
            formattedDate = formattedDate.split('T')[0];
        }

        const updatedTransaction = {
            id: editingTransaction.value.id,
            customer_id: editingTransaction.value.customer_id, // customer_id'yi koruyalım
            type: editingTransaction.value.type === 'borç' ? 'Borç' : 'Ödeme', // Tablo görünümü için büyük harfle
            date: formattedDate,
            amount: editingTransaction.value.amount,
            description: editingTransaction.value.description,
        };

        customerTransactions.value = customerTransactions.value.map((transaction) =>
            transaction.id === updatedTransaction.id ? { ...transaction, ...updatedTransaction } : transaction
        );

        updateCalculations();
        isEditDialogVisible.value = false;
    };

    const saveAllTransactions = async () => {
        try {
            const updatedTransactions = customerTransactions.value.map(transaction => {
                // Basit ve güvenilir tarih formatlaması
                let formattedDate = transaction.date;
                if (formattedDate instanceof Date) {
                    // Local tarih olarak formatla (timezone problemlerini önlemek için)
                    const year = formattedDate.getFullYear();
                    const month = String(formattedDate.getMonth() + 1).padStart(2, '0');
                    const day = String(formattedDate.getDate()).padStart(2, '0');
                    formattedDate = `${year}-${month}-${day}`;
                } else if (typeof formattedDate === 'string' && formattedDate.includes('T')) {
                    formattedDate = formattedDate.split('T')[0];
                }

                return {
                    customer_id: transaction.customer_id,
                    id: transaction.id,
                    type: transaction.type.toLowerCase() === 'borç' ? 'borç' : 'ödeme', // Backend için küçük harfle
                    date: formattedDate,
                    amount: transaction.amount,
                    description: transaction.description,
                };
            });
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
        // Bu fonksiyon artık sadece reactive olarak çalışıyor
        // Dropdown değiştiğinde otomatik olarak hesaplamalar güncelleniyor
    };

    const removeTransaction = (transaction) => {
        customerTransactions.value = customerTransactions.value.filter(t => t.id !== transaction.id);
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
        selectedCustomer.value = null;
        newTransactionDate.value = null;
        newTransactionType.value = null;
        newTransactionDescription.value = "";
        newTransactionAmount.value = null;
    };

    const saveTransaction = async () => {
        try {
            // Basit ve güvenilir tarih formatlaması
            let formattedDate = newTransactionDate.value;
            if (formattedDate instanceof Date) {
                // Local tarih olarak formatla (timezone problemlerini önlemek için)
                const year = formattedDate.getFullYear();
                const month = String(formattedDate.getMonth() + 1).padStart(2, '0');
                const day = String(formattedDate.getDate()).padStart(2, '0');
                formattedDate = `${year}-${month}-${day}`;
            } else if (typeof formattedDate === 'string') {
                // String ise sadece tarih kısmını al
                if (formattedDate.includes('T')) {
                    formattedDate = formattedDate.split('T')[0];
                }
            }

            console.log('Gönderilen tarih:', formattedDate);

            const resp = await axios.post("/api/transactions", {
                customer_id: newTransactionCustomer.value.id,
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
        addCustomerTransactionDialog.value = false;
        fetchCustomers();
    };
    </script>

    <style scoped>
    .card {
        margin: 2rem 0;
    }
    </style>
