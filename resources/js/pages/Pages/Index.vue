<script setup lang="ts">
import AppFooter from '@/components/AppFooter.vue';
import PublicHeader from '@/components/PublicHeader.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { Search } from 'lucide-vue-next';
import { useTranslations } from '@/composables/useTranslations';

interface Page {
    id: number;
    title: string;
    slug: string;
    excerpt?: string;
    content?: string;
    published_at?: string;
    created_at: string;
}

interface Props {
    pages: Page[];
}

const props = defineProps<Props>();
const { t } = useTranslations();
const searchQuery = ref('');

const filteredPages = computed(() => {
    if (!searchQuery.value.trim()) {
        return props.pages;
    }
    
    const query = searchQuery.value.toLowerCase();
    return props.pages.filter(page => 
        page.title.toLowerCase().includes(query) ||
        page.excerpt?.toLowerCase().includes(query) ||
        page.slug.toLowerCase().includes(query)
    );
});

const handleSearch = () => {
    // Search is handled by computed property
};
</script>

<template>
    <Head :title="t('pages_title')" />
    <div class="flex min-h-screen flex-col dark:bg-gray-900">
        <PublicHeader />

        <main class="flex-1">
            <div class="mx-auto max-w-7xl px-4 py-8 md:px-6">
                <!-- Header -->
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                        {{ t('pages_title') }}
                    </h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">
                        {{ t('explore_available_pages') }}
                    </p>
                </div>

                <!-- Search -->
                <div class="mb-6">
                    <div class="relative">
                        <Search class="absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400" />
                        <input
                            v-model="searchQuery"
                            type="text"
                            :placeholder="t('search_pages')"
                            class="w-full rounded-lg bg-white py-2 pl-10 pr-4 text-gray-900 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-gray-800 dark:text-white dark:placeholder-gray-400"
                            @input="handleSearch"
                        />
                    </div>
                </div>

                <!-- Pages List -->
                <div
                    v-if="filteredPages.length > 0"
                    class="space-y-6"
                >
                    <article
                        v-for="page in filteredPages"
                        :key="page.id"
                        class="group rounded-lg bg-white p-6 transition-all dark:bg-gray-800"
                    >
                        <Link
                            :href="`/${page.slug}`"
                            class="block"
                        >
                            <h2 class="mb-2 text-xl font-semibold text-gray-900 dark:text-white">
                                {{ page.title }}
                            </h2>
                            <p
                                v-if="page.excerpt"
                                class="mb-4 text-gray-600 dark:text-gray-400"
                            >
                                {{ page.excerpt }}
                            </p>
                            <div class="flex items-center justify-between text-sm text-gray-500 dark:text-gray-400">
                                <span v-if="page.published_at">
                                    {{ t('published_on') }}: {{ new Date(page.published_at).toLocaleDateString('ro-RO') }}
                                </span>
                                <span class="text-primary-600 hover:text-primary-700 dark:text-primary-400">
                                    {{ t('read_more') }}
                                </span>
                            </div>
                        </Link>
                    </article>
                </div>

                <!-- Empty State -->
                <div
                    v-else-if="pages.length === 0"
                    class="flex flex-col items-center justify-center py-12"
                >
                    <p class="text-lg text-gray-500 dark:text-gray-400">
                        {{ t('no_pages_available') }}
                    </p>
                </div>
                <div
                    v-else
                    class="flex flex-col items-center justify-center py-12"
                >
                    <p class="text-lg text-gray-500 dark:text-gray-400">
                        {{ t('no_pages_found_for_search') }}
                    </p>
                    <button
                        @click="searchQuery = ''"
                        class="mt-4 text-primary-600 hover:text-primary-700 dark:text-primary-400"
                    >
                        {{ t('clear_search') }}
                    </button>
                </div>
            </div>
        </main>

        <AppFooter />
    </div>
</template>


