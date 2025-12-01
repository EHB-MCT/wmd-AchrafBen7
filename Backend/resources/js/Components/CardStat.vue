<template>
    <div class="rounded-[28px] border border-[#e4e9f3] bg-white/95 p-6 shadow-[0_24px_60px_rgba(15,23,42,0.06)]">
        <div class="flex flex-col">
            <p class="text-sm font-medium text-slate-500">{{ label }}</p>
            <p class="mt-1 text-3xl font-semibold tracking-tight text-slate-900">{{ value }}</p>
            <p v-if="subtitle" class="mt-1 text-sm text-slate-400">{{ subtitle }}</p>
        </div>
        <div
            v-if="trend"
            class="mt-5 flex items-center text-sm font-semibold"
            :class="[trend.isPositive ? 'text-emerald-600' : 'text-rose-600']"
        >
            <span>{{ trendPrefix }}{{ trend.value }}%</span>
            <span class="ml-2 text-slate-400 font-normal">t.o.v. vorige periode</span>
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
    trend: {
        type: Object,
        default: null,
    },
});

const trendPrefix = computed(() => (props.trend?.isPositive ? '+' : ''));
</script>
