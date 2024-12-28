<script setup>
import { ref,computed } from 'vue';
import AppMenuItem from './AppMenuItem.vue';
import { useStore } from 'vuex';
const store = useStore();

const userRole = computed(() => store.state.user.role);

const model = ref([

    {
        label: 'Home',
        items: [
            { label: 'Ana Sayfa', icon: 'pi pi-fw pi-home', to: '/sys' }
        ]
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
                label: 'Giriş-Çıkış Yönetimi',
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
                    { label: 'Müşteri Yönetimi', icon: 'pi pi-users', to: '/sys/customers' },
                    { label: 'Sipariş Yönetimi', icon: 'pi pi-book', to: '/sys/orders' }
                ]
            }
        ]
    },
    {
        items: [
            {
                label: 'Geri Dönüşüm Kayıtları',
                icon: 'pi pi-replay',
                to: '/sys/recycling'
            }
        ]

    },
    {
        items: [
            {
                label: 'Hammadde Tedarik Kayıtları',
                icon: 'pi pi-truck',
                to: '/sys/suppliers'
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
                to: '/sys/worker/production'
            }
        ]
    },
    {
        items: [
            {
                label: 'Vardiyalar',
                icon: 'pi pi-calendar-plus',
                to: '/sys/worker/shifts'
            }
        ]
    }


]);


if (userRole.value !== 'admin') {
    // Admin olmayan kullanıcılar için 'Kullanıcı Yönetimi' sekmesini kaldırıyoruz
    model.value = model.value.filter(menu => {
        if (menu.items) {
            menu.items = menu.items.filter(subMenu =>
                subMenu.label !== 'Kullanıcı Yönetimi' &&
                subMenu.label !== 'PACS Yönetimi' &&
                subMenu.label !== 'Vardiya Yönetimi' &&
                subMenu.label !== 'Üretim Yönetimi' &&
                subMenu.label !== 'Stok Yönetimi' &&
                subMenu.label !== 'Satış Yönetimi' &&
                subMenu.label !== 'Geri Dönüşüm Kayıtları' &&
                subMenu.label !== 'Hammadde Tedarik Kayıtları' &&
                subMenu.label !== 'Raporlar'
            );
        }
        return menu.items.length > 0 || !menu.items;
    });
}

if (userRole.value !== 'worker') {
    // Worker olmayan kullanıcılar için 'Üretim Ekle' sekmesini kaldırıyoruz
    model.value = model.value.filter(menu => {
        if (menu.items) {
            menu.items = menu.items.filter(subMenu => subMenu.label !== 'Üretim Ekle' && subMenu.label !== 'Vardiyalar');
        }
        return menu.items.length > 0 || !menu.items;
    });
}
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
