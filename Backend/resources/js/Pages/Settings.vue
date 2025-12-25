<template>
    <div class="space-y-6">
        <div>
            <p class="text-sm text-slate-500">Personaliseer je ervaring</p>
            <h2 class="text-3xl font-semibold text-slate-900">Instellingen</h2>
        </div>

        <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
            <form class="space-y-5">
                <div class="grid gap-5 md:grid-cols-2">
                    <label class="text-sm font-medium text-slate-700">
                        Naam organisatie
                        <input
                            type="text"
                            class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm shadow-sm focus:border-sky-400 focus:outline-none"
                            placeholder="NiOS Analytics"
                        />
                    </label>
                    <label class="text-sm font-medium text-slate-700">
                        Support e-mail
                        <input
                            type="email"
                            class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm shadow-sm focus:border-sky-400 focus:outline-none"
                            placeholder="support@nios.app"
                        />
                    </label>
                </div>
                <label class="text-sm font-medium text-slate-700">
                    Webhook (realtime)
                    <input
                        type="url"
                        class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm shadow-sm focus:border-sky-400 focus:outline-none"
                        placeholder="https://api.nios.app/webhooks/events"
                    />
                </label>
                <div class="flex items-center justify-end">
                    <button
                        type="button"
                        class="rounded-full bg-slate-900 px-5 py-2 text-sm font-semibold text-white shadow hover:bg-slate-800"
                    >
                        Opslaan
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import AppLayout from '../Layouts/AppLayout.vue';

defineOptions({ layout: AppLayout });
</script>
