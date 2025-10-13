<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const user = computed(() => page.props.value?.auth?.user ?? null);
const roles = computed(() => (user.value && Array.isArray(user.value.roles)) ? user.value.roles : []);
const displayName = computed(() => user.value?.name ?? 'Guest');
</script>

<template>
    <Head>
        <title>Dashboard</title>
    </Head>

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Dashboard</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-lg font-medium">Welcome, {{ displayName }}!</h3>
                        <p class="mt-2">Your roles: <span class="font-semibold">{{ (roles.length ? roles.join(', ') : 'none') }}</span></p>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
