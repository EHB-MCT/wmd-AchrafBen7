<template>
    <div class="space-y-6">
        <div>
            <p class="text-sm text-slate-500">Zie waar gebruikers klikken en scrollen</p>
            <h2 class="text-3xl font-semibold text-slate-900">Heatmap</h2>
        </div>

        <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-lg font-semibold">Live heatmap</p>
                    <p class="text-sm text-slate-500">Interacties gebaseerd op device_x / device_y</p>
                </div>
                <div class="flex items-center gap-3">
                    <FilterChip label="Mobile" value="mobile" :model-value="'mobile'" :active="true" />
                    <DateRangePicker v-model="selectedRange" :presets="heatmapRanges" />
                </div>
            </div>
            <div class="mt-6 grid gap-6 lg:grid-cols-3">
                <div ref="heatmapContainer" class="rounded-3xl border border-dashed border-slate-200 bg-slate-50 p-3 lg:col-span-2">
                    <canvas ref="heatmapCanvas" class="h-[420px] w-full rounded-2xl bg-white" />
                </div>
                <div class="space-y-4 rounded-3xl border border-slate-100 bg-slate-50 p-4">
                    <div>
                        <p class="text-xs uppercase text-slate-400">Events geplot</p>
                        <p class="text-3xl font-semibold text-slate-900">{{ heatmap.meta.total_events ?? 0 }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase text-slate-400">Top hotspots</p>
                        <ul class="mt-3 space-y-2">
                            <li v-for="hotspot in hotspots" :key="hotspot.x + '-' + hotspot.y" class="flex items-center justify-between rounded-2xl bg-white px-3 py-2">
                                <span class="text-sm text-slate-600">x: {{ hotspot.x }}, y: {{ hotspot.y }}</span>
                                <span class="text-sm font-semibold text-slate-900">{{ hotspot.count }}</span>
                            </li>
                            <li v-if="hotspots.length === 0" class="text-sm text-slate-400">Geen events in deze periode.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, inject, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import axios from 'axios';
import AppLayout from '../Layouts/AppLayout.vue';
import FilterChip from '../Components/FilterChip.vue';
import DateRangePicker from '../Components/DateRangePicker.vue';

defineOptions({ layout: AppLayout });

const selectedRange = inject('globalRange', ref('24h'));
const heatmap = ref({ points: [], meta: {} });
const heatmapCanvas = ref(null);
const heatmapContainer = ref(null);
const heatmapRanges = [
    { label: 'Laatste 24u', value: '24h' },
    { label: 'Laatste 7 dagen', value: '7d' },
    { label: 'Laatste 30 dagen', value: '30d' },
];

const loadHeatmap = async () => {
    const { data } = await axios.get('/api/stats/heatmap', {
        params: { range: selectedRange.value },
    });
    heatmap.value = data;
    drawHeatmap();
};

const drawHeatmap = () => {
    const canvas = heatmapCanvas.value;
    const container = heatmapContainer.value;

    if (!canvas || !container) {
        return;
    }

    const width = Math.max(200, container.clientWidth - 24);
    const height = 420;
    const ctx = canvas.getContext('2d');

    canvas.width = width;
    canvas.height = height;
    ctx.clearRect(0, 0, width, height);

    heatmap.value.points.forEach((point) => {
        const x = point.normalized_x * width;
        const y = height - point.normalized_y * height;
        const radius = 80 * Math.max(0.2, point.intensity);
        const gradient = ctx.createRadialGradient(x, y, 0, x, y, radius);
        gradient.addColorStop(0, `rgba(59,130,246,${point.intensity})`);
        gradient.addColorStop(1, 'rgba(59,130,246,0)');
        ctx.fillStyle = gradient;
        ctx.fillRect(x - radius, y - radius, radius * 2, radius * 2);
    });
};

const hotspots = computed(() => {
    return [...(heatmap.value.points ?? [])]
        .sort((a, b) => b.count - a.count)
        .slice(0, 4);
});

onMounted(() => {
    loadHeatmap();
    window.addEventListener('resize', drawHeatmap);
});

onBeforeUnmount(() => {
    window.removeEventListener('resize', drawHeatmap);
});

watch(selectedRange, loadHeatmap);
watch(
    () => heatmap.value.points,
    () => {
        drawHeatmap();
    }
);
</script>
