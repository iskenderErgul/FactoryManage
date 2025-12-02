<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { useLayout } from '@/layout/composables/layout';
import { useRouter } from 'vue-router';
import store from "@/store/index.js";
import axios from 'axios';

const { layoutConfig, onMenuToggle } = useLayout();

const outsideClickListener = ref(null);
const topbarMenuActive = ref(false);
const router = useRouter();

const companyName = ref('');

onMounted(async () => {
    bindOutsideClickListener();

    try {
        const response = await axios.get('/api/site-settings', {
            params: { group: 'general' }
        });
        const siteNameSetting = response.data.find(s => s.key === 'site_name');
        if (siteNameSetting && siteNameSetting.value) {
            companyName.value = siteNameSetting.value;
        } else {
            companyName.value = store.state.user.name || 'Özergül Plastik';
        }
    } catch (e) {
        companyName.value = store.state.user.name || 'Özergül Plastik';
    }
});

onBeforeUnmount(() => {
    unbindOutsideClickListener();
});

const logoUrl = computed(() => {
    return `layout/images/${layoutConfig.darkTheme.value ? 'logo-white' : 'logo-dark'}.svg`;
});

const onTopBarMenuButton = () => {
    topbarMenuActive.value = !topbarMenuActive.value;
};
const onSettingsClick = () => {
    topbarMenuActive.value = false;
    router.push('/documentation');
};
const topbarMenuClasses = computed(() => {
    return {
        'layout-topbar-menu-mobile-active': topbarMenuActive.value
    };
});

const bindOutsideClickListener = () => {
    if (!outsideClickListener.value) {
        outsideClickListener.value = (event) => {
            if (isOutsideClicked(event)) {
                topbarMenuActive.value = false;
            }
        };
        document.addEventListener('click', outsideClickListener.value);
    }
};
const unbindOutsideClickListener = () => {
    if (outsideClickListener.value) {
        document.removeEventListener('click', outsideClickListener);
        outsideClickListener.value = null;
    }
};
const isOutsideClicked = (event) => {
    if (!topbarMenuActive.value) return;

    const sidebarEl = document.querySelector('.layout-topbar-menu');
    const topbarEl = document.querySelector('.layout-topbar-menu-button');

    return !(sidebarEl.isSameNode(event.target) || sidebarEl.contains(event.target) || topbarEl.isSameNode(event.target) || topbarEl.contains(event.target));
};

const logout = () => {
    store.dispatch('logout');
};
</script>

<template>
    <div class="layout-topbar">
        <router-link to="/sys" class="layout-topbar-logo">
            <img src="../../../public/Logo.png" alt="Logo" class="w-2 h-2 " />
            <span>{{ companyName }}</span>
        </router-link>

        <button class="p-link layout-menu-button layout-topbar-button" @click="onMenuToggle()">
            <i class="pi pi-bars"></i>
        </button>

        <button class="p-link layout-topbar-menu-button layout-topbar-button" @click="onTopBarMenuButton()">
            <i class="pi pi-ellipsis-v"></i>
        </button>

        <div class="layout-topbar-menu">
            <button @click="logout" class="p-link layout-topbar-button">
                <i class="pi pi-sign-out"></i>

            </button>
        </div>
    </div>
</template>

<style lang="scss" scoped></style>
