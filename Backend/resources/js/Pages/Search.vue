<template>
    <div class="space-y-6">
        <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
            <div>
                <p class="text-sm text-slate-500">Begrijp naar wat klanten zoeken</p>
                <h2 class="text-3xl font-semibold text-slate-900">Zoekopdrachten</h2>
            </div>
            <DateRangePicker v-model="selectedRange" />
        </div>

        <div class="grid gap-5 md:grid-cols-3">
            <CardStat label="Totale zoekopdrachten" :value="formatNumber(metrics.totals.searches)" icon="search" />
            <CardStat label="Klikratio" :value="`${metrics.totals.click_rate ?? 0}%`" icon="trending-up" />
            <CardStat label="Zonder resultaat" :value="`${metrics.totals.zero_result_rate ?? 0}%`" icon="sparkles" />
        </div>

        <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
            <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-lg font-semibold">Resultaatverdeling</p>
                    <p class="text-sm text-slate-500">Performance van zoekopdrachten</p>
                </div>
                <div class="flex gap-4 text-sm">
                    <div class="flex items-center gap-2 text-emerald-600">
                        <span class="h-3 w-3 rounded-full bg-emerald-500"></span>
                        Succes {{ metrics.distribution.success?.percentage ?? 0 }}%
                    </div>
                    <div class="flex items-center gap-2 text-amber-600">
                        <span class="h-3 w-3 rounded-full bg-amber-400"></span>
                        Gedeeltelijk {{ metrics.distribution.partial?.percentage ?? 0 }}%
                    </div>
                    <div class="flex items-center gap-2 text-rose-600">
                        <span class="h-3 w-3 rounded-full bg-rose-500"></span>
                        Geen resultaat {{ metrics.distribution.empty?.percentage ?? 0 }}%
                    </div>
                </div>
            </div>
            <div class="mt-6">
                <ChartPie :labels="distributionLabels" :datasets="distributionDatasets" :height="320" />
            </div>
        </div>

        <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-lg font-semibold">Top zoektermen</p>
            </div>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full text-left text-sm text-slate-600">
                    <thead>
                        <tr class="text-xs uppercase text-slate-400">
                            <th class="pb-3">Zoekterm</th>
                            <th class="pb-3">Volume</th>
                            <th class="pb-3">Conversie</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="query in metrics.top_queries" :key="query.phrase" class="border-t border-slate-100">
                            <td class="py-3 font-semibold text-slate-900">{{ query.phrase }}</td>
                            <td class="py-3">{{ formatNumber(query.volume) }}</td>
                            <td class="py-3 text-emerald-600">{{ `${query.conversion}%` }}</td>
                        </tr>
                        <tr v-if="metrics.top_queries.length === 0">
                            <td colspan="3" class="py-6 text-center text-slate-400">Geen zoekopdrachten in deze periode.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, inject, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import axios from 'axios';
import AppLayout from '../Layouts/AppLayout.vue';
import CardStat from '../Components/CardStat.vue';
import DateRangePicker from '../Components/DateRangePicker.vue';
import ChartPie from '../Components/ChartPie.vue';

defineOptions({ layout: AppLayout });

const metrics = ref({
    totals: { searches: 0, click_rate: 0, zero_result_rate: 0 },
    top_queries: [],
    distribution: {
        success: { count: 0, percentage: 0 },
        partial: { count: 0, percentage: 0 },
        empty: { count: 0, percentage: 0 },
    },
});
const selectedRange = inject('globalRange', ref('7d'));
const numberFormatter = new Intl.NumberFormat('nl-NL');

const formatNumber = (value) => numberFormatter.format(value ?? 0);

const loadSearchStats = async () => {
    const { data } = await axios.get('/api/stats/search', {
        params: { range: selectedRange.value },
    });
    metrics.value = data;
};

onMounted(loadSearchStats);
watch(selectedRange, loadSearchStats);
let refreshTimer;
onMounted(() => {
    refreshTimer = setInterval(loadSearchStats, 10000);
});
onBeforeUnmount(() => {
    if (refreshTimer) {
        clearInterval(refreshTimer);
    }
});

const distributionLabels = ['Succes', 'Gedeeltelijk', 'Geen resultaat'];
const distributionDatasets = computed(() => [
    {
        data: [
            metrics.value.distribution.success?.count ?? 0,
            metrics.value.distribution.partial?.count ?? 0,
            metrics.value.distribution.empty?.count ?? 0,
        ],
        backgroundColor: ['#22c55e', '#f59e0b', '#f87171'],
        borderWidth: 0,
    },
]);
</script>
