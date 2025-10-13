<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

defineProps({ topic: Object });
</script>

<template>
    <Head title="Topic" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Topic: {{ topic.name }}</h2>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
                <div class="bg-white p-6 shadow sm:rounded-lg">
                    <h3 class="text-lg font-medium">{{ topic.name }}</h3>
                    <p class="text-sm text-gray-600">Slug: {{ topic.slug }}</p>
                    <p class="text-sm">Active: {{ topic.is_active ? 'Yes' : 'No' }}</p>
                </div>

                <div class="bg-white p-6 mt-6 shadow sm:rounded-lg">
                    <h4 class="font-semibold">Questions attached</h4>
                    <ul class="mt-2">
                        <li v-for="q in topic.questions" :key="q.id">{{ q.name }}</li>
                        <li v-if="(topic.questions || []).length === 0" class="text-sm text-gray-600">No questions attached.</li>
                    </ul>
                </div>

                <div class="bg-white p-6 mt-6 shadow sm:rounded-lg">
                    <h4 class="font-semibold">Quizzes attached</h4>
                    <ul class="mt-2">
                        <li v-for="q in topic.quizzes" :key="q.id">{{ q.name }}</li>
                        <li v-if="(topic.quizzes || []).length === 0" class="text-sm text-gray-600">No quizzes attached.</li>
                    </ul>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
