<template>
    <div class="relative w-full" :style="{ height: `${height}px` }">
        <Line :data="chartData" :options="chartOptions" />
    </div>
</template>

<script setup>
import {
    Chart as ChartJS,
    CategoryScale,
    Filler,
    Legend,
    LineElement,
    LinearScale,
    PointElement,
    Title,
    Tooltip,
} from 'chart.js';
import { computed } from 'vue';
import { Line } from 'vue-chartjs';

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend, Filler);

const props = defineProps({
    labels: {
        type: Array,
        required: true,
    },
    datasets: {
        type: Array,
        required: true,
    },
    height: {
        type: Number,
        default: 320,
    },
    options: {
        type: Object,
        default: () => ({}),
    },
});

const chartData = computed(() => ({
    labels: props.labels,
    datasets: props.datasets,
}));

const chartOptions = computed(() => ({
    responsive: true,
    maintainAspectRatio: false,
    interaction: {
        intersect: false,
        mode: 'index',
    },
    plugins: {
        legend: {
            display: false,
        },
    },
    scales: {
        x: {
            grid: {
                display: false,
            },
        },
        y: {
            beginAtZero: true,
            ticks: {
                stepSize: 1,
            },
        },
    },
    tension: 0.5,
    ...props.options,
}));
</script>
