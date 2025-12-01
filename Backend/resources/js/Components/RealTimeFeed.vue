<template>
    <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
        <header class="mb-4 flex items-center justify-between">
            <div>
                <p class="text-lg font-semibold text-slate-900">Activité en temps réel</p>
                <p class="text-sm text-slate-500">Derniers événements utilisateurs</p>
            </div>
            <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">
                {{ items.length }} actifs
            </span>
        </header>
        <ul class="space-y-4">
            <li v-for="item in items" :key="item.id" class="flex items-center gap-3 rounded-xl border border-slate-100 p-3">
                <div class="flex h-9 w-9 items-center justify-center rounded-full bg-sky-50 text-lg">
                    <span>{{ iconFor(item.type) }}</span>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-medium text-slate-900">{{ item.name }}</p>
                    <p class="text-xs text-slate-400">{{ item.time_ago ?? 'À l’instant' }}</p>
                </div>
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
</script>
