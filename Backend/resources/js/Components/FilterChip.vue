<template>
    <button
        type="button"
        class="rounded-full border px-4 py-1.5 text-sm font-semibold transition"
        :class="
            active
                ? 'border-slate-900 bg-slate-900 text-white shadow-[0_10px_24px_rgba(15,23,42,0.25)]'
                : 'border-transparent bg-slate-100 text-slate-500 hover:border-slate-200 hover:bg-white'
        "
        @click="$emit('update:modelValue', value)"
    >
        {{ label }}
    </button>
</template>

<script setup>
defineProps({
    label: { type: String, required: true },
    value: { type: String, required: true },
    modelValue: { type: String, default: '' },
    active: { type: Boolean, default: false },
});
</script>
