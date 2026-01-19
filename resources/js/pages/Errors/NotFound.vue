<script setup lang="ts">
import AppFooter from '@/components/AppFooter.vue';
import PublicHeader from '@/components/PublicHeader.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Home, Search, ArrowLeft } from 'lucide-vue-next';
import { useTranslations } from '@/composables/useTranslations';

interface Props {
    status?: number;
}

defineProps<Props>();
const { t } = useTranslations();

const goBack = () => {
    if (window.history.length > 1) {
        window.history.back();
    } else {
        router.visit('/');
    }
};
</script>

<template>
    <Head :title="t('page_not_found')" />
    <div class="flex min-h-screen flex-col dark:bg-gray-900">
        <PublicHeader />

        <main class="flex-1">
            <div class="mx-auto flex max-w-7xl flex-col items-center justify-center px-4 py-16 md:px-6">
                <!-- 404 Illustration -->
                <div class="mb-8 text-center">
                    <h1 class="mb-4 text-9xl font-bold text-primary-600 dark:text-primary-400">
                        404
                    </h1>
                    <h2 class="mb-4 text-3xl font-bold text-gray-900 dark:text-white md:text-4xl">
                        {{ t('page_not_found_title') }}
                    </h2>
                    <p class="mx-auto max-w-md text-lg text-gray-600 dark:text-gray-400">
                        {{ t('page_not_found_message') }}
                    </p>
                </div>

                <!-- Actions -->
                <div class="flex flex-col gap-4 sm:flex-row">
                    <Link
                        href="/"
                        class="inline-flex items-center justify-center gap-2 rounded-lg bg-primary-600 px-6 py-3 font-semibold text-white transition-colors hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800"
                    >
                        <Home class="h-5 w-5" />
                        {{ t('main_page') }}
                    </Link>
                    <Link
                        href="/products"
                        class="inline-flex items-center justify-center gap-2 rounded-lg bg-white px-6 py-3 font-semibold text-gray-700 transition-colors hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 dark:focus:ring-offset-gray-800"
                    >
                        <Search class="h-5 w-5" />
                        {{ t('search_products_button') }}
                    </Link>
                    <button
                        @click="goBack"
                        class="inline-flex items-center justify-center gap-2 rounded-lg bg-white px-6 py-3 font-semibold text-gray-700 transition-colors hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 dark:focus:ring-offset-gray-800"
                    >
                        <ArrowLeft class="h-5 w-5" />
                        {{ t('back') }}
                    </button>
                </div>

                <!-- Helpful Links -->
                <div class="mt-12 w-full max-w-2xl">
                    <h3 class="mb-4 text-center text-lg font-semibold text-gray-900 dark:text-white">
                        {{ t('you_might_be_interested') }}
                    </h3>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <Link
                            href="/products"
                            class="rounded-lg bg-white p-4 transition-colors hover:bg-gray-50 dark:bg-gray-800"
                        >
                            <h4 class="mb-2 font-semibold text-gray-900 dark:text-white">
                                {{ t('products') }}
                            </h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                {{ t('explore_our_products') }}
                            </p>
                        </Link>
                        <Link
                            href="/pages"
                            class="rounded-lg bg-white p-4 transition-colors hover:bg-gray-50 dark:bg-gray-800"
                        >
                            <h4 class="mb-2 font-semibold text-gray-900 dark:text-white">
                                {{ t('pages') }}
                            </h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                {{ t('view_all_available_pages') }}
                            </p>
                        </Link>
                    </div>
                </div>
            </div>
        </main>

        <AppFooter />
    </div>
</template>

