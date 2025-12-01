<template>
    <div class="space-y-6">
        <div>
            <p class="text-sm text-slate-500">Begrijp naar wat klanten zoeken</p>
            <h2 class="text-3xl font-semibold text-slate-900">Zoekopdrachten</h2>
        </div>

        <div class="grid gap-5 md:grid-cols-3">
            <CardStat label="Totale zoekopdrachten" value="12 430" icon="search" />
            <CardStat label="Klikratio" value="42%" icon="trending-up" />
            <CardStat label="Zonder resultaat" value="3%" icon="sparkles" />
        </div>

        <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-lg font-semibold">Top zoektermen</p>
                <DateRangePicker v-model="range" />
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
                        <tr v-for="query in queries" :key="query.phrase" class="border-t border-slate-100">
                            <td class="py-3 font-semibold text-slate-900">{{ query.phrase }}</td>
                            <td class="py-3">{{ query.volume }}</td>
                            <td class="py-3 text-emerald-600">{{ query.conversion }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import AppLayout from '../Layouts/AppLayout.vue';
import CardStat from '../Components/CardStat.vue';
import DateRangePicker from '../Components/DateRangePicker.vue';

defineOptions({ layout: AppLayout });

const range = ref('7d');
const queries = ref([
    { phrase: 'Premium polijsten', volume: '1 230', conversion: '12%' },
    { phrase: 'Mobiele uitdeukservice', volume: '980', conversion: '9%' },
    { phrase: 'Stoomreiniging', volume: '870', conversion: '6%' },
    { phrase: 'Tesla detailing', volume: '760', conversion: '15%' },
]);
</script>
