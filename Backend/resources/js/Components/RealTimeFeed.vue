<template>
    <div class="rounded-[28px] border border-[#e4e9f3] bg-white/95 p-6 shadow-[0_24px_60px_rgba(15,23,42,0.06)]">
        <header class="mb-6 flex items-center justify-between">
            <div>
                <p class="text-lg font-semibold text-slate-900">Realtime-activiteit</p>
                <p class="text-sm text-slate-500">Laatste gebruikersacties</p>
            </div>
            <span class="rounded-full bg-emerald-50 px-4 py-1 text-xs font-semibold uppercase tracking-wide text-emerald-700">
                Live
            </span>
        </header>
        <ul class="space-y-3">
            <li
                v-for="item in items"
                :key="item.id"
                class="flex items-center gap-4 rounded-2xl border border-slate-100/70 bg-slate-50/60 px-4 py-3"
            >
                <div class="flex h-11 w-11 items-center justify-center rounded-2xl text-xl" :style="iconBackground(item.type)">
                    <span>{{ iconFor(item.type) }}</span>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-semibold text-slate-900">{{ item.name }}</p>
                    <p class="text-xs text-slate-400">{{ item.time_ago ?? 'Net nu' }}</p>
                </div>
            </li>
            <li v-if="items.length === 0" class="rounded-2xl border border-dashed border-slate-200 px-4 py-6 text-center text-sm text-slate-400">
                Geen recente events
            </li>
        </ul>
    </div>
</template>

<script setup>
const props = defineProps({
    items: {
        type: Array,
        default: () => [],
    },
});

const iconFor = (type) => {
    const icons = {
        view: '👁️',
        click: '🖱️',
        conversion: '✅',
        search: '🔍',
    };

    return icons[type] ?? '✨';
};

const iconBackground = (type) => {
    const map = {
        view: 'linear-gradient(135deg,#e0f2ff,#d1e9ff)',
        click: 'linear-gradient(135deg,#fef3d7,#ffe6ba)',
        conversion: 'linear-gradient(135deg,#e4f7ef,#c6f0dc)',
        search: 'linear-gradient(135deg,#f1f5ff,#d5e2ff)',
    };

    return {
        background: map[type] ?? 'linear-gradient(135deg,#f1f5ff,#d5e2ff)',
    };
};
</script>
