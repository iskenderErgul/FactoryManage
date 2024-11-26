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
                    'rgba(139, 92, 246, 0.4)',
                    'rgba(34, 197, 94, 0.4)',
                    'rgba(251, 146, 60, 0.4)',
                    'rgba(236, 72, 153, 0.4)',
                    'rgba(16, 185, 129, 0.4)',
                    'rgba(96, 165, 250, 0.4)',
                    'rgba(52, 211, 153, 0.4)',
                    'rgba(250, 204, 21, 0.4)',
                    'rgba(192, 38, 211, 0.4)',
                    'rgba(59, 130, 246, 0.4)',
                    'rgba(239, 68, 68, 0.4)',
                    'rgba(156, 163, 175, 0.4)',
                    'rgba(217, 70, 239, 0.4)',
                    'rgba(24, 78, 119, 0.4)',
                    'rgba(245, 158, 11, 0.4)',
                    'rgba(168, 85, 247, 0.4)',
                    'rgba(51, 65, 85, 0.4)'
                ],
                borderColor: [
                    'rgb(249, 115, 22)',
                    'rgb(6, 182, 212)',
                    'rgb(107, 114, 128)',
                    'rgb(139, 92, 246)',
                    'rgb(34, 197, 94)',
                    'rgb(251, 146, 60)',
                    'rgb(236, 72, 153)',
                    'rgb(16, 185, 129)',
                    'rgb(96, 165, 250)',
                    'rgb(52, 211, 153)',
                    'rgb(250, 204, 21)',
                    'rgb(192, 38, 211)',
                    'rgb(59, 130, 246)',
                    'rgb(239, 68, 68)',
                    'rgb(156, 163, 175)',
                    'rgb(217, 70, 239)',
                    'rgb(24, 78, 119)',
                    'rgb(245, 158, 11)',
                    'rgb(168, 85, 247)',
                    'rgb(51, 65, 85)'
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
