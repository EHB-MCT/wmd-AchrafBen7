<template>
    <div class="relative" ref="root">
        <button
            type="button"
            class="flex items-center gap-2 rounded-full border border-slate-200 bg-white/90 px-5 py-2.5 text-sm font-semibold text-slate-600 shadow-[0_12px_30px_rgba(15,23,42,0.08)] transition hover:border-slate-300"
            @click="open = !open"
        >
            <span>{{ activeLabel }}</span>
            <svg class="h-4 w-4 text-slate-400" viewBox="0 0 20 20" fill="none" stroke="currentColor">
                <path d="M6 8l4 4 4-4" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
        </button>
        <div
            v-if="open"
            class="absolute right-0 z-20 mt-3 w-56 rounded-3xl border border-slate-100 bg-white/95 p-4 text-sm shadow-[0_20px_45px_rgba(15,23,42,0.15)]"
        >
            <p class="px-2 pb-3 text-xs font-semibold uppercase tracking-wide text-slate-400">Periode</p>
            <ul class="space-y-2">
                <li v-for="preset in presets" :key="preset.value">
                    <button
                        type="button"
                        class="flex w-full items-center justify-between rounded-2xl px-3.5 py-2.5 text-sm transition"
                        :class="
                            modelValue === preset.value
                                ? 'bg-slate-900 text-white shadow-[0_10px_25px_rgba(15,23,42,0.2)]'
                                : 'text-slate-600 hover:bg-slate-100'
                        "
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
            { label: 'Laatste 24u', value: '24h' },
            { label: 'Laatste 7 dagen', value: '7d' },
            { label: 'Laatste 30 dagen', value: '30d' },
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

const activeLabel = computed(() => props.presets.find((preset) => preset.value === props.modelValue)?.label ?? 'Periode');

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
