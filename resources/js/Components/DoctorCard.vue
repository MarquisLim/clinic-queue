<script setup>
import { ref, computed, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    doctor: { type: Object, required: true },
    selectedSpecId: { type: Number, default: null }
});

const activeSpecialtyId = computed(() => {
    return props.selectedSpecId || props.doctor.speciality_id;
});

const days = ref([]);           // [{date, label, weekday, available}]
const selectedDate = ref(null);
const times = ref([]);          // [{start, iso, available}]
const loadingTimes = ref(false);
const errorMsg = ref('');

// modal
const showModal = ref(false);
const pickedSlot = ref(null);

const specText = computed(() =>
    props.doctor.specialty?.name || ''
);

function fmtWeekday(d) {
    return new Date(d).toLocaleDateString(undefined, { weekday: 'short' }).replace('.', '');
}
function datePlus(base, i) {
    const dt = new Date(base);
    dt.setDate(dt.getDate() + i);
    return dt.toISOString().slice(0,10);
}

async function fetchAvailability() {
    const start = new Date().toISOString().slice(0,10);
    const url = new URL(route('slots.availability'));
    url.searchParams.set('doctor_id', props.doctor.id);
    url.searchParams.set('from', start);
    url.searchParams.set('days', '10');

    const res = await fetch(url, { headers: { 'X-Requested-With': 'fetch' } });
    const data = await res.json(); // [{date, available}]

    days.value = data.map(d => ({
        date: d.date,
        label: String(new Date(d.date).getDate()),
        weekday: fmtWeekday(d.date),
        available: !!d.available,
    }));

    const first = days.value.find(x => x.available);
    if (first) await fetchDay(first.date);
}

async function fetchDay(date) {
    selectedDate.value = date;
    loadingTimes.value = true;
    times.value = [];

    const url = new URL(route('slots.day'));
    url.searchParams.set('doctor_id', props.doctor.id);
    url.searchParams.set('date', date);

    const res = await fetch(url, { headers: { 'X-Requested-With': 'fetch' } });
    const data = await res.json(); // {slots:[], closed:bool}
    times.value = (data?.slots || []).filter(s => s.available);
    loadingTimes.value = false;
}

function openConfirm(slot) {
    pickedSlot.value = slot;
    showModal.value = true;
}

function confirmBooking() {
    if (!pickedSlot.value) return;
    const slotLocal = `${selectedDate.value} ${pickedSlot.value.start}:00`;
    const specialty_id = activeSpecialtyId.value;
    if (!specialty_id) {
        alert('Не найдена специальность для записи. Обратитесь к администратору.');
        return;
    }
    router.post(
        route('appointments.store'),
        {
            doctor_id: props.doctor.id,
            specialty_id,
            slot_start: slotLocal
        },
        {
            preserveScroll: true,
            onError: (errors) => {
                alert(errors.slot_start || errors.doctor_id || errors.specialty_id || 'Ошибка записи');
            },
            onSuccess: () => {
                showModal.value = false;
                router.visit(route('appointments.mine'));
            },
        }
    );
}

onMounted(fetchAvailability);
</script>

<template>
    <div class="card bg-base-100 border border-base-300/70 rounded-2xl shadow-md hover:shadow-lg transition p-5">
        <div class="flex flex-col md:flex-row gap-6">
            <!-- Photo -->
            <div class="md:w-28">
                <img :src="doctor.photo_url" :alt="doctor.user?.name" class="w-28 h-28 object-cover rounded-2xl shadow" />
            </div>

            <!-- Main info -->
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-3 flex-wrap">
                    <h3 class="text-xl font-bold truncate">{{ doctor.user?.name }}</h3>
                    <span class="badge" :class="doctor.is_active ? 'badge-success' : 'badge-ghost'">
            {{ doctor.is_active ? 'Активен' : 'Не активен' }}
          </span>
                </div>

                <div class="mt-2 text-sm space-y-1">
                    <div v-if="specText" class="text-base-content/90">{{ specText }}</div>
                    <div class="text-base-content/70">Кабинет: <b>{{ doctor.room || '—' }}</b></div>
                    <div class="text-base-content/70">Среднее время приёма: <b>{{ doctor.avg_duration_min }} мин</b></div>
                </div>
            </div>

            <!-- Data -->
            <div class="md:w-80">
                <!-- лента дат -->
                <div class="grid grid-cols-7 gap-2 mb-3">
                    <button
                        v-for="d in days"
                        :key="d.date"
                        class="btn btn-sm rounded-xl"
                        :class="[
                          d.available
                            ? (d.date === selectedDate ? 'btn-info text-white' : 'btn-outline')
                            : 'btn-disabled'
                        ]"
                        :title="d.weekday + ', ' + d.date"
                        @click="d.available && fetchDay(d.date)"
                    >
                        {{ d.label }}
                    </button>
                </div>

                <!-- слоты -->
                <div v-if="loadingTimes" class="text-sm text-base-content/60">Загружаем слоты…</div>
                <div v-else class="flex flex-wrap gap-2">
                    <button
                        v-for="t in times"
                        :key="t.iso"
                        class="btn btn-xs rounded-full btn-outline"
                        @click="openConfirm(t)"
                    >
                        {{ t.start }}
                    </button>
                    <div v-if="!times.length" class="text-sm text-base-content/60">Нет свободных слотов</div>
                </div>
            </div>
        </div>

        <!-- Modal Approve -->
        <dialog class="modal" :open="showModal">
            <div class="modal-box">
                <h3 class="font-bold text-lg mb-2">Подтверждение записи</h3>
                <p class="mb-4">
                    Врач: <b>{{ doctor.user?.name }}</b><br>
                    Дата и время: <b>{{ selectedDate }} {{ pickedSlot?.start }}</b>
                </p>
                <p v-if="errorMsg" class="text-error text-sm mb-2">{{ errorMsg }}</p>

                <div class="modal-action">
                    <button class="btn btn-ghost" @click="showModal=false">Отмена</button>
                    <button class="btn btn-primary" @click="confirmBooking">Записаться</button>
                </div>
            </div>
            <form method="dialog" class="modal-backdrop" @click="showModal=false">
                <button>close</button>
            </form>
        </dialog>
    </div>
</template>
