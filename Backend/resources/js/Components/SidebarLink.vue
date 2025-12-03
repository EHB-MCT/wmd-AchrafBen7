<template>
    <Link
        :href="href"
        class="group flex items-center gap-3 rounded-2xl px-3 py-2.5 text-sm font-medium transition"
        :class="
            isActive
                ? 'bg-white/15 text-white shadow-[0_8px_24px_rgba(15,23,42,0.35)]'
                : 'text-slate-400 hover:text-white hover:bg-white/5'
        "
    >
        <span
            class="h-8 w-1 rounded-full transition"
            :class="isActive ? 'bg-sky-400' : 'bg-transparent group-hover:bg-white/50'"
        />
        <span
            class="flex h-9 w-9 items-center justify-center rounded-2xl border border-white/10 bg-white/5 text-white transition group-hover:border-white/20"
        >
            <svg class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path :d="iconPath" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
        </span>
        <span class="tracking-tight">{{ label }}</span>
    </Link>
</template>

<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const props = defineProps({
    href: { type: String, required: true },
    label: { type: String, required: true },
    icon: { type: String, default: 'dashboard' },
});

const iconMap = {
    dashboard: 'M4 12l2-2 4 4 8-8 2 2-10 10-6-6z',
    sessions: 'M4 5h16v14H4z',
    events: 'M6 5l12 7-12 7V5z',
    search: 'M11 5a6 6 0 104.24 10.24L19 19',
    conversions: 'M5 12h14M12 5l7 7-7 7',
    heatmap: 'M4 4h6v6H4zM14 4h6v6h-6zM4 14h6v6H4zM14 14h6v6h-6z',
    settings: 'M12 8a4 4 0 100 8 4 4 0 000-8zm8 4l2 0M2 12l2 0M12 2l0 2M12 20l0 2',
    timeline: 'M4 12h16M12 4v16M8 8h.01M16 16h.01',
};

const page = usePage();
const iconPath = computed(() => iconMap[props.icon] ?? iconMap.dashboard);
const isActive = computed(() => page.url.startsWith(props.href));
</script>
