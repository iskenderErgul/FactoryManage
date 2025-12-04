import { ref } from 'vue';
import axios from 'axios';

export function useProductionReports() {
    const loading = ref(false);
    const error = ref(null);

    const handleRequest = async (requestFn) => {
        loading.value = true;
        error.value = null;
        try {
            const response = await requestFn();
            return response.data;
        } catch (err) {
            error.value = err.response?.data?.message || 'Bir hata oluştu';
            throw err;
        } finally {
            loading.value = false;
        }
    };

    const fetchDateRangeReport = async (startDate, endDate, filters = {}) => {
        return handleRequest(() => 
            axios.get('/api/reports/production/date-range', {
                params: { start_date: startDate, end_date: endDate, ...filters }
            })
        );
    };

    const fetchWorkerEfficiencyReport = async (startDate, endDate, userId = null) => {
        return handleRequest(() => 
            axios.get('/api/reports/production/worker-efficiency', {
                params: { start_date: startDate, end_date: endDate, user_id: userId }
            })
        );
    };

    const fetchProductAnalysisReport = async (startDate, endDate, productId = null) => {
        return handleRequest(() => 
            axios.get('/api/reports/production/product-analysis', {
                params: { start_date: startDate, end_date: endDate, product_id: productId }
            })
        );
    };

    const fetchTrendAnalysisReport = async (year = null, month = null) => {
        return handleRequest(() => 
            axios.get('/api/reports/production/trend-analysis', {
                params: { year, month }
            })
        );
    };

    const fetchRealtimeDashboard = async () => {
        return handleRequest(() => 
            axios.get('/api/reports/production/realtime-dashboard')
        );
    };

    const fetchExecutiveSummary = async (startDate, endDate) => {
        return handleRequest(() => 
            axios.get('/api/reports/production/executive-summary', {
                params: { start_date: startDate, end_date: endDate }
            })
        );
    };

    const fetchWorkerDetailReport = async (userId, startDate, endDate) => {
        return handleRequest(() => 
            axios.get(`/api/reports/production/worker-detail/${userId}`, {
                params: { start_date: startDate, end_date: endDate }
            })
        );
    };

    return {
        loading,
        error,
        fetchDateRangeReport,
        fetchWorkerEfficiencyReport,
        fetchProductAnalysisReport,
        fetchTrendAnalysisReport,
        fetchRealtimeDashboard,
        fetchExecutiveSummary,
        fetchWorkerDetailReport,
    };
}
