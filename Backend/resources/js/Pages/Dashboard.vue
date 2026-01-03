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
                        Vergelijk periode
                    </button>
                    <button
                        type="button"
                        class="rounded-full border border-slate-200 px-4 py-2 text-xs font-semibold text-slate-600 transition hover:border-slate-300"
                        @click="downloadExport('csv')"
                    >
                        Exporteer CSV
                    </button>
                    <button
                        type="button"
                        class="rounded-full border border-slate-200 px-4 py-2 text-xs font-semibold text-slate-600 transition hover:border-slate-300"
                        @click="downloadExport('pdf')"
                    >
                        Exporteer PDF
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
            <CardStat
                label="Frontend signaal"
                :value="frontendSignal.count?.toString() ?? '0'"
                subtitle="Klik op 'Stuur dashboard signaal'"
                icon="sparkles"
            />
            <CardStat
                label="Meest geboekte detailer"
                :value="topProvider.name"
                :subtitle="`Boekingen: ${topProvider.bookings}`"
                icon="trending-up"
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
            <div class="rounded-[32px] border border-[#e4e9f3] bg-white/95 p-6 shadow-[0_24px_60px_rgba(15,23,42,0.06)]">
                <div class="flex items-center justify-between">
                    <p class="text-lg font-semibold text-slate-900">Reservaties per detailer</p>
                    <p class="text-xs uppercase text-slate-400">{{ bookingList.length }} aanbieders</p>
                </div>
                <div class="mt-4 space-y-3">
                    <div
                        v-for="row in bookingList"
                        :key="row.provider_id"
                        class="flex items-center justify-between rounded-2xl bg-slate-50/70 px-4 py-3 text-sm"
                    >
                        <span class="font-semibold text-slate-900">{{ row.provider_id }}</span>
                        <span class="text-slate-500">{{ row.total_bookings }}</span>
                    </div>
                    <p v-if="bookingList.length === 0" class="text-sm text-slate-400">
                        Nog geen reserveringen.
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, inject, onBeforeUnmount, onMounted, ref, watch } from 'vue';
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
const frontendSignal = ref({ count: 0, last: null });
const topProvider = ref({ name: '-', bookings: 0 });
const bookingList = ref([]);
const selectedRange = inject('globalRange', ref('7d'));
const compareMode = ref(false);

const fetchOverview = async () => {
    const { data } = await axios.get('/api/stats/overview', {
        params: { range: selectedRange.value, compare: compareMode.value },
    });
    overview.value = data;
};
const fetchFrontendSignal = async () => {
    const { data } = await axios.get('/api/frontend-signal');
    frontendSignal.value = data;
};
const fetchTopProvider = async () => {
    const { data } = await axios.get('/api/bookings/top');
    topProvider.value = {
        name: data?.provider_id || '-',
        bookings: Number(data?.total_bookings ?? 0),
    };
};
const fetchBookings = async () => {
    const { data } = await axios.get('/api/bookings');
    bookingList.value = Array.isArray(data) ? data : [];
};

onMounted(fetchOverview);
onMounted(fetchFrontendSignal);
onMounted(fetchTopProvider);
onMounted(fetchBookings);
watch([selectedRange, compareMode], fetchOverview);
let refreshTimer;
let signalTimer;
onMounted(() => {
    refreshTimer = setInterval(() => {
        fetchOverview();
        fetchTopProvider();
        fetchBookings();
    }, 10000);
    signalTimer = setInterval(fetchFrontendSignal, 10000);
});
onBeforeUnmount(() => {
    if (refreshTimer) {
        clearInterval(refreshTimer);
    }
    if (signalTimer) {
        clearInterval(signalTimer);
    }
});

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
