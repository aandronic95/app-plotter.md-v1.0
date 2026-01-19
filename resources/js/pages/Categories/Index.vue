<script setup lang="ts">
import AppFooter from '@/components/AppFooter.vue';
import PublicHeader from '@/components/PublicHeader.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ChevronRight, ChevronUp, ChevronDown } from 'lucide-vue-next';
import { ref } from 'vue';

interface SubCategory {
    id: number;
    name: string;
    slug: string;
    description?: string;
    image?: string;
    products_count: number;
}

interface Category {
    id: number;
    name: string;
    slug: string;
    description?: string;
    image?: string;
    products_count: number;
    children: SubCategory[];
}

interface Props {
    categories: Category[];
}

defineProps<Props>();
const expandedCategories = ref<Set<number>>(new Set());

const toggleCategory = (categoryId: number) => {
    if (expandedCategories.value.has(categoryId)) {
        expandedCategories.value.delete(categoryId);
    } else {
        expandedCategories.value.add(categoryId);
    }
};

const isExpanded = (categoryId: number) => {
    return expandedCategories.value.has(categoryId);
};
</script>

<template>
    <Head title="Categorii" />
    <div class="flex min-h-screen flex-col dark:bg-gray-900">
        <PublicHeader />

        <main class="flex-1">
            <div class="mx-auto max-w-7xl px-4 py-8 md:px-6">
                <!-- Header -->
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                        Toate categoriile
                    </h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">
                        Explorează toate categoriile și subcategoriile noastre de produse
                    </p>
                </div>

                <!-- Categories Grid -->
                <div
                    v-if="categories.length > 0"
                    class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3"
                >
                    <div
                        v-for="category in categories"
                        :key="category.id"
                        class="flex flex-col overflow-hidden rounded-lg bg-white transition-shadow dark:bg-gray-800"
                    >
                        <!-- Category Header -->
                        <div class="flex-1 p-6">
                            <div class="flex flex-col">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="flex-1">
                                        <Link
                                            :href="`/categories/${category.slug}`"
                                            class="text-xl font-semibold text-gray-900 transition-colors hover:text-primary-600 dark:text-white dark:hover:text-primary-400"
                                        >
                                            {{ category.name }}
                                        </Link>
                                        <span
                                            v-if="category.products_count > 0"
                                            class="mt-2 inline-block rounded-full bg-primary-100 px-3 py-1 text-sm font-medium text-primary-800 dark:bg-primary-900 dark:text-primary-200"
                                        >
                                            {{ category.products_count }} produse
                                        </span>
                                    </div>
                                    <button
                                        v-if="category.children.length > 0"
                                        @click="toggleCategory(category.id)"
                                        class="flex shrink-0 items-center justify-center rounded-lg p-2 text-gray-500 transition-colors hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-gray-200"
                                    >
                                        <ChevronDown
                                            v-if="!isExpanded(category.id)"
                                            class="h-5 w-5"
                                        />
                                        <ChevronUp
                                            v-else
                                            class="h-5 w-5"
                                        />
                                    </button>
                                </div>
                                <p
                                    v-if="category.description"
                                    class="mt-3 text-sm text-gray-600 dark:text-gray-400"
                                >
                                    {{ category.description }}
                                </p>
                            </div>
                        </div>

                        <!-- Subcategories -->
                        <div
                            v-if="category.children.length > 0"
                            :class="[
                                'overflow-hidden transition-all duration-300',
                                isExpanded(category.id) ? 'max-h-[2000px] opacity-100' : 'max-h-0 opacity-0',
                            ]"
                        >
                            <div class="bg-gray-50 px-6 py-4 dark:bg-gray-900">
                                <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-gray-700 dark:text-gray-300">
                                    Subcategorii ({{ category.children.length }})
                                </h3>
                                <div class="space-y-2">
                                    <Link
                                        v-for="subcategory in category.children"
                                        :key="subcategory.id"
                                        :href="`/categories/${subcategory.slug}`"
                                        class="group flex items-center justify-between rounded-lg bg-white p-3 transition-all hover:bg-primary-50 dark:bg-gray-800 dark:hover:bg-primary-900/20"
                                    >
                                        <div class="flex-1">
                                            <h4 class="text-sm font-semibold text-gray-900 group-hover:text-primary-600 dark:text-white dark:group-hover:text-primary-400">
                                                {{ subcategory.name }}
                                            </h4>
                                            <span
                                                v-if="subcategory.products_count > 0"
                                                class="mt-1 inline-block text-xs text-gray-500 dark:text-gray-400"
                                            >
                                                {{ subcategory.products_count }} produse
                                            </span>
                                        </div>
                                        <ChevronRight class="ml-2 h-4 w-4 text-gray-400 transition-transform group-hover:translate-x-1 group-hover:text-primary-600 dark:text-gray-500 dark:group-hover:text-primary-400" />
                                    </Link>
                                </div>
                            </div>
                        </div>

                        <!-- View Category Link -->
                        <div class="px-6 py-4">
                            <Link
                                :href="`/categories/${category.slug}`"
                                class="inline-flex items-center text-sm font-medium text-primary-600 transition-colors hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300"
                            >
                                Vezi toate produsele
                                <ChevronRight class="ml-1 h-4 w-4" />
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div
                    v-else
                    class="flex flex-col items-center justify-center py-12"
                >
                    <div class="mb-4 rounded-full bg-gray-100 p-4 dark:bg-gray-800">
                        <ChevronDown class="h-12 w-12 text-gray-400 dark:text-gray-500" />
                    </div>
                    <p class="text-lg text-gray-500 dark:text-gray-400">
                        Nu există categorii disponibile momentan.
                    </p>
                </div>
            </div>
        </main>

        <AppFooter />
    </div>
</template>

