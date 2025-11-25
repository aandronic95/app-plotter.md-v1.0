<script setup lang="ts">
import AppFooter from '@/components/AppFooter.vue';
import PublicHeader from '@/components/PublicHeader.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { Search } from 'lucide-vue-next';

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
    <Head title="Pagini" />
    <div class="flex min-h-screen flex-col">
        <PublicHeader />

        <main class="flex-1">
            <div class="mx-auto max-w-7xl px-4 py-8 md:px-6">
                <!-- Header -->
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                        Pagini
                    </h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">
                        Explorează paginile disponibile pe site
                    </p>
                </div>

                <!-- Search -->
                <div class="mb-6">
                    <div class="relative">
                        <Search class="absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400" />
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Caută pagini..."
                            class="w-full rounded-lg border border-gray-300 bg-white py-2 pl-10 pr-4 text-gray-900 placeholder-gray-500 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white dark:placeholder-gray-400"
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
                        class="group rounded-lg border border-gray-200 bg-white p-6 shadow-sm transition-all hover:border-primary-300 hover:shadow-lg dark:border-gray-700 dark:bg-gray-800 dark:hover:border-primary-600"
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
                                    Publicat: {{ new Date(page.published_at).toLocaleDateString('ro-RO') }}
                                </span>
                                <span class="text-primary-600 hover:text-primary-700 dark:text-primary-400">
                                    Citește mai mult →
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
                        Nu există pagini disponibile momentan.
                    </p>
                </div>
                <div
                    v-else
                    class="flex flex-col items-center justify-center py-12"
                >
                    <p class="text-lg text-gray-500 dark:text-gray-400">
                        Nu s-au găsit pagini care să corespundă căutării.
                    </p>
                    <button
                        @click="searchQuery = ''"
                        class="mt-4 text-primary-600 hover:text-primary-700 dark:text-primary-400"
                    >
                        Șterge căutarea
                    </button>
                </div>
            </div>
        </main>

        <AppFooter />
    </div>
</template>


