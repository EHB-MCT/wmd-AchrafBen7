<template>
    <div class="space-y-6">
        <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
            <div>
                <p class="text-sm text-slate-500">Krijg zicht op engagementgedrag</p>
                <h2 class="text-3xl font-semibold text-slate-900">Sessies</h2>
            </div>
            <DateRangePicker v-model="selectedRange" />
        </div>

        <div class="grid gap-5 md:grid-cols-3">
            <CardStat label="Actieve sessies" :value="metrics.totals.active?.toString() ?? '0'" icon="users" />
            <CardStat label="Sessies per week" :value="metrics.totals.weekly?.toString() ?? '0'" icon="sparkles" />
            <CardStat label="Gemiddelde duur" :value="metrics.totals.average_duration ?? '0m 00s'" icon="clock" />
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm lg:col-span-2">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-lg font-semibold">Trend</p>
                        <p class="text-sm text-slate-500">Dagelijkse sessies</p>
                    </div>
                </div>
                <div class="mt-4">
                    <ChartLine
                        :labels="metrics.timeline.labels"
                        :datasets="[
                            {
                                label: 'Sessies',
                                data: metrics.timeline.data,
                                borderColor: '#0ea5e9',
                                backgroundColor: 'rgba(14, 165, 233, 0.15)',
                                borderWidth: 3,
                                pointRadius: 0,
                                fill: true,
                            },
                        ]"
                    />
                </div>
            </div>
            <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
                <p class="text-lg font-semibold">Platformen</p>
                <ul class="mt-4 space-y-3">
                    <li v-for="platform in platforms" :key="platform.label" class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <span class="h-3 w-3 rounded-full bg-sky-400"></span>
                            <span class="text-sm text-slate-600">{{ platform.label }}</span>
                        </div>
                        <span class="text-sm font-semibold text-slate-900">{{ platform.value }}</span>
                    </li>
                    <li v-if="platforms.length === 0" class="text-sm text-slate-400">Geen gegevens beschikbaar.</li>
                </ul>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-lg font-semibold">Recente sessies</p>
                    <p class="text-sm text-slate-500">Laatste gebruikersbezoeken</p>
                </div>
            </div>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full text-left text-sm text-slate-600">
                    <thead>
                        <tr class="text-xs uppercase text-slate-400">
                            <th class="pb-3">Gebruiker</th>
                            <th class="pb-3">Platform</th>
                            <th class="pb-3">Duur</th>
                            <th class="pb-3">Wanneer</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="session in metrics.recent" :key="session.id" class="border-t border-slate-100">
                            <td class="py-3 font-medium text-slate-900">{{ session.user }}</td>
                            <td class="py-3">{{ session.platform }}</td>
                            <td class="py-3">{{ session.duration }}</td>
                            <td class="py-3 text-slate-500">{{ session.started_at }}</td>
                        </tr>
                        <tr v-if="metrics.recent.length === 0">
                            <td colspan="4" class="py-6 text-center text-slate-400">Geen sessies gevonden.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script setup>
import { onMounted, ref, watch } from 'vue';
import axios from 'axios';
import AppLayout from '../Layouts/AppLayout.vue';
import CardStat from '../Components/CardStat.vue';
import ChartLine from '../Components/ChartLine.vue';
import DateRangePicker from '../Components/DateRangePicker.vue';

defineOptions({ layout: AppLayout });

const metrics = ref({
    totals: {},
    platforms: {},
    timeline: { labels: [], data: [] },
    recent: [],
});

const platforms = ref([]);
const selectedRange = ref('7d');

const loadSessions = async () => {
    const { data } = await axios.get('/api/stats/sessions', {
        params: { range: selectedRange.value },
    });
    metrics.value = data;
    platforms.value = Object.entries(data.platforms ?? {}).map(([label, value]) => ({ label, value }));
};

onMounted(loadSessions);
watch(selectedRange, loadSessions);
</script>
