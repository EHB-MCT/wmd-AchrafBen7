<template>
    <div class="space-y-6">
        <div class="flex flex-col gap-2 md:flex-row md:items-end md:justify-between">
            <div>
                <p class="text-sm text-slate-500">Analyse comportementale NiOS • Derniers 7 jours</p>
                <h2 class="text-3xl font-semibold text-slate-900">Vue d’ensemble</h2>
            </div>
            <div class="flex gap-2">
                <FilterChip
                    v-for="preset in presets"
                    :key="preset.value"
                    :label="preset.label"
                    :value="preset.value"
                    :model-value="selectedRange"
                    :active="selectedRange === preset.value"
                    @update:model-value="(val) => (selectedRange = val)"
                />
            </div>
        </div>

        <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-4">
            <CardStat
                v-for="kpi in overview.kpis"
                :key="kpi.label"
                :label="kpi.label"
                :value="kpi.value"
                :subtitle="kpi.subtitle"
                :icon="kpi.icon"
                :trend="kpi.trend"
            />
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm lg:col-span-2">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-lg font-semibold">Activité de la semaine</p>
                        <p class="text-sm text-slate-500">Sessions et événements par jour</p>
                    </div>
                </div>
                <div class="mt-4">
                    <ChartLine :labels="overview.activity.labels" :datasets="activityDatasets" />
                </div>
            </div>
            <RealTimeFeed :items="overview.realtime" />
        </div>
    </div>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue';
import axios from 'axios';
import AppLayout from '../Layouts/AppLayout.vue';
import CardStat from '../Components/CardStat.vue';
import ChartLine from '../Components/ChartLine.vue';
import RealTimeFeed from '../Components/RealTimeFeed.vue';
import FilterChip from '../Components/FilterChip.vue';

defineOptions({ layout: AppLayout });

const presets = [
    { label: '24h', value: '24h' },
    { label: '7j', value: '7d' },
    { label: '30j', value: '30d' },
];

const overview = ref({
    kpis: [],
    activity: { labels: [], sessions: [], events: [] },
    realtime: [],
});
const selectedRange = ref('7d');

const fetchOverview = async () => {
    const { data } = await axios.get('/api/stats/overview', {
        params: { range: selectedRange.value },
    });
    overview.value = data;
};

onMounted(fetchOverview);
watch(selectedRange, fetchOverview);

const activityDatasets = computed(() => [
    {
        label: 'Sessions',
        data: overview.value.activity.sessions,
        backgroundColor: 'rgba(59, 130, 246, 0.15)',
        borderColor: '#3b82f6',
        fill: true,
        borderWidth: 3,
        pointRadius: 0,
    },
    {
        label: 'Événements',
        data: overview.value.activity.events,
        backgroundColor: 'rgba(99, 102, 241, 0.15)',
        borderColor: '#6366f1',
        fill: true,
        borderWidth: 3,
        pointRadius: 0,
    },
]);
</script>
