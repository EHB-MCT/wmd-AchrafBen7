<template>
    <div class="relative" ref="root">
        <button
            type="button"
            class="flex items-center gap-2 rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-600 shadow-sm transition hover:border-slate-300"
            @click="open = !open"
        >
            <span>{{ activeLabel }}</span>
            <svg class="h-4 w-4 text-slate-400" viewBox="0 0 20 20" fill="none" stroke="currentColor">
                <path d="M6 8l4 4 4-4" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
        </button>
        <div
            v-if="open"
            class="absolute right-0 z-20 mt-2 w-48 rounded-2xl border border-slate-100 bg-white p-3 text-sm shadow-xl"
        >
            <p class="px-2 pb-2 text-xs font-semibold uppercase text-slate-400">Plage</p>
            <ul class="space-y-1">
                <li v-for="preset in presets" :key="preset.value">
                    <button
                        type="button"
                        class="flex w-full items-center justify-between rounded-xl px-3 py-2 transition"
                        :class="modelValue === preset.value ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-100'"
                        @click="selectRange(preset.value)"
                    >
                        <span>{{ preset.label }}</span>
                        <span v-if="modelValue === preset.value" class="text-xs">•</span>
                    </button>
                </li>
            </ul>
        </div>
    </div>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';

const props = defineProps({
    modelValue: {
        type: String,
        default: '7d',
    },
    presets: {
        type: Array,
        default: () => [
            { label: 'Dernières 24h', value: '24h' },
            { label: '7 derniers jours', value: '7d' },
            { label: '30 derniers jours', value: '30d' },
        ],
    },
});

const emit = defineEmits(['update:modelValue']);
const open = ref(false);
const root = ref(null);

const selectRange = (value) => {
    emit('update:modelValue', value);
    open.value = false;
};

const activeLabel = computed(() => props.presets.find((preset) => preset.value === props.modelValue)?.label ?? 'Plage');

const handleClick = (event) => {
    if (!root.value) {
        return;
    }

    if (!root.value.contains(event.target)) {
        open.value = false;
    }
};

onMounted(() => document.addEventListener('click', handleClick));
onBeforeUnmount(() => document.removeEventListener('click', handleClick));
</script>
