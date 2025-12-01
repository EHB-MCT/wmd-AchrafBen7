<template>
    <div class="flex min-h-screen bg-slate-50 text-slate-900">
        <aside class="hidden w-72 flex-col justify-between bg-slate-900 px-6 py-8 text-white lg:flex">
            <div>
                <div class="mb-10">
                    <p class="text-sm uppercase tracking-wide text-slate-400">NiOS</p>
                    <h1 class="mt-1 text-2xl font-semibold">Analytics</h1>
                    <p class="text-sm text-slate-400">Insights comportementaux</p>
                </div>
                <nav class="space-y-1">
                    <SidebarLink
                        v-for="link in links"
                        :key="link.href"
                        :href="link.href"
                        :label="link.label"
                        :icon="link.icon"
                    />
                </nav>
            </div>
            <div class="rounded-2xl border border-white/10 bg-white/5 p-4 text-sm text-slate-200">
                <p class="text-xs uppercase tracking-wide text-slate-400">Version</p>
                <p class="text-lg font-semibold text-white">{{ version }}</p>
                <p class="text-xs text-slate-400">votre build stable</p>
            </div>
        </aside>
        <div class="flex w-full flex-1 flex-col">
            <header class="sticky top-0 z-10 border-b border-slate-200 bg-white/90 backdrop-blur">
                <div class="flex items-center justify-between px-6 py-4">
                    <div>
                        <p class="text-xs uppercase tracking-wide text-slate-400">Bienvenue</p>
                        <p class="text-lg font-semibold">{{ user?.name ?? 'Product Team' }}</p>
                    </div>
                    <div class="flex items-center gap-4">
                        <DateRangePicker v-model="range" />
                        <button
                            type="button"
                            class="rounded-full border border-slate-200 bg-white p-2 text-slate-500 shadow-sm hover:text-slate-700"
                            aria-label="Notifications"
                        >
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path
                                    d="M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 00-12 0v3.2a2 2 0 01-.6 1.4L4 17h5m2 0v1a2 2 0 104 0v-1"
                                    stroke-width="1.5"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />
                            </svg>
                        </button>
                        <div class="flex items-center gap-3 rounded-full border border-slate-200 bg-white px-3 py-1 shadow-sm">
                            <div class="h-9 w-9 rounded-full bg-sky-500 text-center text-lg leading-9 text-white">
                                {{ initials }}
                            </div>
                            <div>
                                <p class="text-sm font-semibold">{{ user?.name ?? 'Analyst' }}</p>
                                <p class="text-xs text-slate-500">Comportemental</p>
                            </div>
                        </div>
                    </div>
                </div>
            </header>
            <main class="flex-1 overflow-y-auto px-6 py-8">
                <slot />
            </main>
        </div>
    </div>
</template>

<script setup>
import { computed, ref } from 'vue';
import { usePage } from '@inertiajs/vue3';
import DateRangePicker from '../Components/DateRangePicker.vue';
import SidebarLink from '../Components/SidebarLink.vue';

const links = [
    { href: '/dashboard', label: 'Vue d’ensemble', icon: 'dashboard' },
    { href: '/sessions', label: 'Sessions', icon: 'sessions' },
    { href: '/events', label: 'Événements', icon: 'events' },
    { href: '/search', label: 'Recherches', icon: 'search' },
    { href: '/conversions', label: 'Conversions', icon: 'conversions' },
    { href: '/heatmap', label: 'Heatmap', icon: 'heatmap' },
    { href: '/settings', label: 'Paramètres', icon: 'settings' },
];

const page = usePage();
const user = computed(() => page.props.auth?.user);
const version = computed(() => page.props.app?.version ?? 'v1.0.0');
const initials = computed(() => (user.value?.name ?? 'NA').split(' ').map((part) => part[0]).join('').slice(0, 2));
const range = ref('7d');
</script>
