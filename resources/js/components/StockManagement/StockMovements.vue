<template>
    <div>
        <DataTable
            :value="formattedMovements"
            ref="dt"
            tableStyle="min-width: 50rem"
            paginator
            :rows="5"
            :rowsPerPageOptions="[5, 10, 20, 50]"
            paginatorTemplate="RowsPerPageDropdown FirstPageLink PrevPageLink CurrentPageReport NextPageLink LastPageLink"
            currentPageReportTemplate="{first} to {last} of {totalRecords}"
        >
            <template #header>
                <div class="text-end pb-4">
                    <Button icon="pi pi-external-link" label="Export" @click="exportCSV" />
                </div>
            </template>
            <Column field="id" header="Hareket Id" style="width: 10%" />
            <Column field="product.product_name" header="Ürün Id" style="width: 15%" />
            <Column field="movement_type" header="Hareket Türü" style="width: 15%" />
            <Column field="quantity" header="Miktar" style="width: 15%" />
            <Column field="related_process" header="İlgili İşlem" style="width: 25%" />
            <Column field="movement_date" header="Hareket Tarihi" style="width: 15%" />
            <Column field="created_at" header="Oluşturulma Tarihi" style="width: 25%" />
            <Column field="updated_at" header="Güncelleme Tarihi" style="width: 25%" />
        </DataTable>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import Button from 'primevue/button';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import axios from 'axios';
import { format } from 'date-fns';

const dt = ref();
const stockMovements = ref([]);

onMounted(() => {
    getStockMovements();
});

const getStockMovements = () => {
    axios.get('/api/getStockMovements').then(resp => {
        stockMovements.value = resp.data.reverse();
    });
};

const formattedMovements = computed(() => {
    return stockMovements.value.map(movement => ({
        ...movement,
        movement_date: movement.movement_date ? format(new Date(movement.movement_date), 'dd/MM/yyyy') : '',
        created_at: movement.created_at ? format(new Date(movement.created_at), 'dd/MM/yyyy') : '',
        updated_at: movement.updated_at ? format(new Date(movement.updated_at), 'dd/MM/yyyy') : ''
    }));
});


    const exportCSV = () => {
        axios.get('/api/stock-movement-export', { responseType: 'blob' })
            .then(response => {
                const url = window.URL.createObjectURL(new Blob([response.data]));
                const link = document.createElement('a');
                link.href = url;
                link.setAttribute('download', 'stock-movement.xlsx');
                document.body.appendChild(link);
                link.click();
            })
            .catch(error => {
                console.error('Export failed:', error);
            });


};
</script>
