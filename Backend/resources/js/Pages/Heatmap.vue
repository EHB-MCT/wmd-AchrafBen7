<template>
    <div class="space-y-6">
        <div>
            <p class="text-sm text-slate-500">Identifiez les zones chaudes de votre parcours</p>
            <h2 class="text-3xl font-semibold text-slate-900">Heatmap</h2>
        </div>

        <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-lg font-semibold">Heatmap en direct</p>
                    <p class="text-sm text-slate-500">Connectez Echo + Redis pour la mise à jour temps réel</p>
                </div>
                <FilterChip label="Desktop" value="desktop" :model-value="'desktop'" :active="true" />
            </div>
            <div class="mt-6 rounded-2xl border border-dashed border-slate-200 bg-slate-50 p-16 text-center text-slate-400">
                Zone Heatmap interactive (brancher vos données front)
            </div>
        </div>
    </div>
</template>

<script setup>
import AppLayout from '../Layouts/AppLayout.vue';
import FilterChip from '../Components/FilterChip.vue';

defineOptions({ layout: AppLayout });
</script>
