<template>
    <div class="space-y-6">
        <div>
            <p class="text-sm text-slate-500">Volg je productfunnel</p>
            <h2 class="text-3xl font-semibold text-slate-900">Conversies</h2>
        </div>

        <div class="grid gap-5 md:grid-cols-4">
            <CardStat label="Bezoeken" value="24 900" icon="sparkles" />
            <CardStat label="Intenties" value="9 320" icon="trending-up" />
            <CardStat label="Offertes" value="2 410" icon="lightning" />
            <CardStat label="Boekingen" value="1 340" icon="conversions" />
        </div>

        <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
            <p class="text-lg font-semibold">Trechter</p>
            <div class="mt-6 grid gap-6 md:grid-cols-4">
                <div
                    v-for="stage in funnel"
                    :key="stage.label"
                    class="rounded-2xl border border-slate-100 bg-slate-50 p-4 text-center"
                >
                    <p class="text-sm uppercase text-slate-400">{{ stage.label }}</p>
                    <p class="mt-2 text-2xl font-semibold text-slate-900">{{ stage.value }}</p>
                    <p class="text-xs text-slate-500">{{ stage.rate }}% vs vorige stap</p>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import AppLayout from '../Layouts/AppLayout.vue';
import CardStat from '../Components/CardStat.vue';

defineOptions({ layout: AppLayout });

const funnel = [
    { label: 'Ontdekking', value: '24 900', rate: 100 },
    { label: 'Intenties', value: '9 320', rate: 37 },
    { label: 'Offertes', value: '2 410', rate: 26 },
    { label: 'Boekingen', value: '1 340', rate: 55 },
];
</script>
