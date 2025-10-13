import { ref, onMounted, onUnmounted } from 'vue';

export function useQueueUpdates(userId, doctorId = null) {
    const queuePosition = ref(null);
    const isConnected = ref(false);

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
        // Здесь можно добавить логику для обновления позиции в очереди
        // Например, перезагрузить данные или обновить состояние
        console.log('Updating queue position based on event:', event);
    }

    function refreshQueueData() {
        // Метод для принудительного обновления данных очереди
        // Можно использовать для перезагрузки данных после получения события
        window.location.reload();
    }

    return {
        queuePosition,
        isConnected,
        updateQueuePosition,
        refreshQueueData
    };
}

