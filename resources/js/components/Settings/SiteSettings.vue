<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import Card from 'primevue/card';
import InputText from 'primevue/inputtext';
import Button from 'primevue/button';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Toast from 'primevue/toast';
import { useToast } from 'primevue/usetoast';

const toast = useToast();

const loading = ref(false);
const saving = ref(false);
const settings = ref([]);
// Seçilen ama henüz yüklenmemiş dosyalar
const pendingFiles = ref({
    site_logo: null,
    site_favicon: null
});

// Bu ekranda sadece belirli anahtarlar kullanılacak
const REQUIRED_KEYS = [
    { group: 'general', key: 'site_name', label: 'Firma Adı' },
    { group: 'general', key: 'site_title', label: 'Sekme Başlığı' },
    { group: 'general', key: 'site_logo', label: 'Site Logo URL' },
    { group: 'general', key: 'site_favicon', label: 'Sekme İkonu (Favicon URL)' }
];

const loadSettings = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/api/site-settings');

        let loaded = Array.isArray(response.data) ? [...response.data] : [];

        // Sadece bu ekrandaki alanlar kalsın, eksik olanlar eklensin
        REQUIRED_KEYS.forEach((item) => {
            const exists = loaded.find(
                (s) => s.group === item.group && s.key === item.key
            );
            if (!exists) {
                loaded.push({
                    id: null,
                    group: item.group,
                    key: item.key,
                    value: ''
                });
            }
        });

        settings.value = loaded;
    } catch (e) {
        console.error(e);
        toast.add({ severity: 'error', summary: 'Hata', detail: 'Site ayarları yüklenemedi.', life: 3000 });
    } finally {
        loading.value = false;
    }
};

const saveAll = async () => {
    saving.value = true;
    try {
        // Önce varsa bekleyen dosyaları yükle
        const uploadKeys = ['site_logo', 'site_favicon'];
        for (const key of uploadKeys) {
            const file = pendingFiles.value[key];
            if (file) {
                const formData = new FormData();
                formData.append('key', key);
                formData.append('file', file);

                const response = await axios.post('/api/site-settings/upload-file', formData, {
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    }
                });

                // Ayar listesindeki değerleri güncelle
                const setting = settings.value.find(s => s.key === key);
                if (setting) {
                    setting.value = response.data.url;
                }
            }
        }

        // Dosyalar yüklendikten sonra text alanlarını toplu kaydet
        const payload = settings.value.map(s => {
            let value = s.value;

            // Logo / favicon için: tam URL geldiyse sadece path kısmını bırak
            if ((s.key === 'site_logo' || s.key === 'site_favicon') && value) {
                const url = new URL(value, window.location.origin);
                // /storage/site/abc.jpg -> site/abc.jpg
                value = url.pathname.replace(/^\/?storage\//, '');
            }

            return {
                group: s.group,
                key: s.key,
                value
            };
        });

        await axios.post('/api/site-settings', { settings: payload });
        toast.add({ severity: 'success', summary: 'Kaydedildi', detail: 'Site ayarları güncellendi.', life: 3000 });
        await loadSettings();
    } catch (e) {
        console.error(e);
        toast.add({ severity: 'error', summary: 'Hata', detail: 'Ayarlar kaydedilemedi.', life: 3000 });
    } finally {
        saving.value = false;
    }
};

const selectFileForSetting = (setting, event) => {
    const file = event.target.files[0];
    if (!file) {
        return;
    }

    pendingFiles.value[setting.key] = file;
    toast.add({
        severity: 'info',
        summary: 'Dosya seçildi',
        detail: 'Kaydet tuşuna bastığınızda yüklenecek.',
        life: 2500
    });
};

// Görsel için src değerini hesaplar (hem eski full URL'leri hem yeni relative path'leri destekler)
const resolveImageSrc = (value) => {
    if (!value) return null;

    // Eğer http ile başlıyorsa zaten tam URL'dir
    if (value.startsWith('http://') || value.startsWith('https://')) {
        return value;
    }

    // Değilse /storage/... formatında relative path kullan
    // value -> "site/xyz.jpg" ise "/storage/site/xyz.jpg" olur
    return `/storage/${value.replace(/^\/?storage\//, '')}`;
};

onMounted(() => {
    loadSettings();
});
</script>

<template>
    <div class="site-settings-page">
        <Toast />

        <Card>
            <template #title>Site Ayarları</template>
            <template #content>
                <div class="actions-row">
                    <Button
                        label="Kaydet"
                        icon="pi pi-save"
                        class="p-button-success"
                        :loading="saving"
                        @click="saveAll" />
                </div>

                <DataTable
                    :value="settings"
                    :loading="loading"
                    responsiveLayout="scroll"
                >
                    <Column field="key" header="Alan">
                        <template #body="{ data }">
                            <span v-if="data.key === 'site_name'">Firma Adı</span>
                            <span v-else-if="data.key === 'site_title'">Sekme Başlığı</span>
                            <span v-else-if="data.key === 'site_logo'">Site Logo </span>
                            <span v-else-if="data.key === 'site_favicon'">Sekme İkonu</span>
                            <span v-else>{{ data.key }}</span>
                        </template>
                    </Column>
                    <Column field="value" header="Değer">
                        <template #body="{ data }">
                            <!-- Logo ve favicon için dosya yükleme -->
                            <div v-if="data.key === 'site_logo' || data.key === 'site_favicon'" class="file-cell">
                                <div v-if="data.value" class="preview">
                                    <img :src="resolveImageSrc(data.value)" alt="preview" />
                                </div>
                                <input
                                    type="file"
                                    accept="image/*,image/x-icon"
                                    @change="(e) => selectFileForSetting(data, e)" />
                            </div>

                            <!-- Diğer alanlar için metin -->
                            <InputText
                                v-else
                                v-model="data.value"
                                class="w-full" />
                        </template>
                    </Column>
                </DataTable>
            </template>
        </Card>
    </div>
</template>

<style scoped>
.site-settings-page {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.actions-row {
    display: flex;
    justify-content: flex-end;
    margin-bottom: 1rem;
}

.file-cell {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.file-cell .preview img {
    max-height: 40px;
    max-width: 160px;
    object-fit: contain;
    border-radius: 4px;
    background: #111827;
    padding: 4px;
}

@media (max-width: 900px) {
    .actions-row {
        justify-content: stretch;
    }
}
</style>

