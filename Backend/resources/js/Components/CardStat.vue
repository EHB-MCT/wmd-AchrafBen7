<template>
    <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-sm font-medium text-slate-500">{{ label }}</p>
                <p class="mt-1 text-3xl font-semibold text-slate-900">{{ value }}</p>
                <p v-if="subtitle" class="mt-1 text-sm text-slate-400">{{ subtitle }}</p>
            </div>
            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-sky-100 text-xl">
                <span>{{ iconSymbol }}</span>
            </div>
        </div>
        <div v-if="trend" class="mt-4 flex items-center text-sm" :class="[trend.isPositive ? 'text-emerald-600' : 'text-rose-600']">
            <span class="font-semibold">{{ trendPrefix }}{{ trend.value }}%</span>
            <span class="ml-2 text-slate-400">vs période précédente</span>
        </div>
        <slot />
    </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    label: { type: String, required: true },
    value: { type: String, required: true },
    subtitle: { type: String, default: null },
    icon: { type: String, default: 'sparkles' },
    trend: {
        type: Object,
        default: null,
    },
});

const icons = {
    users: '👥',
    sparkles: '✨',
    'trending-up': '📈',
    clock: '⏱️',
    lightning: '⚡',
    conversions: '🎯',
    search: '🔍',
    default: '📊',
};

const iconSymbol = computed(() => icons[props.icon] ?? icons.default);
const trendPrefix = computed(() => (props.trend?.isPositive ? '+' : ''));
</script>
