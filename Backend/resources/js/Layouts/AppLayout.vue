<template>
    <div class="flex min-h-screen bg-[#0b1220] text-slate-900">
        <aside
            class="hidden w-72 flex-col justify-between border-r border-white/5 bg-gradient-to-b from-[#111a2e] to-[#0c1223] px-7 py-8 text-white lg:flex"
        >
            <div>
                <div class="mb-12">
                    <p class="text-sm font-semibold text-slate-400">NiOS</p>
                    <h1 class="mt-1 text-3xl font-semibold tracking-tight text-white">Analytics</h1>
                    <p class="text-sm text-slate-500">Gedragsinzichten</p>
                </div>
                <nav class="space-y-1.5">
                    <SidebarLink
                        v-for="link in links"
                        :key="link.href"
                        :href="link.href"
                        :label="link.label"
                        :icon="link.icon"
                    />
                </nav>
            </div>
            <div class="rounded-2xl border border-white/5 bg-white/5 px-5 py-4 text-sm text-slate-200">
                <p class="text-xs uppercase tracking-wide text-slate-400">Version</p>
                <p class="text-lg font-semibold text-white">{{ version }}</p>
                <p class="text-xs text-slate-400">v1.0.0 Beta</p>
                <p class="text-xs text-slate-500 mt-1">stabiele build</p>
            </div>
        </aside>
        <div class="flex w-full flex-1 flex-col bg-[#f3f6fb]">
            <header class="border-b border-slate-200 bg-[#f9fbff]">
                <div class="mx-auto flex w-full max-w-6xl items-center justify-between px-8 py-5">
                    <div>
                        <p class="text-sm font-medium text-slate-400">Welkom</p>
                        <p class="text-xl font-semibold text-slate-900">{{ user?.name ?? 'Productteam' }}</p>
                    </div>
                    <div class="flex items-center gap-5">
                        <DateRangePicker v-model="range" />
                        <button
                            type="button"
                            class="rounded-full border border-slate-200 bg-white p-2 text-slate-500 shadow-sm transition hover:text-slate-700"
                            aria-label="Notifications"
                        >
                            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path
                                    d="M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 00-12 0v3.2a2 2 0 01-.6 1.4L4 17h5m2 0v1a2 2 0 104 0v-1"
                                    stroke-width="1.5"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />
                            </svg>
                        </button>
                        <div class="flex items-center gap-3 rounded-full border border-slate-200 bg-white px-4 py-1.5 shadow-sm">
                            <div class="h-10 w-10 rounded-full bg-sky-500 text-center text-lg leading-10 text-white">
                                {{ initials }}
                            </div>
                            <div>
                                <p class="text-sm font-semibold">{{ user?.name ?? 'Analyst' }}</p>
                                <p class="text-xs text-slate-500">Gedrag</p>
                            </div>
                        </div>
                    </div>
                </div>
            </header>
            <main class="flex-1 overflow-y-auto px-8 py-10">
                <div class="mx-auto w-full max-w-6xl">
                    <slot />
                </div>
            </main>
        </div>
    </div>
</template>

<script setup>
import { computed, provide, ref } from 'vue';
import { usePage } from '@inertiajs/vue3';
import DateRangePicker from '../Components/DateRangePicker.vue';
import SidebarLink from '../Components/SidebarLink.vue';

const links = [
    { href: '/dashboard', label: 'Overzicht', icon: 'dashboard' },
    { href: '/sessions', label: 'Sessies', icon: 'sessions' },
    { href: '/events', label: 'Evenementen', icon: 'events' },
    { href: '/timeline', label: 'User Timeline', icon: 'timeline' },
    { href: '/search', label: 'Zoekopdrachten', icon: 'search' },
    { href: '/conversions', label: 'Conversies', icon: 'conversions' },
    { href: '/heatmap', label: 'Heatmap', icon: 'heatmap' },
    { href: '/settings', label: 'Instellingen', icon: 'settings' },
];

const page = usePage();
const user = computed(() => page.props.auth?.user);
const version = computed(() => page.props.app?.version ?? 'v1.0.0');
const initials = computed(() => (user.value?.name ?? 'NA').split(' ').map((part) => part[0]).join('').slice(0, 2));
const range = ref('7d');

provide('globalRange', range);
</script>
