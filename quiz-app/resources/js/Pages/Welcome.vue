<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    canLogin: {
        type: Boolean,
        default: false,
    },
    canRegister: {
        type: Boolean,
        default: false,
    },
    appName: {
        type: String,
        required: true,
    },
});

const welcomeTitle = computed(() => `Welcome to ${props.appName}`);
</script>

<template>
    <Head>
        <title>{{ welcomeTitle }}</title>
    </Head>
    <div
        class="flex min-h-screen flex-col items-center justify-center bg-slate-950 px-6 py-12 text-white"
    >
        <div class="w-full max-w-2xl text-center">
            <h1 class="text-4xl font-semibold sm:text-5xl">
                {{ welcomeTitle }}
            </h1>

            <p class="mt-6 text-base text-white/70 sm:text-lg">
                We're glad you're here. Explore quizzes, build knowledge, and
                make learning a habit with
                <span class="font-semibold text-white">{{ props.appName }}</span
                >.
            </p>

            <div
                v-if="canLogin"
                class="mt-10 flex flex-wrap items-center justify-center gap-4"
            >
                <Link
                    v-if="$page.props.auth?.user"
                    :href="route('dashboard')"
                    class="rounded-full bg-white px-6 py-2 text-sm font-semibold text-slate-900 transition hover:bg-white/90"
                >
                    Go to dashboard
                </Link>

                <template v-else>
                    <Link
                        :href="route('login')"
                        class="rounded-full border border-white/30 px-6 py-2 text-sm font-semibold transition hover:border-white/60 hover:text-white"
                    >
                        Log in
                    </Link>

                    <Link
                        v-if="canRegister"
                        :href="route('register')"
                        class="rounded-full bg-white px-6 py-2 text-sm font-semibold text-slate-900 transition hover:bg-white/90"
                    >
                        Create an account
                    </Link>
                </template>
            </div>
        </div>
    </div>
</template>
