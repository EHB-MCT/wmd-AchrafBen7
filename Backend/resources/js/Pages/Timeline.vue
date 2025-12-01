<template>
    <div class="space-y-6">
        <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
            <div>
                <p class="text-sm text-slate-500">Volledige gebruikersflow</p>
                <h2 class="text-3xl font-semibold text-slate-900">User Timeline</h2>
            </div>
            <div class="flex flex-col gap-3 md:flex-row md:items-center md:gap-4">
                <label class="flex items-center gap-2 text-sm text-slate-500">
                    <span>Gebruiker</span>
                    <select v-model="selectedUser" class="rounded-xl border border-slate-200 px-3 py-2 text-sm shadow-sm">
                        <option v-for="user in users" :key="user.id" :value="user.id">
                            {{ user.name }}
                        </option>
                    </select>
                </label>
                <DateRangePicker v-model="selectedRange" />
            </div>
        </div>

        <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-lg font-semibold">Laatste activiteiten</p>
                <p class="text-xs uppercase text-slate-400">{{ timelineEntries.length }} events</p>
            </div>
            <ol class="relative mt-6 border-l-2 border-slate-100 pl-6">
                <li
                    v-for="entry in timelineEntries"
                    :key="entry.id"
                    class="mb-6 ml-4 rounded-2xl bg-slate-50/70 p-4 shadow-sm"
                >
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="rounded-full px-3 py-1 text-xs font-semibold" :class="badgeClass(entry.kind, entry.type)">
                                {{ entryBadge(entry.kind, entry.type) }}
                            </span>
                            <p class="text-sm font-semibold text-slate-900">{{ entry.label }}</p>
                        </div>
                        <p class="text-xs text-slate-400">{{ entry.displayTime }}</p>
                    </div>
                    <div v-if="entry.meta" class="mt-2 text-sm text-slate-500">
                        <span v-if="entry.meta.screen">Scherm: {{ entry.meta.screen }}</span>
                        <span v-if="entry.meta.details" class="ml-2 text-slate-400">{{ entry.meta.details }}</span>
                        <span v-if="entry.meta.platform">Platform: {{ entry.meta.platform }}</span>
                        <span v-if="entry.meta.duration">Duur: {{ entry.meta.duration }}</span>
                    </div>
                </li>
                <li v-if="timelineEntries.length === 0" class="text-center text-sm text-slate-400">
                    Geen activiteiten voor deze periode.
                </li>
            </ol>
        </div>
    </div>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue';
import axios from 'axios';
import AppLayout from '../Layouts/AppLayout.vue';
import DateRangePicker from '../Components/DateRangePicker.vue';

defineOptions({ layout: AppLayout });

const selectedRange = ref('7d');
const selectedUser = ref(null);
const users = ref([]);
const entries = ref([]);
const hasBootstrappedUser = ref(false);
const formatter = new Intl.DateTimeFormat('nl-NL', { dateStyle: 'medium', timeStyle: 'short' });

const loadTimeline = async () => {
    const { data } = await axios.get('/api/stats/timeline', {
        params: { user_id: selectedUser.value, range: selectedRange.value },
    });
    users.value = data.users ?? [];
    entries.value = data.entries ?? [];
    if (data.active_user) {
        selectedUser.value = data.active_user;
    }
};

onMounted(loadTimeline);
watch(selectedRange, loadTimeline);

watch(
    selectedUser,
    (value, oldValue) => {
        if (!value) {
            return;
        }

        if (!hasBootstrappedUser.value) {
            hasBootstrappedUser.value = true;
            return;
        }

        if (value !== oldValue) {
            loadTimeline();
        }
    }
);

const timelineEntries = computed(() =>
    entries.value.map((entry) => ({
        ...entry,
        displayTime: entry.timestamp ? formatter.format(new Date(entry.timestamp)) : 'Onbekend',
    }))
);

const badgeClass = (kind, type) => {
    if (kind === 'session_start') {
        return 'bg-emerald-100 text-emerald-700';
    }
    if (kind === 'session_end') {
        return 'bg-slate-200 text-slate-600';
    }
    if (type === 'conversion') {
        return 'bg-amber-100 text-amber-700';
    }
    return 'bg-sky-100 text-sky-700';
};

const entryBadge = (kind, type) => {
    if (kind === 'session_start') {
        return 'Sessiestart';
    }
    if (kind === 'session_end') {
        return 'Session end';
    }
    if (type === 'conversion') {
        return 'Conversion';
    }
    return type ?? 'Event';
};
</script>
