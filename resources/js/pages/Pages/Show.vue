<script setup lang="ts">
import AppFooter from '@/components/AppFooter.vue';
import PublicHeader from '@/components/PublicHeader.vue';
import { Head, Link } from '@inertiajs/vue3';
import { useTranslations } from '@/composables/useTranslations';

interface Page {
    id: number;
    title: string;
    slug: string;
    content?: string;
    excerpt?: string;
    meta_title?: string;
    meta_description?: string;
    published_at?: string;
    created_at: string;
    updated_at: string;
}

interface Props {
    page: Page;
}

defineProps<Props>();
const { t } = useTranslations();
</script>

<template>
    <Head
        :title="page.meta_title || page.title"
        :description="page.meta_description || page.excerpt"
    />
    <div class="flex min-h-screen flex-col dark:bg-gray-900">
        <PublicHeader />

        <main class="flex-1">
            <div class="mx-auto max-w-7xl px-4 py-8 md:px-6">
                <!-- Breadcrumb -->
                <nav class="mb-6 text-sm">
                    <Link
                        href="/"
                        class="text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100"
                    >
                        {{ t('home') }}
                    </Link>
                    <span class="mx-2 text-gray-400">/</span>
                    <Link
                        href="/pages"
                        class="text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100"
                    >
                        {{ t('pages') }}
                    </Link>
                    <span class="mx-2 text-gray-400">/</span>
                    <span class="text-gray-900 dark:text-white">{{ page.title }}</span>
                </nav>

                <!-- Page Content -->
                <article class="rounded-lg bg-white p-8 dark:bg-gray-800">
                    <header class="mb-6">
                        <h1 class="mb-4 text-4xl font-bold text-gray-900 dark:text-white">
                            {{ page.title }}
                        </h1>
                        <div class="flex items-center gap-4 text-sm text-gray-500 dark:text-gray-400">
                            <span v-if="page.published_at">
                                {{ t('published_on') }}: {{ new Date(page.published_at).toLocaleDateString('ro-RO', {
                                    year: 'numeric',
                                    month: 'long',
                                    day: 'numeric',
                                }) }}
                            </span>
                            <span v-else>
                                {{ t('created_on') }}: {{ new Date(page.created_at).toLocaleDateString('ro-RO', {
                                    year: 'numeric',
                                    month: 'long',
                                    day: 'numeric',
                                }) }}
                            </span>
                        </div>
                    </header>

                    <div
                        v-if="page.excerpt"
                        class="mb-6 rounded-lg bg-gray-50 p-4 text-lg italic text-gray-700 dark:bg-gray-900 dark:text-gray-300"
                    >
                        {{ page.excerpt }}
                    </div>

                    <div
                        v-if="page.content"
                        class="prose prose-lg max-w-none dark:prose-invert prose-headings:text-gray-900 dark:prose-headings:text-white prose-p:text-gray-700 dark:prose-p:text-gray-300 prose-a:text-primary-600 dark:prose-a:text-primary-400 prose-strong:text-gray-900 dark:prose-strong:text-white prose-ul:text-gray-700 dark:prose-ul:text-gray-300 prose-ol:text-gray-700 dark:prose-ol:text-gray-300"
                        v-html="page.content"
                    />

                    <div
                        v-else
                        class="text-gray-500 dark:text-gray-400"
                    >
                        {{ t('page_content_not_available') }}
                    </div>
                </article>

                <!-- Back to Pages -->
                <div class="mt-8">
                    <Link
                        href="/pages"
                        class="inline-flex items-center text-primary-600 hover:text-primary-700 dark:text-primary-400"
                    >
                        {{ t('back_to_pages') }}
                    </Link>
                </div>
            </div>
        </main>

        <AppFooter />
    </div>
</template>

