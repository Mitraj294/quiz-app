<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

defineProps({ topics: Array });

const form = useForm({ name: '', parent_id: null, is_active: true });

function submit() {
    form.post(route('topics.store'));
}
</script>

<template>
    <Head title="Topics" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Topics</h2>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
                <div class="bg-white p-6 shadow sm:rounded-lg">
                    <h3 class="text-lg font-medium">Create topic</h3>
                    <div class="mt-4">
                        <input v-model="form.name" placeholder="Topic name" class="border rounded p-2 w-full" />
                        <div class="mt-2 flex items-center gap-2">
                            <select v-model="form.parent_id" class="border rounded p-2">
                                <option :value="null">No parent</option>
                                <option v-for="t in topics" :key="t.id" :value="t.id">{{ t.name }}</option>
                            </select>
                            <label class="flex items-center gap-2">
                                <input type="checkbox" v-model="form.is_active" /> Active
                            </label>
                            <button @click.prevent="submit" class="bg-blue-600 text-white px-3 py-1 rounded">Create</button>
                        </div>
                        <div v-if="form.errors" class="text-red-600 mt-2">
                            <div v-for="(err, key) in form.errors" :key="key">{{ err }}</div>
                        </div>
                    </div>
                </div>

                <div class="bg-white p-6 mt-6 shadow sm:rounded-lg">
                    <h3 class="text-lg font-medium">Existing topics</h3>
                    <ul class="mt-4 divide-y">
                        <li v-for="t in topics" :key="t.id" class="py-2 flex justify-between items-center">
                            <div>
                                <Link :href="route('topics.show', t.id)" class="font-semibold text-blue-600">{{ t.name }}</Link>
                                <div class="text-sm text-gray-600">Slug: {{ t.slug }} · Active: {{ t.is_active ? 'yes' : 'no' }}</div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
