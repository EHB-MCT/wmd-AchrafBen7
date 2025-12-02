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
import { inject, onMounted, ref, watch } from 'vue';
import axios from 'axios';
import AppLayout from '../Layouts/AppLayout.vue';
import CardStat from '../Components/CardStat.vue';
import DateRangePicker from '../Components/DateRangePicker.vue';

defineOptions({ layout: AppLayout });

const metrics = ref({
    totals: { searches: 0, click_rate: 0, zero_result_rate: 0 },
    top_queries: [],
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
</script>
