<template>
    <div class="min-h-screen bg-base-200 py-8">
        <div class="max-w-4xl mx-auto px-4">
            <div class="bg-base-100 rounded-lg shadow-lg p-6">
                <h1 class="text-3xl font-bold mb-6">Тестирование Pusher</h1>
                
                <!-- Статус подключения -->
                <div class="mb-6">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="badge" :class="isConnected ? 'badge-success' : 'badge-error'">
                            {{ isConnected ? '🔴 Подключено' : '❌ Не подключено' }}
                        </div>
                        <span class="text-sm text-base-content/70">
                            {{ isConnected ? 'WebSocket соединение активно' : 'WebSocket соединение неактивно' }}
                        </span>
                    </div>
                </div>

                <!-- Конфигурация -->
                <div class="mb-6">
                    <h2 class="text-xl font-semibold mb-3">Конфигурация Pusher</h2>
                    <div class="bg-base-200 rounded p-4">
                        <div v-if="config" class="space-y-2 text-sm">
                            <div><strong>App ID:</strong> {{ config.pusher_config.app_id }}</div>
                            <div><strong>Key:</strong> {{ config.pusher_config.key }}</div>
                            <div><strong>Cluster:</strong> {{ config.pusher_config.cluster }}</div>
                            <div><strong>Encrypted:</strong> {{ config.pusher_config.encrypted ? 'Да' : 'Нет' }}</div>
                            <div><strong>Use TLS:</strong> {{ config.pusher_config.useTLS ? 'Да' : 'Нет' }}</div>
                        </div>
                        <div v-else class="text-base-content/70">
                            Загрузка конфигурации...
                        </div>
                    </div>
                </div>

                <!-- Кнопки тестирования -->
                <div class="mb-6">
                    <h2 class="text-xl font-semibold mb-3">Тестирование</h2>
                    <div class="flex gap-3 flex-wrap">
                        <button 
                            @click="testConnection" 
                            class="btn btn-primary"
                            :disabled="testing"
                        >
                            {{ testing ? 'Тестирование...' : 'Тест подключения' }}
                        </button>
                        <button 
                            @click="testAppointmentEvent" 
                            class="btn btn-secondary"
                            :disabled="testing"
                        >
                            {{ testing ? 'Отправка...' : 'Тест события записи' }}
                        </button>
                        <button 
                            @click="loadConfig" 
                            class="btn btn-outline"
                            :disabled="testing"
                        >
                            Обновить конфигурацию
                        </button>
                    </div>
                </div>

                <!-- Результаты тестов -->
                <div class="mb-6">
                    <h2 class="text-xl font-semibold mb-3">Результаты тестов</h2>
                    <div class="bg-base-200 rounded p-4">
                        <div v-if="testResults.length === 0" class="text-base-content/70">
                            Тесты еще не выполнялись
                        </div>
                        <div v-else class="space-y-3">
                            <div 
                                v-for="(result, index) in testResults" 
                                :key="index"
                                class="p-3 rounded"
                                :class="result.success ? 'bg-success/20 border border-success' : 'bg-error/20 border border-error'"
                            >
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="text-lg">{{ result.success ? '✅' : '❌' }}</span>
                                    <span class="font-semibold">{{ result.title }}</span>
                                    <span class="text-xs text-base-content/70">{{ result.timestamp }}</span>
                                </div>
                                <div class="text-sm">{{ result.message }}</div>
                                <div v-if="result.data" class="mt-2 text-xs bg-base-300 p-2 rounded">
                                    <pre>{{ JSON.stringify(result.data, null, 2) }}</pre>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Лог событий -->
                <div class="mb-6">
                    <h2 class="text-xl font-semibold mb-3">Лог событий Pusher</h2>
                    <div class="bg-base-200 rounded p-4 max-h-64 overflow-y-auto">
                        <div v-if="eventLog.length === 0" class="text-base-content/70">
                            События еще не получены
                        </div>
                        <div v-else class="space-y-2">
                            <div 
                                v-for="(event, index) in eventLog" 
                                :key="index"
                                class="text-sm p-2 bg-base-300 rounded"
                            >
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="font-mono text-xs">{{ event.timestamp }}</span>
                                    <span class="badge badge-outline badge-sm">{{ event.channel }}</span>
                                    <span class="badge badge-primary badge-sm">{{ event.event }}</span>
                                </div>
                                <div class="font-mono text-xs">
                                    {{ JSON.stringify(event.data, null, 2) }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Инструкции -->
                <div class="bg-info/20 border border-info rounded p-4">
                    <h3 class="font-semibold mb-2">Инструкции по тестированию:</h3>
                    <ol class="list-decimal list-inside space-y-1 text-sm">
                        <li>Убедитесь, что в .env файле правильно настроены переменные Pusher</li>
                        <li>Выполните <code>composer install</code> для установки pusher-php-server</li>
                        <li>Выполните <code>npm install</code> для установки frontend зависимостей</li>
                        <li>Выполните <code>npm run dev</code> для компиляции frontend</li>
                        <li>Нажмите "Тест подключения" для проверки соединения с Pusher</li>
                        <li>Нажмите "Тест события записи" для отправки тестового события</li>
                        <li>События должны появиться в логе событий в реальном времени</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import axios from 'axios';

const isConnected = ref(false);
const config = ref(null);
const testing = ref(false);
const testResults = ref([]);
const eventLog = ref([]);

let echo = null;

onMounted(() => {
    loadConfig();
    setupPusherListeners();
});

onUnmounted(() => {
    if (echo) {
        echo.leave('test-channel');
        echo.leave('appointments');
        echo.leave('doctor.1');
        echo.leave('patient.1');
    }
});

function setupPusherListeners() {
    if (window.Echo) {
        echo = window.Echo;
        isConnected.value = true;

        // Слушаем тестовый канал
        echo.channel('test-channel')
            .listen('.test-event', (e) => {
                addEventLog('test-channel', 'test-event', e);
            });

        // Слушаем каналы приложения
        echo.channel('appointments')
            .listen('.appointment.created', (e) => {
                addEventLog('appointments', 'appointment.created', e);
            })
            .listen('.appointment.cancelled', (e) => {
                addEventLog('appointments', 'appointment.cancelled', e);
            })
            .listen('.status.changed', (e) => {
                addEventLog('appointments', 'status.changed', e);
            });

        echo.channel('doctor.1')
            .listen('.appointment.created', (e) => {
                addEventLog('doctor.1', 'appointment.created', e);
            });

        echo.channel('patient.1')
            .listen('.appointment.created', (e) => {
                addEventLog('patient.1', 'appointment.created', e);
            });

        console.log('Pusher listeners установлены');
    } else {
        console.error('Echo не доступен');
        isConnected.value = false;
    }
}

function addEventLog(channel, event, data) {
    eventLog.value.unshift({
        timestamp: new Date().toLocaleTimeString(),
        channel,
        event,
        data
    });
    
    // Ограничиваем количество событий в логе
    if (eventLog.value.length > 50) {
        eventLog.value = eventLog.value.slice(0, 50);
    }
}

function addTestResult(success, title, message, data = null) {
    testResults.value.unshift({
        success,
        title,
        message,
        data,
        timestamp: new Date().toLocaleTimeString()
    });
    
    // Ограничиваем количество результатов
    if (testResults.value.length > 10) {
        testResults.value = testResults.value.slice(0, 10);
    }
}

async function loadConfig() {
    try {
        const response = await axios.get('/test/pusher/config');
        config.value = response.data;
    } catch (error) {
        console.error('Ошибка загрузки конфигурации:', error);
    }
}

async function testConnection() {
    testing.value = true;
    try {
        const response = await axios.get('/test/pusher/connection');
        addTestResult(
            response.data.success,
            'Тест подключения',
            response.data.message,
            response.data.data
        );
    } catch (error) {
        addTestResult(
            false,
            'Тест подключения',
            'Ошибка: ' + error.message
        );
    } finally {
        testing.value = false;
    }
}

async function testAppointmentEvent() {
    testing.value = true;
    try {
        const response = await axios.get('/test/pusher/appointment');
        addTestResult(
            response.data.success,
            'Тест события записи',
            response.data.message,
            response.data.data
        );
    } catch (error) {
        addTestResult(
            false,
            'Тест события записи',
            'Ошибка: ' + error.message
        );
    } finally {
        testing.value = false;
    }
}
</script>