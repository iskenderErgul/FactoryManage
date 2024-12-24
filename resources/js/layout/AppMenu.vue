<script setup>
import { ref } from 'vue';
import AppMenuItem from './AppMenuItem.vue';

const model = ref([
    {
        label: 'Home',
        items: [{ label: 'Ana Sayfa', icon: 'pi pi-fw pi-home', to: '/sys' }]
    },
    {

        items: [
            {
                label: 'Kullanıcı Yönetimi',
                icon: 'pi pi-users',
                items: [
                    { label: 'Kullanıcı Yönetimi', icon: 'pi pi-users', to: '/sys/manage-users' },
                    { label: 'Kullanıcı Giriş Kayıtları', icon: 'pi pi-file', to: '/sys/user-log' }
                ]
            }
        ]
    },
    {

        items: [
            {
                label: 'PACS Yönetimi',
                icon: 'pi pi-inbox',
                items: [
                    { label: 'PACS Giriş Kayıtları', icon: 'pi pi-inbox', to: '/sys/pacs-entries' },
                    { label: 'Giriş-Çıkış Logları', icon: 'pi pi-calendar', to: '/sys/pacs-log' }
                ]
            }
        ]
    },
    {

        items: [
            {
                label: 'Vardiya Yönetimi',
                icon: 'pi pi-calendar-times',
                items: [
                    { label: 'Vardiya Şablonları', icon: 'pi pi-calendar-plus', to: '/sys/shift-templates' },
                    { label: 'Vardiya Kaydı Atamaları', icon: 'pi pi-calendar-times', to: '/sys/shift-assignments' },
                    { label: 'Vardiya Kayıtları', icon: 'pi pi-table', to: '/sys/shifts' },

                ]
            }
        ]
    },
    {

        items: [
            {
                label: 'Üretim Yönetimi',
                icon: 'pi pi-chart-line',
                items: [
                    { label: 'Üretim Takibi', icon: 'pi pi-chart-line', to: '/sys/production' },
                    { label: 'Üretim Logları', icon: 'pi pi-file', to: '/sys/production-log' },
                    { label: 'Makine Yönetimi', icon: 'pi pi-cog', to: '/sys/machine-management' },
                    { label: 'Maliyet Yönetimi', icon: 'pi pi-money-bill', to: '/sys/cost-management' }
                ]
            }
        ]
    },
    {

        items: [
            {
                label: 'Stok Yönetimi',
                icon: 'pi pi-box',
                items: [
                    { label: 'Ürün Yönetimi', icon: 'pi pi-box', to: '/sys/product-management' },
                    { label: 'Stok Hareketleri', icon: 'pi pi-th-large', to: '/sys/stock-movements' },
                    { label: 'Stok Hareketleri Logları', icon: 'pi pi-file', to: '/sys/stock-movements-log' }
                ]
            }
        ]
    },
    {

        items: [
            {
                label: 'Satış Yönetimi',
                icon: 'pi pi-shopping-cart',
                items: [
                    { label: 'Satış Yönetimi', icon: 'pi pi-shopping-cart', to: '/sys/sales' },
                    { label: 'Satış Logları', icon: 'pi pi-file', to: '/sys/sales-log' },
                    { label: 'Müşteri Yönetimi', icon: 'pi pi-users', to: '/sys/customers' }
                ]
            }
        ]
    },
    {

        items: [
            {
                label: 'Raporlar',
                icon: 'pi pi-chart-bar',
                items: [
                    { label: 'Üretim Raporları', icon: 'pi pi-chart-bar', to: '/sys/reports/production' },
                    { label: 'Satış Raporları', icon: 'pi pi-chart-bar', to: '/sys/reports/sales' },
                    { label: 'Stok Raporları', icon: 'pi pi-chart-bar', to: '/sys/reports/stocks' },
                    { label: 'Finansal Raporlar', icon: 'pi pi-chart-bar', to: '/sys/reports/financial' }
                ]
            }
        ]
    },
    {

        items: [
            {
                label: 'Üretim Ekle',
                icon: 'pi pi-chart-line',
                items: [
                    { label: 'Üretim Ekle', icon: 'pi pi-chart-line', to: '/sys/worker/production' },
                    { label: 'Vardiyalar', icon: 'pi pi-calendar-plus', to: '/sys/worker/shifts' },

                ]
            }
        ]
    },

]);
</script>

<template>
    <ul class="layout-menu">
        <template v-for="(item, i) in model" :key="i">
            <app-menu-item v-if="!item.separator" :item="item" :index="i"></app-menu-item>
            <li v-if="item.separator" class="menu-separator"></li>
        </template>
    </ul>
</template>

<style lang="scss" scoped></style>
