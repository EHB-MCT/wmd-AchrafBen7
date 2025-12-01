<template>
    <div class="space-y-8">
        <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
            <div>
                <h2 class="text-4xl font-semibold text-slate-900">Overzicht</h2>
                <p class="mt-1 text-base text-slate-500">
                    NiOS gedragsanalyse • Laatste {{ rangeLabel }}
                </p>
            </div>
            <div class="flex flex-col gap-3 md:flex-row md:items-center md:gap-4">
                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        class="rounded-full border px-4 py-2 text-xs font-semibold uppercase tracking-wide"
                        :class="compareMode ? 'border-sky-500 bg-sky-50 text-sky-700' : 'border-slate-200 text-slate-500'"
                        @click="compareMode = !compareMode"
                    >
                        Compare range
                    </button>
                    <button
                        type="button"
                        class="rounded-full border border-slate-200 px-4 py-2 text-xs font-semibold text-slate-600 transition hover:border-slate-300"
                        @click="downloadExport('csv')"
                    >
                        Export CSV
                    </button>
                    <button
                        type="button"
                        class="rounded-full border border-slate-200 px-4 py-2 text-xs font-semibold text-slate-600 transition hover:border-slate-300"
                        @click="downloadExport('pdf')"
                    >
                        Export PDF
                    </button>
                </div>
                <DateRangePicker v-model="selectedRange" />
            </div>
        </div>

        <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-4">
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
            <div class="rounded-[32px] border border-[#e4e9f3] bg-white/95 p-6 shadow-[0_24px_60px_rgba(15,23,42,0.06)] lg:col-span-2">
                <div class="flex flex-col gap-1">
                    <p class="text-lg font-semibold text-slate-900">Weekactiviteit</p>
                    <p class="text-sm text-slate-500">Sessies en events per dag</p>
                </div>
                <div class="mt-6">
                    <ChartLine :labels="overview.activity.labels" :datasets="activityDatasets" :height="320" />
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
import DateRangePicker from '../Components/DateRangePicker.vue';

defineOptions({ layout: AppLayout });

const overview = ref({
    kpis: [],
    activity: { labels: [], sessions: [], events: [] },
    realtime: [],
    comparison: null,
});
const selectedRange = ref('7d');
const compareMode = ref(false);

const fetchOverview = async () => {
    const { data } = await axios.get('/api/stats/overview', {
        params: { range: selectedRange.value, compare: compareMode.value },
    });
    overview.value = data;
};

onMounted(fetchOverview);
watch([selectedRange, compareMode], fetchOverview);

const downloadExport = (format) => {
    window.open(`/api/export/kpis.${format}?range=${selectedRange.value}`, '_blank');
};

const activityDatasets = computed(() => {
    const base = [
        {
            label: 'Sessies',
            data: overview.value.activity.sessions,
            backgroundColor: 'rgba(59, 130, 246, 0.15)',
            borderColor: '#3b82f6',
            fill: true,
            borderWidth: 3,
            pointRadius: 0,
        },
        {
            label: 'Evenementen',
            data: overview.value.activity.events,
            backgroundColor: 'rgba(99, 102, 241, 0.15)',
            borderColor: '#6366f1',
            fill: true,
            borderWidth: 3,
            pointRadius: 0,
        },
    ];

    if (overview.value.comparison?.activity) {
        base.push(
            {
                label: 'Sessies (vorige)',
                data: overview.value.comparison.activity.sessions,
                borderColor: '#94a3b8',
                borderDash: [6, 6],
                borderWidth: 2,
                pointRadius: 0,
                fill: false,
            },
            {
                label: 'Evenementen (vorige)',
                data: overview.value.comparison.activity.events,
                borderColor: '#cbd5f5',
                borderDash: [4, 6],
                borderWidth: 2,
                pointRadius: 0,
                fill: false,
            }
        );
    }

    return base;
});

const rangeLabel = computed(() => {
    const map = {
        '24h': '24 uur',
        '7d': '7 dagen',
        '30d': '30 dagen',
    };

    return map[selectedRange.value] ?? '7 dagen';
});
</script>
