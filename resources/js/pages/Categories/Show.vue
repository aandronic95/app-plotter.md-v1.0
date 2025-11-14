<script setup lang="ts">
import AppFooter from '@/components/AppFooter.vue';
import PublicHeader from '@/components/PublicHeader.vue';
import CategoriesSidebar from '@/components/CategoriesSidebar.vue';
import ProductCard from '@/components/ProductCard.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

interface Product {
    id: number;
    name: string;
    slug: string;
    price: number;
    originalPrice?: number;
    image: string;
    description?: string;
    discount?: number;
}

interface Category {
    id: number;
    name: string;
    slug: string;
    description?: string;
    image?: string;
    parent?: {
        id: number;
        name: string;
        slug: string;
    };
    children: Array<{
        id: number;
        name: string;
        slug: string;
    }>;
}

interface Props {
    category: Category;
    products: {
        data: Product[];
        links: any;
        meta: any;
    };
    filters: {
        search?: string;
        sort_by?: string;
        sort_order?: string;
    };
    categories?: Array<{
        id: number;
        name: string;
        slug: string;
        count?: number;
    }>;
}

const props = defineProps<Props>();

const search = ref(props.filters.search || '');
const sortBy = ref(props.filters.sort_by || 'created_at');
const sortOrder = ref(props.filters.sort_order || 'desc');

const handleSearch = () => {
    router.get(
        `/categories/${props.category.slug}`,
        {
            search: search.value || undefined,
            sort_by: sortBy.value,
            sort_order: sortOrder.value,
        },
        {
            preserveState: true,
            replace: true,
        },
    );
};

const handleSort = () => {
    router.get(
        `/categories/${props.category.slug}`,
        {
            search: search.value || undefined,
            sort_by: sortBy.value,
            sort_order: sortOrder.value,
        },
        {
            preserveState: true,
            replace: true,
        },
    );
};
</script>

<template>
    <Head :title="category.name" />
    <div class="flex min-h-screen flex-col">
        <PublicHeader />

        <main class="flex-1">
            <div class="mx-auto max-w-7xl px-4 py-6 md:px-6">
                <!-- Breadcrumb -->
                <nav
                    v-if="category.parent"
                    class="mb-6 text-sm"
                >
                    <Link
                        href="/"
                        class="text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100"
                    >
                        Acasă
                    </Link>
                    <span class="mx-2 text-gray-400">/</span>
                    <Link
                        :href="`/categories/${category.parent.slug}`"
                        class="text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100"
                    >
                        {{ category.parent.name }}
                    </Link>
                    <span class="mx-2 text-gray-400">/</span>
                    <span class="text-gray-900 dark:text-white">{{ category.name }}</span>
                </nav>

                <!-- Category Header -->
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                        {{ category.name }}
                    </h1>
                    <p
                        v-if="category.description"
                        class="mt-2 text-gray-600 dark:text-gray-400"
                    >
                        {{ category.description }}
                    </p>
                </div>

                <div class="grid grid-cols-1 gap-6 lg:grid-cols-4">
                    <!-- Categories Sidebar -->
                    <aside class="lg:col-span-1">
                        <CategoriesSidebar
                            :categories="categories"
                            :active-category-slug="category.slug"
                        />
                    </aside>

                    <!-- Products Section -->
                    <div class="lg:col-span-3">
                        <!-- Filters -->
                        <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                            <div class="flex flex-1 items-center gap-2">
                                <input
                                    v-model="search"
                                    type="text"
                                    placeholder="Caută produse..."
                                    class="flex-1 rounded-lg border border-gray-300 px-4 py-2 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                                    @keyup.enter="handleSearch"
                                />
                                <button
                                    @click="handleSearch"
                                    class="rounded-lg bg-gray-900 px-4 py-2 text-white hover:bg-gray-800 dark:bg-gray-700 dark:hover:bg-gray-600"
                                >
                                    Caută
                                </button>
                            </div>
                            <div class="flex items-center gap-2">
                                <select
                                    v-model="sortBy"
                                    class="rounded-lg border border-gray-300 px-3 py-2 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                                    @change="handleSort"
                                >
                                    <option value="created_at">Data adăugării</option>
                                    <option value="price">Preț</option>
                                    <option value="name">Nume</option>
                                </select>
                                <select
                                    v-model="sortOrder"
                                    class="rounded-lg border border-gray-300 px-3 py-2 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                                    @change="handleSort"
                                >
                                    <option value="desc">Descrescător</option>
                                    <option value="asc">Crescător</option>
                                </select>
                            </div>
                        </div>

                        <!-- Products Grid -->
                        <div
                            v-if="products.data.length > 0"
                            class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3"
                        >
                            <ProductCard
                                v-for="product in products.data"
                                :key="product.id"
                                :product="product"
                            />
                        </div>
                        <div
                            v-else
                            class="flex flex-col items-center justify-center py-12"
                        >
                            <p class="text-lg text-gray-500 dark:text-gray-400">
                                Nu există produse în această categorie.
                            </p>
                        </div>

                        <!-- Pagination -->
                        <div
                            v-if="products.links && products.links.length > 3"
                            class="mt-8 flex justify-center"
                        >
                            <nav class="flex gap-2">
                                <Link
                                    v-for="link in products.links"
                                    :key="link.label"
                                    :href="link.url || '#'"
                                    :class="[
                                        'rounded-lg px-4 py-2',
                                        link.active
                                            ? 'bg-gray-900 text-white dark:bg-gray-700'
                                            : 'bg-white text-gray-700 hover:bg-gray-100 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700',
                                        !link.url && 'pointer-events-none opacity-50',
                                    ]"
                                >
                                    <span v-html="link.label" />
                                </Link>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <AppFooter />
    </div>
</template>

