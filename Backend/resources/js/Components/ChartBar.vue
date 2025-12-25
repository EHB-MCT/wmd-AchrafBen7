<template>
    <div class="relative w-full" :style="{ height: `${height}px` }">
        <Bar :data="chartData" :options="chartOptions" />
    </div>
</template>

<script setup>
import {
    BarElement,
    CategoryScale,
    Chart as ChartJS,
    Legend,
    LinearScale,
    Title,
    Tooltip,
} from 'chart.js';
import { computed } from 'vue';
import { Bar } from 'vue-chartjs';

ChartJS.register(CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend);

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
    stacked: {
        type: Boolean,
        default: false,
    },
});

const chartData = computed(() => ({
    labels: props.labels,
    datasets: props.datasets,
}));

const chartOptions = computed(() => ({
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            display: false,
        },
    },
    scales: {
        x: {
            stacked: props.stacked,
            grid: { display: false },
        },
        y: {
            stacked: props.stacked,
            beginAtZero: true,
        },
    },
}));
</script>
