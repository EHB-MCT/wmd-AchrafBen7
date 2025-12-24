<template>
    <div class="space-y-6">
        <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
            <div>
                <p class="text-sm text-slate-500">Productactiviteit</p>
                <h2 class="text-3xl font-semibold text-slate-900">Evenementen</h2>
            </div>
            <div class="flex items-center gap-3">
                <button
                    type="button"
                    class="rounded-full border px-4 py-2 text-xs font-semibold uppercase tracking-wide"
                    :class="compareMode ? 'border-sky-500 bg-sky-50 text-sky-700' : 'border-slate-200 text-slate-500'"
                    @click="compareMode = !compareMode"
                >
                    Vergelijk
                </button>
                <DateRangePicker v-model="selectedRange" />
            </div>
        </div>

        <div class="grid gap-5 md:grid-cols-3">
            <CardStat label="Totaal per week" :value="metrics.totals.weekly?.toString() ?? '0'" icon="sparkles" />
            <CardStat label="Conversies" :value="metrics.totals.conversions?.toString() ?? '0'" icon="conversions" />
            <CardStat
                label="Conversieratio"
                :value="metrics.totals.conversion_rate ? `${metrics.totals.conversion_rate}%` : '0%'"
                icon="trending-up"
            />
        </div>

        <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-lg font-semibold">Tijdlijn</p>
                    <p class="text-sm text-slate-500">Dagelijks eventvolume</p>
                </div>
            </div>
            <div class="mt-4">
                <ChartBar :labels="metrics.timeline.labels" :datasets="datasets" />
            </div>
        </div>

        <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-lg font-semibold">Belangrijkste events</p>
                    <p class="text-sm text-slate-500">Meest voorkomende interacties</p>
                </div>
            </div>
            <ul class="mt-4 space-y-3">
                <li v-for="event in metrics.top_events" :key="event.name" class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-semibold text-slate-900">{{ event.name }}</p>
                        <p class="text-xs uppercase text-slate-400">{{ event.type }}</p>
                    </div>
                    <span class="text-sm font-semibold text-slate-600">{{ event.total }}</span>
                </li>
                <li v-if="metrics.top_events.length === 0" class="text-sm text-slate-400">Geen gegevens beschikbaar.</li>
            </ul>
        </div>
    </div>
</template>

<script setup>
import { computed, inject, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import axios from 'axios';
import AppLayout from '../Layouts/AppLayout.vue';
import CardStat from '../Components/CardStat.vue';
import ChartBar from '../Components/ChartBar.vue';
import DateRangePicker from '../Components/DateRangePicker.vue';

defineOptions({ layout: AppLayout });

const metrics = ref({
    totals: {},
    timeline: { labels: [], data: [] },
    top_events: [],
    comparison: null,
});
const selectedRange = inject('globalRange', ref('7d'));
const compareMode = ref(false);

const loadEvents = async () => {
    const { data } = await axios.get('/api/stats/events', {
        params: { range: selectedRange.value, compare: compareMode.value },
    });
    metrics.value = data;
};

onMounted(loadEvents);
watch([selectedRange, compareMode], loadEvents);
let refreshTimer;
onMounted(() => {
    refreshTimer = setInterval(loadEvents, 10000);
});
onBeforeUnmount(() => {
    if (refreshTimer) {
        clearInterval(refreshTimer);
    }
});

const datasets = computed(() => [
    {
        label: 'Evenementen',
        data: metrics.value.timeline.data,
        backgroundColor: 'rgba(59, 130, 246, 0.6)',
        borderRadius: 12,
        maxBarThickness: 32,
    },
    ...(metrics.value.comparison?.timeline
        ? [
              {
                  label: 'Vorige periode',
                  data: metrics.value.comparison.timeline.data,
                  backgroundColor: 'rgba(148, 163, 184, 0.5)',
                  borderRadius: 12,
                  maxBarThickness: 32,
              },
          ]
        : []),
]);
</script>
