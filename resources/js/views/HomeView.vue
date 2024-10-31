<template>
    <div class="card">
        <Chart type="bar" :data="chartData" :options="chartOptions" />
    </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import Chart from 'primevue/chart';
import axios from "axios";

const products = ref([]);
const chartData = ref();
const chartOptions = ref();

onMounted(() => {
    getAllProducts();
});

// Ürünleri API'den al
const getAllProducts = () => {
    axios.get('/api/products')
        .then(response => {
            products.value = response.data;
            chartData.value = setChartData();
            chartOptions.value = setChartOptions();
        })
        .catch(error => {
            console.error('Error fetching products:', error);
        });
};


const setChartData = () => {

    const stockQuantities = products.value.map(product => product.stock_quantity);
    const productNames = products.value.map(product => product.product_name);

    return {
        labels: productNames,
        datasets: [
            {
                label: 'Ürünler',
                data: stockQuantities,
                backgroundColor: [
                    'rgba(249, 115, 22, 0.4)',
                    'rgba(6, 182, 212, 0.4)',
                    'rgba(107, 114, 128, 0.4)',
                    'rgba(139, 92, 246, 0.4)'
                ],
                borderColor: [
                    'rgb(249, 115, 22)',
                    'rgb(6, 182, 212)',
                    'rgb(107, 114, 128)',
                    'rgb(139, 92, 246)'
                ],
                borderWidth: 1
            }

        ]
    };
};


const setChartOptions = () => {
    const documentStyle = getComputedStyle(document.documentElement);
    const textColor = documentStyle.getPropertyValue('--p-text-color'); // Koyu metin rengi
    const textColorSecondary = '#FFFFFF'; // Açık renk, eksen metni için beyaz
    const surfaceBorder = documentStyle.getPropertyValue('--p-content-border-color');

    return {
        plugins: {
            legend: {
                labels: {
                    color: '#FFFFFF'
                }
            }
        },
        scales: {
            x: {
                ticks: {
                    color: textColorSecondary
                },
                grid: {
                    color: surfaceBorder
                }
            },
            y: {
                beginAtZero: true,
                ticks: {
                    color: textColorSecondary
                },
                grid: {
                    color: surfaceBorder
                }
            }
        }
    };
}
</script>

<style>
.chart-container {
    width: 50vw; /* Genişliğin yarısı */
    height: 50vh; /* Yüksekliğin yarısı */
    margin: auto; /* Ortalanması için */
}
</style>
