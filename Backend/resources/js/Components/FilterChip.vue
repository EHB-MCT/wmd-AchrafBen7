<template>
    <button
        type="button"
        class="rounded-full px-4 py-1 text-sm font-medium transition"
        :class="active ? 'bg-slate-900 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
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
