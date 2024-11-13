<template>
    <h3 class="text-center">Vardiyanın Tarih Aralıgı gelecek</h3>
    <h3 class="text-center">11/11/2024 - 17/11/2024 </h3>
    <div class="flex justify-content-between flex-wrap border-round border-2 border-solid border-300">

        <div class="w-3 p-4 bg-gray-800 rounded-lg">
            <h3 class="text-xl font-semibold text-center text-white">Sabah</h3>
            <h3 class="text-xl font-semibold mb-4 text-center text-white">08:00:00 - 15:59:59</h3>
            <div class="space-y-2">
                <div v-for="assignment in morningAssignments" :key="assignment.id" class="p-4 bg-blue-700 rounded-lg shadow-md">
                    <p class="text-white">{{ assignment.user.name }}</p>
                </div>
            </div>
        </div>

        <!-- Noon Column -->
        <div class="w-3 p-4 bg-gray-800 rounded-lg">
            <h3 class="text-xl font-semibold mb-4 text-center text-white">Öğlen</h3>
            <h3 class="text-xl font-semibold mb-4 text-center text-white">16:00:00 - 23:59:59</h3>
            <div class="space-y-2">
                <div v-for="assignment in noonAssignments" :key="assignment.id" class="p-4 bg-green-700 rounded-lg shadow-md">
                    <p class="text-white">{{ assignment.user.name }}</p>
                </div>
            </div>
        </div>

        <!-- Evening Column -->
        <div class="w-3 p-4 bg-gray-800 rounded-lg">
            <h3 class="text-xl font-semibold mb-4 text-center text-white">Akşam</h3>
            <h3 class="text-xl font-semibold mb-4 text-center text-white">00:00:00 - 07:59:59</h3>
            <div class="space-y-2">
                <div v-for="assignment in eveningAssignments" :key="assignment.id" class="p-4 bg-yellow-700 rounded-lg shadow-md mb-1">
                    <p class="text-white">{{ assignment.user.name }}</p>
                </div>
            </div>
        </div>

        <!-- Overtime Column -->
        <div class="w-3 p-4 bg-gray-800 rounded-lg">
            <h3 class="text-xl font-semibold mb-4 text-center text-white">Mesai(Pazar)</h3>
            <h3 class="text-xl font-semibold mb-4 text-center text-white">08:00:00 - 19:00:00</h3>
            <div class="space-y-2">
                <div v-for="assignment in overtimeAssignments" :key="assignment.id" class="p-4 bg-red-700 rounded-lg shadow-md ">
                    <p class="text-white">{{ assignment.user.name }}</p>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from "axios";

const morningAssignments = ref([]);
const noonAssignments = ref([]);
const eveningAssignments = ref([]);
const overtimeAssignments = ref([]);

const shifts = ref([]);

const fetchShifts = async () => {
    try {
        const response = await axios.get('/api/shift/shifts');
        shifts.value = response.data;
        categorizeAssignments(); // Categorize the shifts after fetching
    } catch (error) {
        console.error(error);
        toast.value.add({ severity: 'error', summary: 'Hata', detail: 'Şablonlar yüklenemedi', life: 3000 });
    }
}

// Categorize the fetched shift data into the correct columns
const categorizeAssignments = () => {
    shifts.value.forEach(shift => {
        if (shift.template.name === "Sabah") {
            morningAssignments.value = shift.shift_assignments;
        } else if (shift.template.name === "Öğlen") {
            noonAssignments.value = shift.shift_assignments;
        } else if (shift.template.name === "Akşam") {
            eveningAssignments.value = shift.shift_assignments;
        } else if (shift.template.name === "Mesai") {
            overtimeAssignments.value = shift.shift_assignments;
        }
    });
}

onMounted(() => {
    fetchShifts();
});
</script>
