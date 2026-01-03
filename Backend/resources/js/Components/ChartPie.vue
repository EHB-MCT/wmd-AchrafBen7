<template>
    <div class="relative w-full" :style="{ height: `${height}px` }">
        <Doughnut :data="chartData" :options="chartOptions" />
    </div>
</template>

<script setup>
import { ArcElement, Chart as ChartJS, Legend, Tooltip } from 'chart.js';
import { computed } from 'vue';
import { Doughnut } from 'vue-chartjs';

ChartJS.register(ArcElement, Tooltip, Legend);

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
});

const chartData = computed(() => ({
    labels: props.labels,
    datasets: props.datasets,
}));

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            display: false,
        },
    },
};
</script>
