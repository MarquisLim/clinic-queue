import { ref, onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';

export function useQueueUpdates(userId, doctorId = null) {
    const queuePosition = ref(null);
    const isConnected = ref(false);
    const lastUpdateTime = ref(null);

    let echo = null;

    onMounted(() => {
        if (window.Echo) {
            echo = window.Echo;
            isConnected.value = true;

            // Слушаем общие события очередей
            echo.channel('appointments')
                .listen('.status.changed', (e) => {
                    console.log('Status changed:', e);
                    // Здесь можно обновить позицию в очереди
                    updateQueuePosition(e);
                })
                .listen('.appointment.created', (e) => {
                    console.log('Appointment created:', e);
                    // Обновляем позиции при создании новой записи
                    updateQueuePosition(e);
                })
                .listen('.appointment.cancelled', (e) => {
                    console.log('Appointment cancelled:', e);
                    // Обновляем позиции при отмене записи
                    updateQueuePosition(e);
                });

            // Слушаем события для конкретного пациента
            if (userId) {
                echo.channel(`patient.${userId}`)
                    .listen('.status.changed', (e) => {
                        console.log('Patient status changed:', e);
                        updateQueuePosition(e);
                    })
                    .listen('.appointment.created', (e) => {
                        console.log('Patient appointment created:', e);
                        updateQueuePosition(e);
                    })
                    .listen('.appointment.cancelled', (e) => {
                        console.log('Patient appointment cancelled:', e);
                        updateQueuePosition(e);
                    });
            }

            // Слушаем события для конкретного врача
            if (doctorId) {
                echo.channel(`doctor.${doctorId}`)
                    .listen('.status.changed', (e) => {
                        console.log('Doctor queue updated:', e);
                        updateQueuePosition(e);
                    });
            }
        }
    });

    onUnmounted(() => {
        if (echo) {
            echo.leave('appointments');
            if (userId) {
                echo.leave(`patient.${userId}`);
            }
            if (doctorId) {
                echo.leave(`doctor.${doctorId}`);
            }
        }
    });

    function updateQueuePosition(event) {
        console.log('Updating queue position based on event:', event);
        
        // Обновляем время последнего обновления
        lastUpdateTime.value = new Date().toISOString();
        
        // Обновляем данные страницы без полной перезагрузки
        if (event.appointment_id) {
            // Для событий с appointment_id обновляем только данные
            refreshQueueData();
        }
    }

    function refreshQueueData() {
        // Обновляем данные страницы через Inertia без полной перезагрузки
        router.reload({
            only: ['appointments', 'doctors', 'upcoming', 'history'], // Обновляем только нужные данные
            preserveState: true,
            preserveScroll: true,
        });
    }

    return {
        queuePosition,
        isConnected,
        lastUpdateTime,
        updateQueuePosition,
        refreshQueueData
    };
}

