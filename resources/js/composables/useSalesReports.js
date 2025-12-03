import axios from 'axios';
import { ref } from 'vue';

export function useSalesReports() {
    const loading = ref(false);
    const error = ref(null);

    const apiClient = axios.create({
        baseURL: '/api/reports/sales',
        headers: {
            'Content-Type': 'application/json',
        },
    });

    /**
     * Tarih Aralıklı Rapor
     */
    const fetchDateRangeReport = async (startDate, endDate) => {
        loading.value = true;
        error.value = null;
        try {
            const response = await apiClient.post('/date-range', {
                start_date: startDate,
                end_date: endDate,
            });
            return response.data;
        } catch (err) {
            error.value = err.response?.data?.message || 'Bir hata oluştu';
            throw err;
        } finally {
            loading.value = false;
        }
    };

    /**
     * Aylık Rapor
     */
    const fetchMonthlyReport = async (year, month) => {
        loading.value = true;
        error.value = null;
        try {
            const response = await apiClient.post('/monthly', {
                year,
                month,
            });
            return response.data;
        } catch (err) {
            error.value = err.response?.data?.message || 'Bir hata oluştu';
            throw err;
        } finally {
            loading.value = false;
        }
    };

    /**
     * Müşteri Bazlı Rapor
     */
    const fetchCustomerReport = async (customerId, filters = {}) => {
        loading.value = true;
        error.value = null;
        try {
            const response = await apiClient.post('/customer', {
                customer_id: customerId,
                start_date: filters.startDate || null,
                end_date: filters.endDate || null,
            });
            return response.data;
        } catch (err) {
            error.value = err.response?.data?.message || 'Bir hata oluştu';
            throw err;
        } finally {
            loading.value = false;
        }
    };

    /**
     * Müşteri Ürün Bazlı Rapor
     */
    const fetchCustomerProductReport = async (customerId, filters = {}) => {
        loading.value = true;
        error.value = null;
        try {
            const payload = {
                customer_id: customerId,
            };

            if (filters.startDate && filters.endDate) {
                payload.start_date = filters.startDate;
                payload.end_date = filters.endDate;
            } else if (filters.year && filters.month) {
                payload.year = filters.year;
                payload.month = filters.month;
            }

            const response = await apiClient.post('/customer-products', payload);
            return response.data;
        } catch (err) {
            error.value = err.response?.data?.message || 'Bir hata oluştu';
            throw err;
        } finally {
            loading.value = false;
        }
    };

    /**
     * Müşteri Ödeme Raporu
     */
    const fetchCustomerPaymentReport = async (customerId, filters = {}) => {
        loading.value = true;
        error.value = null;
        try {
            const response = await apiClient.post('/customer-payments', {
                customer_id: customerId,
                start_date: filters.startDate || null,
                end_date: filters.endDate || null,
            });
            return response.data;
        } catch (err) {
            error.value = err.response?.data?.message || 'Bir hata oluştu';
            throw err;
        } finally {
            loading.value = false;
        }
    };

    /**
     * Trend Raporu
     */
    const fetchTrendReport = async () => {
        loading.value = true;
        error.value = null;
        try {
            const response = await apiClient.get('/trends');
            return response.data;
        } catch (err) {
            error.value = err.response?.data?.message || 'Bir hata oluştu';
            throw err;
        } finally {
            loading.value = false;
        }
    };

    /**
     * PDF Export
     */
    const exportToPdf = async (reportType, data) => {
        loading.value = true;
        error.value = null;
        try {
            const response = await apiClient.post('/export-pdf', {
                report_type: reportType,
                ...data,
            }, {
                responseType: 'blob',
            });
            
            // Blob'u indir
            const url = window.URL.createObjectURL(new Blob([response.data]));
            const link = document.createElement('a');
            link.href = url;
            link.setAttribute('download', `satis-raporu-${Date.now()}.pdf`);
            document.body.appendChild(link);
            link.click();
            link.remove();
            
            return response.data;
        } catch (err) {
            error.value = err.response?.data?.message || 'PDF oluşturulurken bir hata oluştu';
            throw err;
        } finally {
            loading.value = false;
        }
    };

    /**
     * Excel Export
     */
    const exportToExcel = async (reportType, data) => {
        loading.value = true;
        error.value = null;
        try {
            const response = await apiClient.post('/export-excel', {
                report_type: reportType,
                ...data,
            }, {
                responseType: 'blob',
            });
            
            // Blob'u indir
            const url = window.URL.createObjectURL(new Blob([response.data]));
            const link = document.createElement('a');
            link.href = url;
            link.setAttribute('download', `satis-raporu-${Date.now()}.xlsx`);
            document.body.appendChild(link);
            link.click();
            link.remove();
            
            return response.data;
        } catch (err) {
            error.value = err.response?.data?.message || 'Excel oluşturulurken bir hata oluştu';
            throw err;
        } finally {
            loading.value = false;
        }
    };

    return {
        loading,
        error,
        fetchDateRangeReport,
        fetchMonthlyReport,
        fetchCustomerReport,
        fetchCustomerProductReport,
        fetchCustomerPaymentReport,
        fetchTrendReport,
        exportToPdf,
        exportToExcel,
    };
}
