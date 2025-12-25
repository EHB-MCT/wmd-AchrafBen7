<template>
    <div class="space-y-6">
        <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
            <div>
                <p class="text-sm text-slate-500">Volg je productfunnel</p>
                <h2 class="text-3xl font-semibold text-slate-900">Conversies</h2>
            </div>
            <DateRangePicker v-model="selectedRange" />
        </div>

        <div class="grid gap-5 md:grid-cols-4">
            <CardStat label="Bezoeken" :value="formatNumber(metrics.totals.visits)" icon="sparkles" />
            <CardStat label="Intenties" :value="formatNumber(metrics.totals.intents)" icon="trending-up" />
            <CardStat label="Offertes" :value="formatNumber(metrics.totals.quotes)" icon="lightning" />
            <CardStat
                label="Boekingen"
                :value="formatNumber(metrics.totals.bookings)"
                :subtitle="`Conversie ${metrics.totals.conversion_rate ?? 0}%`"
                icon="conversions"
            />
        </div>

        <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
            <p class="text-lg font-semibold">Trechter</p>
            <div class="mt-6 grid gap-6 md:grid-cols-4">
                <div
                    v-for="stage in metrics.funnel"
                    :key="stage.label"
                    class="rounded-2xl border border-slate-100 bg-slate-50 p-4 text-center"
                >
                    <p class="text-sm uppercase text-slate-400">{{ stage.label }}</p>
                    <p class="mt-2 text-2xl font-semibold text-slate-900">{{ formatNumber(stage.value) }}</p>
                    <p class="text-xs text-slate-500">{{ stage.rate }}% vs vorige stap</p>
                </div>
                <div v-if="metrics.funnel.length === 0" class="rounded-2xl border border-dashed border-slate-200 p-8 text-center text-sm text-slate-400">
                    Geen funneldata in deze periode.
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { inject, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import axios from 'axios';
import AppLayout from '../Layouts/AppLayout.vue';
import CardStat from '../Components/CardStat.vue';
import DateRangePicker from '../Components/DateRangePicker.vue';

defineOptions({ layout: AppLayout });

const metrics = ref({
    totals: { visits: 0, intents: 0, quotes: 0, bookings: 0, conversion_rate: 0 },
    funnel: [],
});
const selectedRange = inject('globalRange', ref('7d'));
const numberFormatter = new Intl.NumberFormat('nl-NL');

const formatNumber = (value) => numberFormatter.format(value ?? 0);

const loadConversions = async () => {
    const { data } = await axios.get('/api/stats/conversions', {
        params: { range: selectedRange.value },
    });
    metrics.value = data;
};

onMounted(loadConversions);
watch(selectedRange, loadConversions);
let refreshTimer;
onMounted(() => {
    refreshTimer = setInterval(loadConversions, 10000);
});
onBeforeUnmount(() => {
    if (refreshTimer) {
        clearInterval(refreshTimer);
    }
});
</script>
