<template>
    <div class="min-h-screen bg-base-200 py-8">
        <div class="max-w-7xl mx-auto px-4">
            <div class="bg-base-100 rounded-lg shadow-lg p-6">
                <h1 class="text-3xl font-bold mb-6">Тест панели врача</h1>
                
                <div class="mb-6">
                    <h2 class="text-xl font-semibold mb-3">Тестовые данные</h2>
                    <div class="bg-base-200 rounded p-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <h3 class="font-semibold mb-2">Врач:</h3>
                                <pre class="text-sm">{{ JSON.stringify(mockDoctor, null, 2) }}</pre>
                            </div>
                            <div>
                                <h3 class="font-semibold mb-2">Записи:</h3>
                                <pre class="text-sm">{{ JSON.stringify(mockAppointments, null, 2) }}</pre>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-6">
                    <button @click="testPanel" class="btn btn-primary">
                        Тестировать панель врача
                    </button>
                </div>

                <div v-if="showPanel" class="border-t pt-6">
                    <h2 class="text-xl font-semibold mb-4">Панель врача с тестовыми данными:</h2>
                    <DoctorPanel 
                        :doctor="mockDoctor" 
                        :appointments="mockAppointments" 
                    />
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import DoctorPanel from '@/Pages/Doctor/Panel.vue';

const showPanel = ref(false);

const mockDoctor = {
    id: 1,
    user: {
        id: 1,
        name: 'Доктор Иванов Иван Иванович',
        email: 'doctor@example.com'
    },
    specialty: {
        id: 1,
        name: 'Терапевт'
    }
};

const mockAppointments = [
    {
        id: 1,
        patient: {
            id: 1,
            name: 'Петров Петр Петрович'
        },
        specialty: {
            id: 1,
            name: 'Терапевт'
        },
        ticket_no: 'T001',
        slot_start: '2025-10-13 09:00:00',
        slot_len_min: 30,
        status: 'pending',
        complaint: 'Головная боль',
        queue_position: 1
    },
    {
        id: 2,
        patient: {
            id: 2,
            name: 'Сидорова Анна Сергеевна'
        },
        specialty: {
            id: 1,
            name: 'Терапевт'
        },
        ticket_no: 'T002',
        slot_start: '2025-10-13 09:30:00',
        slot_len_min: 30,
        status: 'checked_in',
        complaint: 'Повышенная температура',
        queue_position: 2
    }
];

function testPanel() {
    showPanel.value = true;
}
</script>
