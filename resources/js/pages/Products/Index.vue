<script setup lang="ts">
import AppFooter from '@/components/AppFooter.vue';
import PublicHeader from '@/components/PublicHeader.vue';
import ProductFiltersSidebar from '@/components/ProductFiltersSidebar.vue';
import ProductCard from '@/components/ProductCard.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import { Filter, X, Loader2 } from 'lucide-vue-next';
import { useAxios } from '@/composables/useAxios';
import { useTranslations } from '@/composables/useTranslations';

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
    count?: number;
    children?: Array<{
        id: number;
        name: string;
        slug: string;
    }>;
}

interface Props {
    products: {
        data: Product[];
        links: any;
        meta: any;
    };
    filters: {
        search?: string;
        category?: string;
        min_price?: string;
        max_price?: string;
        in_stock?: string;
        has_discount?: string;
        is_featured?: string;
        is_new?: string;
        low_stock?: string;
        min_discount?: string;
        max_discount?: string;
        sort_by?: string;
        sort_order?: string;
    };
    categories?: Category[];
    priceRange?: {
        min: number;
        max: number;
    };
}

const props = defineProps<Props>();

const { t } = useTranslations();
const axios = useAxios();
const search = ref(props.filters.search || '');
const sortBy = ref(props.filters.sort_by || 'created_at');
const sortOrder = ref(props.filters.sort_order || 'desc');
const showMobileFilters = ref(false);

// Reactive state for products
const products = ref<{
    data: Product[];
    links: any;
    meta: any;
}>(props.products);
const isLoading = ref(false);
const currentFilters = ref({ ...props.filters });

// Debounce timer
let filterTimeout: ReturnType<typeof setTimeout> | null = null;

// Function to fetch products via API
const fetchProducts = async (filters: Record<string, any>) => {
    isLoading.value = true;
    try {
        // Build query params
        const params = new URLSearchParams();
        Object.keys(filters).forEach((key) => {
            const value = filters[key];
            if (value !== undefined && value !== null && value !== '') {
                params.append(key, String(value));
            }
        });

        const response = await axios.get(`/api/products?${params.toString()}`);
        
        if (response.data && response.data.data) {
            products.value = {
                data: response.data.data,
                links: response.data.links || [],
                meta: response.data.meta || {},
            };
        }
    } catch (error) {
        console.error('Error fetching products:', error);
    } finally {
        isLoading.value = false;
    }
};

// Watch for filter changes and apply with debounce
const applyFilters = (immediate = false) => {
    if (filterTimeout) {
        clearTimeout(filterTimeout);
    }

    const doFilter = () => {
        const filters: Record<string, any> = {
            ...currentFilters.value,
            search: search.value || undefined,
            sort_by: sortBy.value,
            sort_order: sortOrder.value,
        };

        // Remove undefined values
        Object.keys(filters).forEach((key) => {
            if (filters[key] === undefined || filters[key] === '') {
                delete filters[key];
            }
        });

        currentFilters.value = filters;
        fetchProducts(filters);
    };

    if (immediate) {
        doFilter();
    } else {
        filterTimeout = setTimeout(doFilter, 300); // 300ms debounce
    }
};

// Handle filter changes from sidebar
const handleFilterChange = (newFilters: Record<string, any>) => {
    currentFilters.value = { ...newFilters };
    // Merge with current search and sort
    const filters: Record<string, any> = {
        ...newFilters,
        search: search.value || undefined,
        sort_by: sortBy.value,
        sort_order: sortOrder.value,
    };

    // Remove undefined values
    Object.keys(filters).forEach((key) => {
        if (filters[key] === undefined || filters[key] === '') {
            delete filters[key];
        }
    });

    currentFilters.value = filters;
    fetchProducts(filters);
};

// Watch for filter changes from props (initial load)
watch(
    () => props.filters,
    (newFilters) => {
        const newFiltersStr = JSON.stringify(newFilters);
        const currentFiltersStr = JSON.stringify(currentFilters.value);
        if (newFiltersStr !== currentFiltersStr) {
            currentFilters.value = { ...newFilters };
            // Only apply filters if we have actual filter values (not just initial empty state)
            const hasFilters = Object.values(newFilters).some(v => v !== undefined && v !== null && v !== '');
            if (hasFilters) {
                applyFilters(true);
            }
        }
    },
    { deep: true }
);

const handleSearch = () => {
    applyFilters(true);
};

const handleSort = () => {
    applyFilters(true);
};

// Watch search input for real-time filtering
watch(search, () => {
    applyFilters();
});

// Watch sort changes
watch([sortBy, sortOrder], () => {
    applyFilters(true);
});

// Handle pagination
const handlePagination = async (url: string | null) => {
    if (!url) return;
    
    isLoading.value = true;
    try {
        // URL is already in the correct format from API (/api/products?...)
        // Just use it directly
        const response = await axios.get(url);
        
        if (response.data && response.data.data) {
            products.value = {
                data: response.data.data,
                links: response.data.links || [],
                meta: response.data.meta || {},
            };
        }
    } catch (error) {
        console.error('Error fetching paginated products:', error);
    } finally {
        isLoading.value = false;
    }
};
</script>

<template>
    <Head :title="t('products')" />
    <div class="flex min-h-screen flex-col">
        <PublicHeader />

        <main class="flex-1">
            <div class="mx-auto max-w-7xl px-4 py-6 md:px-6">
                <!-- Header -->
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                        {{ t('products') }}
                    </h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">
                        {{ t('explore_all_products') }}
                    </p>
                </div>

                <!-- Mobile Filter Toggle Button -->
                <div class="mb-4 flex items-center justify-between lg:hidden">
                    <button
                        @click="showMobileFilters = !showMobileFilters"
                        class="flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                    >
                        <Filter class="h-4 w-4" />
                        {{ t('filters') }}
                    </button>
                </div>

                <!-- Mobile Filters Overlay -->
                <div
                    v-if="showMobileFilters"
                    class="fixed inset-0 z-50 lg:hidden"
                    @click="showMobileFilters = false"
                >
                    <div class="absolute inset-0 bg-black/50" />
                    <div
                        class="absolute right-0 top-0 h-full w-80 overflow-y-auto bg-white p-6 shadow-xl dark:bg-gray-800"
                        @click.stop
                    >
                        <div class="mb-4 flex items-center justify-between">
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                                {{ t('filters') }}
                            </h2>
                            <button
                                @click="showMobileFilters = false"
                                class="rounded-lg p-2 hover:bg-gray-100 dark:hover:bg-gray-700"
                            >
                                <X class="h-5 w-5" />
                            </button>
                        </div>
                        <ProductFiltersSidebar
                            :categories="categories"
                            :filters="currentFilters"
                            :price-range="priceRange"
                            @filter-change="handleFilterChange"
                        />
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-6 lg:grid-cols-4">
                    <!-- Filters Sidebar - Desktop -->
                    <aside class="hidden lg:block lg:col-span-1">
                        <div class="sticky top-20 rounded-lg border bg-white p-4 shadow-sm dark:bg-gray-800">
                            <ProductFiltersSidebar
                                :categories="categories"
                                :filters="currentFilters"
                                :price-range="priceRange"
                                @filter-change="handleFilterChange"
                            />
                        </div>
                    </aside>

                    <!-- Products Section -->
                    <div class="lg:col-span-3">
                        <!-- Filters -->
                        <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                            <div class="flex flex-1 items-center gap-2">
                                <input
                                    v-model="search"
                                    type="text"
                                    :placeholder="t('search_products')"
                                    class="flex-1 rounded-lg border border-gray-300 px-4 py-2 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                                    @keyup.enter="handleSearch"
                                />
                                <button
                                    @click="handleSearch"
                                    class="rounded-lg bg-gray-900 px-4 py-2 text-white hover:bg-gray-800 dark:bg-gray-700 dark:hover:bg-gray-600"
                                >
                                    {{ t('search_button') }}
                                </button>
                            </div>
                            <div class="flex items-center gap-2">
                                <select
                                    v-model="sortBy"
                                    class="rounded-lg border border-gray-300 px-3 py-2 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                                    @change="handleSort"
                                >
                                    <option value="created_at">{{ t('date_added') }}</option>
                                    <option value="price">{{ t('price') }}</option>
                                    <option value="name">{{ t('name') }}</option>
                                </select>
                                <select
                                    v-model="sortOrder"
                                    class="rounded-lg border border-gray-300 px-3 py-2 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                                    @change="handleSort"
                                >
                                    <option value="desc">{{ t('sort_desc') }}</option>
                                    <option value="asc">{{ t('sort_asc') }}</option>
                                </select>
                            </div>
                        </div>

                        <!-- Loading State -->
                        <div
                            v-if="isLoading"
                            class="flex flex-col items-center justify-center py-12"
                        >
                            <Loader2 class="h-8 w-8 animate-spin text-gray-500" />
                            <p class="mt-4 text-gray-500 dark:text-gray-400">
                                {{ t('loading_products') }}
                            </p>
                        </div>

                        <!-- Products Grid -->
                        <div
                            v-else-if="products.data.length > 0"
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
                                {{ t('no_products_found') }}
                            </p>
                        </div>

                        <!-- Pagination -->
                        <div
                            v-if="products.links && products.links.length > 3 && !isLoading"
                            class="mt-8 flex justify-center"
                        >
                            <nav class="flex gap-2">
                                <button
                                    v-for="link in products.links"
                                    :key="link.label"
                                    @click="link.url && handlePagination(link.url)"
                                    :disabled="!link.url"
                                    :class="[
                                        'rounded-lg px-4 py-2',
                                        link.active
                                            ? 'bg-gray-900 text-white dark:bg-gray-700'
                                            : 'bg-white text-gray-700 hover:bg-gray-100 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700',
                                        !link.url && 'pointer-events-none opacity-50',
                                    ]"
                                >
                                    <span v-html="link.label" />
                                </button>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <AppFooter />
    </div>
</template>

