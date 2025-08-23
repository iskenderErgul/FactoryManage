<template>
    <div class="shifts-container">
        <!-- Header with Navigation -->
        <div class="card mb-4">
            <div class="flex justify-content-between align-items-center p-4">
                <div class="flex align-items-center gap-3">
                    <div class="text-center">
                        <h3 class="m-0">Vardiya Takvimi</h3>
                        <p class="text-600 m-0">{{ formatWeekRange() }}</p>
                    </div>
                </div>

                <div class="flex gap-2">
                    <Button
                        label="Vardiya Değiştir"
                        @click="rotateShifts"
                        severity="success"
                        :loading="loading"
                        v-tooltip="'Mevcut vardiyaları sıralı şekilde değiştir (1→2→3→4→1...)'"
                    />
                    <Button
                        icon="pi pi-refresh"
                        @click="fetchShiftData"
                        outlined
                        :loading="loading"
                        v-tooltip="'Yenile'"
                    />
                </div>
            </div>
        </div>

        <!-- Single Week View -->
        <div class="card">
            <div class="shifts-grid">
                <div
                    v-for="(dayShifts, date) in groupedShifts"
                    :key="date"
                    class="day-column"
                    :class="{ 'current-day': isToday(date) }"
                >
                    <div class="day-header">
                        <strong>{{ formatDayName(date) }}</strong>
                        <small class="block text-600">{{ formatDate(date) }}</small>
                        <Tag
                            v-if="isToday(date)"
                            value="Bugün"
                            severity="success"
                            class="mt-1"
                            size="small"
                        />
                    </div>

                    <div class="shifts-list">
                        <div
                            v-for="shift in dayShifts"
                            :key="shift.id"
                            class="shift-card"
                            :class="getShiftCardClass(shift)"
                        >
                            <div class="shift-header">
                                <span class="shift-name">{{ getShiftName(shift) }}</span>
                                <small class="shift-time">
                                    {{ getShiftTime(shift) }}
                                </small>
                            </div>

                            <div class="assigned-users">
                                <div
                                    v-for="assignment in getShiftAssignments(shift)"
                                    :key="assignment.id"
                                    class="user-chip"
                                >
                                    <Avatar
                                        :label="getUserInitials(assignment.user?.name || 'XX')"
                                        size="small"
                                        class="mr-2"
                                    />
                                    <span>{{ assignment.user?.name || 'Bilinmeyen Kullanıcı' }}</span>
                                </div>
                                <div v-if="getShiftAssignments(shift).length === 0" class="no-assignment">
                                    <i class="pi pi-user-plus text-400"></i>
                                    <span class="text-400">Atama yok</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Loading Overlay -->
        <div v-if="loading" class="loading-overlay">
            <ProgressSpinner />
        </div>

        <Toast ref="toast" />
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';
import Button from 'primevue/button';
import Card from 'primevue/card';
import Badge from 'primevue/badge';
import Tag from 'primevue/tag';
import Avatar from 'primevue/avatar';
import Toast from 'primevue/toast';
import ProgressSpinner from 'primevue/progressspinner';

const toast = ref(null);
const loading = ref(false);
const shifts = ref([]);
const currentWeekStart = ref(getMonday(new Date()));

onMounted(() => {
    fetchShiftData();
});

// Helper function to get Monday of the week
function getMonday(date) {
    const d = new Date(date);
    const day = d.getDay();
    const diff = d.getDate() - day + (day === 0 ? -6 : 1);
    return new Date(d.setDate(diff));
}

const fetchShiftData = async () => {
    loading.value = true;
    try {
        const weekStart = currentWeekStart.value.toISOString().split('T')[0];
        const weekEnd = new Date(currentWeekStart.value);
        weekEnd.setDate(weekEnd.getDate() + 6);
        const weekEndStr = weekEnd.toISOString().split('T')[0];

        const response = await axios.get(`/api/shift/shifts/${weekStart}/${weekEndStr}`);

        if (response.data.shifts) {
            shifts.value = response.data.shifts;
        } else {
            shifts.value = response.data;
        }

        // Veri yapısını kontrol et
        console.log('Gelen veri yapısı:', shifts.value);

    } catch (error) {
        console.error('Vardiya verileri yüklenirken hata:', error);
        toast.value.add({
            severity: 'error',
            summary: 'Hata',
            detail: 'Vardiya verileri yüklenemedi',
            life: 3000
        });
    } finally {
        loading.value = false;
    }
};

// Güvenli helper fonksiyonlar
const getShiftName = (shift) => {
    return shift?.template?.name || shift?.name || 'Bilinmeyen Vardiya';
};

const getShiftTime = (shift) => {
    const startTime = shift?.template?.start_time || shift?.start_time || '00:00';
    const endTime = shift?.template?.end_time || shift?.end_time || '00:00';

    return `${startTime.substring(0,5)} - ${endTime.substring(0,5)}`;
};

const getShiftAssignments = (shift) => {
    return shift?.shift_assignments || shift?.assignments || [];
};

const navigateWeeks = (weeks) => {
    const newDate = new Date(currentWeekStart.value);
    newDate.setDate(newDate.getDate() + (weeks * 7));
    currentWeekStart.value = getMonday(newDate);
    fetchShiftData();
};

const goToCurrentWeek = () => {
    currentWeekStart.value = getMonday(new Date());
    fetchShiftData();
};

const rotateShifts = async () => {
    loading.value = true;
    try {
        const response = await axios.post('/api/shift/rotate-current');

        toast.value.add({
            severity: 'success',
            summary: 'Başarılı',
            detail: response.data.message,
            life: 5000
        });

        // Rotasyon detaylarını console'a yazdır
        if (response.data.rotation_details) {
            console.log('Rotasyon Detayları:', response.data.rotation_details);
            console.log('Toplam Vardiya Sayısı:', response.data.total_templates);
            console.log('Vardiya İsimleri:', response.data.template_names);
            console.log('Gelecek Atamalar:', response.data.future_assignments_count);

            // Kullanıcıya detayları da göster
            setTimeout(() => {
                const detailMessage = `Değiştirilen: ${response.data.rotated_count} atama\nGelecek hafta atamaları: ${response.data.future_assignments_count}\nToplam ${response.data.total_templates} vardiya: ${response.data.template_names.join(', ')}`;
                
                toast.value.add({
                    severity: 'info',
                    summary: 'Rotasyon Detayları',
                    detail: detailMessage,
                    life: 6000
                });
            }, 1000);
        }

        await fetchShiftData(); // Verileri yenile

    } catch (error) {
        console.error('Rotasyon hatası:', error);
        toast.value.add({
            severity: 'error',
            summary: 'Hata',
            detail: error.response?.data?.message || 'Vardiya değiştirme başarısız',
            life: 3000
        });
    } finally {
        loading.value = false;
    }
};

const groupedShifts = computed(() => {
    const grouped = {};

    // Veriyi kontrol et
    if (!Array.isArray(shifts.value)) {
        console.warn('Shifts verisi array değil:', shifts.value);
        return grouped;
    }

    shifts.value.forEach(shift => {
        // Shift nesnesini kontrol et
        if (!shift || !shift.date) {
            console.warn('Geçersiz shift verisi:', shift);
            return;
        }

        if (!grouped[shift.date]) {
            grouped[shift.date] = [];
        }
        grouped[shift.date].push(shift);
    });

    // Tarihe göre sırala ve her gün içinde vardiyaları saat sırasına göre sırala
    Object.keys(grouped).forEach(date => {
        grouped[date].sort((a, b) => {
            const timeA = getShiftTime(a).split(' - ')[0];
            const timeB = getShiftTime(b).split(' - ')[0];
            return timeA.localeCompare(timeB);
        });
    });

    return grouped;
});

const weekStats = computed(() => {
    const uniqueUsers = new Set();
    let totalAssignments = 0;

    if (!Array.isArray(shifts.value)) return { totalUsers: 0, assignedUsers: 0, totalAssignments: 0 };

    shifts.value.forEach(shift => {
        const assignments = getShiftAssignments(shift);
        assignments.forEach(assignment => {
            if (assignment?.user?.id) {
                uniqueUsers.add(assignment.user.id);
                totalAssignments++;
            }
        });
    });

    return {
        totalUsers: uniqueUsers.size,
        assignedUsers: uniqueUsers.size,
        totalAssignments: totalAssignments
    };
});

const isCurrentWeek = computed(() => {
    const today = new Date();
    const currentMonday = getMonday(today);
    return currentWeekStart.value.toDateString() === currentMonday.toDateString();
});

const formatWeekRange = () => {
    const weekEnd = new Date(currentWeekStart.value);
    weekEnd.setDate(weekEnd.getDate() + 6);

    return `${formatDate(currentWeekStart.value.toISOString().split('T')[0])} - ${formatDate(weekEnd.toISOString().split('T')[0])}`;
};

const formatDate = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('tr-TR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    });
};

const formatDayName = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('tr-TR', { weekday: 'long' });
};

const isToday = (dateString) => {
    const today = new Date().toISOString().split('T')[0];
    return dateString === today;
};

const getShiftCardClass = (shift) => {
    const shiftName = getShiftName(shift);
    
    // Dinamik renk sistemi - vardiya ismine göre renk belirleme
    const classes = {
        'Sabah': 'shift-morning',
        'Gündüz': 'shift-day',
        'Öğlen': 'shift-noon',
        'Akşam': 'shift-evening',
        'Gece': 'shift-night',
        'Mesai': 'shift-overtime',
        '1. Vardiya': 'shift-morning',
        '2. Vardiya': 'shift-day',
        '3. Vardiya': 'shift-noon',
        '4. Vardiya': 'shift-evening',
        '5. Vardiya': 'shift-night',
        '6. Vardiya': 'shift-overtime'
    };
    
    // Eğer tam eşleşme yoksa, vardiya numarasını kontrol et
    if (!classes[shiftName]) {
        // Vardiya isminde numara var mı kontrol et
        const numberMatch = shiftName.match(/(\d+)\.?\s*Vardiya/);
        if (numberMatch) {
            const shiftNumber = parseInt(numberMatch[1]);
            const colorClasses = ['shift-morning', 'shift-day', 'shift-noon', 'shift-evening', 'shift-night', 'shift-overtime'];
            return colorClasses[(shiftNumber - 1) % colorClasses.length];
        }
    }
    
    return classes[shiftName] || 'shift-default';
};

const getUserInitials = (name) => {
    if (!name || typeof name !== 'string') return 'XX';
    return name.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2);
};
</script>

<style scoped>
/* Önceki CSS kodları aynı kalacak */
.shifts-container {
    padding: 1rem;
    max-width: 100%;
    overflow-x: hidden;
}

.shifts-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 0.8rem;
    min-height: 500px;
    width: 100%;
}

.day-column {
    border: 1px solid var(--surface-border);
    border-radius: 12px;
    padding: 0.8rem;
    background: var(--surface-card);
    display: flex;
    flex-direction: column;
    transition: all 0.3s ease;
    min-width: 0;
}

.day-column.current-day {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 2px rgba(var(--primary-color-rgb), 0.1);
    background: linear-gradient(145deg, var(--surface-card), rgba(var(--primary-color-rgb), 0.02));
}

.day-header {
    text-align: center;
    padding-bottom: 1rem;
    border-bottom: 1px solid var(--surface-border);
    margin-bottom: 1rem;
    flex-shrink: 0;
}

.shifts-list {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 0.8rem;
    overflow-y: auto;
    max-height: 400px;
}

.shift-card {
    border-radius: 10px;
    padding: 0.8rem;
    color: white;
    font-size: 0.875rem;
    box-shadow: 0 3px 6px rgba(0,0,0,0.15);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    min-height: 100px;
    display: flex;
    flex-direction: column;
}

.shift-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}

.shift-morning, .shift-day {
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
}

.shift-noon {
    background: linear-gradient(135deg, #10b981, #047857);
}

.shift-evening {
    background: linear-gradient(135deg, #f59e0b, #d97706);
}

.shift-night {
    background: linear-gradient(135deg, #6366f1, #4338ca);
}

.shift-overtime {
    background: linear-gradient(135deg, #ef4444, #dc2626);
}

.shift-default {
    background: linear-gradient(135deg, #6b7280, #4b5563);
}

.shift-header {
    display: flex;
    flex-direction: column;
    gap: 0.3rem;
    margin-bottom: 0.8rem;
    flex-shrink: 0;
}

.shift-name {
    font-weight: 700;
    font-size: 0.9rem;
    text-align: center;
}

.shift-time {
    font-size: 0.75rem;
    opacity: 0.9;
    text-align: center;
    font-weight: 500;
}

.assigned-users {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    flex: 1;
}

.user-chip {
    display: flex;
    align-items: center;
    background: rgba(255, 255, 255, 0.25);
    border-radius: 8px;
    padding: 0.5rem 0.6rem;
    font-size: 0.75rem;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.1);
    transition: all 0.2s ease;
    min-height: 32px;
    width: 100%;
    box-sizing: border-box;
}

.user-chip:hover {
    background: rgba(255, 255, 255, 0.35);
    transform: scale(1.01);
}

.user-chip .p-avatar {
    margin-right: 0.5rem !important;
    flex-shrink: 0;
}

.user-chip span {
    font-weight: 500;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    flex: 1;
}

.no-assignment {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1.5rem;
    border: 2px dashed rgba(255, 255, 255, 0.3);
    border-radius: 8px;
    font-size: 0.8rem;
    opacity: 0.8;
    flex-direction: column;
    gap: 0.5rem;
    text-align: center;
}

.no-assignment i {
    font-size: 1.2rem;
}

.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
}

@media (max-width: 1400px) {
    .shifts-grid {
        gap: 0.6rem;
    }
    .day-column {
        padding: 0.7rem;
    }
}

@media (max-width: 1200px) {
    .shifts-grid {
        grid-template-columns: repeat(4, 1fr);
        gap: 0.5rem;
    }
    .day-column {
        padding: 0.6rem;
    }
    .shift-card {
        min-height: 90px;
        padding: 0.7rem;
    }
    .user-chip {
        padding: 0.4rem 0.5rem;
        font-size: 0.7rem;
        min-height: 28px;
    }
}

@media (max-width: 768px) {
    .shifts-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 0.4rem;
    }
    .shifts-container {
        padding: 0.5rem;
    }
    .day-column {
        padding: 0.5rem;
    }
    .shift-card {
        min-height: 80px;
        padding: 0.6rem;
    }
    .user-chip {
        padding: 0.4rem 0.5rem;
        font-size: 0.7rem;
        min-height: 26px;
    }
    .shift-name {
        font-size: 0.8rem;
    }
    .shift-time {
        font-size: 0.7rem;
    }
}

@media (max-width: 480px) {
    .shifts-grid {
        grid-template-columns: 1fr;
        gap: 0.5rem;
    }
    .shifts-list {
        max-height: 300px;
    }
}

.shifts-list::-webkit-scrollbar {
    width: 4px;
}

.shifts-list::-webkit-scrollbar-track {
    background: rgba(255, 255, 255, 0.1);
    border-radius: 4px;
}

.shifts-list::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.3);
    border-radius: 4px;
}

.shifts-list::-webkit-scrollbar-thumb:hover {
    background: rgba(255, 255, 255, 0.5);
}
</style>
