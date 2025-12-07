<script setup lang="ts">
import AppLogo from '@/components/AppLogo.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import {
    Sheet,
    SheetContent,
    SheetHeader,
    SheetTitle,
    SheetTrigger,
} from '@/components/ui/sheet';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSub,
    DropdownMenuSubContent,
    DropdownMenuSubTrigger,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { home, register, dashboard, login, logout } from '@/routes';
import { Link, router, usePage } from '@inertiajs/vue3';
import { Menu, Search, ShoppingCart, User, Package, FileText, List, ChevronDown, ChevronRight, Sun, Moon } from 'lucide-vue-next';
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';
import { useSiteSettings } from '@/composables/useSiteSettings';
import { useAppearance } from '@/composables/useAppearance';
import { useTranslations } from '@/composables/useTranslations';
import { useApiCache } from '@/composables/useApiCache';
import { useCacheManager } from '@/composables/useCacheManager';
import LanguageSwitcher from '@/components/LanguageSwitcher.vue';

interface NavigationItem {
    id: number;
    title: string;
    href: string;
    is_external?: boolean;
    target?: string;
}

interface SearchProduct {
    id: number;
    name: string;
    slug: string;
    price: number;
    original_price?: number | null;
    image: string;
    description?: string;
    discount?: number;
}

interface SearchPage {
    id: number;
    title: string;
    slug: string;
    excerpt?: string;
}

interface CategoryGroup {
    name: string;
    items: NavigationItem[];
}

const page = usePage();
const auth = computed(() => page.props.auth);
const cartCount = ref(0);
const menuItems = ref<NavigationItem[]>([]);
const isLoading = ref(true);
const categories = ref<CategoryGroup[]>([]);
const isCategoriesLoading = ref(false);
const expandedCategories = ref<Set<string>>(new Set());
const { siteSettings, fetchSiteSettings } = useSiteSettings();
const { appearance, updateAppearance } = useAppearance();
const { t } = useTranslations();
const cacheManager = useCacheManager();

// Dark mode toggle
const isDark = computed(() => {
    if (typeof window === 'undefined') {
        return false;
    }
    if (appearance.value === 'system') {
        return window.matchMedia('(prefers-color-scheme: dark)').matches;
    }
    return appearance.value === 'dark';
});

const toggleDarkMode = () => {
    const newMode = isDark.value ? 'light' : 'dark';
    updateAppearance(newMode);
};

// Search functionality
const isSearchOpen = ref(false);
const searchQuery = ref('');
const searchInputRef = ref<HTMLInputElement | null>(null);
const searchResults = ref<{
    products: SearchProduct[];
    pages: SearchPage[];
}>({
    products: [],
    pages: [],
});
const isSearching = ref(false);
let searchTimeout: ReturnType<typeof setTimeout> | null = null;

// Fallback menu items dacă API-ul eșuează
const fallbackMenuItems = computed<NavigationItem[]>(() => [
    { id: 1, title: t('home'), href: '/', is_external: false, target: '_self' },
    { id: 2, title: t('products'), href: '/products', is_external: false, target: '_self' },
    { id: 3, title: t('categories_nav'), href: '/categories', is_external: false, target: '_self' },
    { id: 4, title: t('about_us_nav'), href: '/about', is_external: false, target: '_self' },
    { id: 5, title: t('contact_nav'), href: '/contact', is_external: false, target: '_self' },
]);

const apiCache = useApiCache();

// Fetch navigation items from API with cache
const fetchNavigationItems = async () => {
    try {
        isLoading.value = true;
        
        const data = await apiCache.fetchWithCache<{ data: NavigationItem[] }>(
            '/api/navigations?group=header',
            {
                key: 'nav_header_api_cache',
                ttl: 2 * 60 * 60 * 1000, // 2 hours
                version: '1.0',
            }
        );
        
        console.log('Navigation API response:', data);
        
        if (data.data && Array.isArray(data.data) && data.data.length > 0) {
            menuItems.value = data.data;
            console.log('Navigation items loaded:', menuItems.value.length);
        } else {
            // Dacă nu există elemente în baza de date, folosește fallback
            console.warn('No navigation items found, using fallback');
            menuItems.value = fallbackMenuItems.value;
        }
    } catch (error) {
        console.error('Error fetching navigation items:', error);
        // Try to load from cache as fallback
        const cached = apiCache.loadFromCache<{ data: NavigationItem[] }>({
            key: 'nav_header_api_cache',
            ttl: 2 * 60 * 60 * 1000,
        });
        
        if (cached?.data && Array.isArray(cached.data) && cached.data.length > 0) {
            menuItems.value = cached.data;
        } else {
            // Folosește fallback dacă API-ul eșuează
            menuItems.value = fallbackMenuItems.value;
        }
    } finally {
        isLoading.value = false;
    }
};

const fetchCartCount = async () => {
    try {
        const response = await fetch('/cart/data');
        const data = await response.json();
        cartCount.value = data.count || 0;
    } catch (error) {
        console.error('Error fetching cart count:', error);
    }
};

// Fetch categories from API with cache
const fetchCategories = async () => {
    try {
        isCategoriesLoading.value = true;
        
        const data = await apiCache.fetchWithCache<{ data: CategoryGroup[] }>(
            '/api/navigations/categories?group=header',
            {
                key: 'nav_categories_header_api_cache',
                ttl: 2 * 60 * 60 * 1000, // 2 hours
                version: '1.0',
            }
        );
        
        if (data.data && data.data.length > 0) {
            categories.value = data.data;
        }
    } catch (error) {
        console.error('Error fetching categories:', error);
        // Try to load from cache as fallback
        const cached = apiCache.loadFromCache<{ data: CategoryGroup[] }>({
            key: 'nav_categories_header_api_cache',
            ttl: 2 * 60 * 60 * 1000,
        });
        
        if (cached?.data && Array.isArray(cached.data) && cached.data.length > 0) {
            categories.value = cached.data;
        } else {
            categories.value = [];
        }
    } finally {
        isCategoriesLoading.value = false;
    }
};

// Toggle category expand/collapse
const toggleCategory = (categoryName: string) => {
    if (expandedCategories.value.has(categoryName)) {
        expandedCategories.value.delete(categoryName);
    } else {
        expandedCategories.value.add(categoryName);
    }
};

// Check if category is expanded
const isCategoryExpanded = (categoryName: string) => {
    return expandedCategories.value.has(categoryName);
};

const handleCartUpdate = () => {
    fetchCartCount();
};

const performSearch = async (query: string) => {
    if (!query.trim() || query.length < 2) {
        searchResults.value = { products: [], pages: [] };
        return;
    }

    isSearching.value = true;

    try {
        const [productsResponse, pagesResponse] = await Promise.all([
            fetch(`/api/products?search=${encodeURIComponent(query)}&limit=5`),
            fetch(`/api/pages?search=${encodeURIComponent(query)}&active_only=true&limit=5`),
        ]);

        const productsData = await productsResponse.json();
        const pagesData = await pagesResponse.json();

        searchResults.value = {
            products: productsData.data || [],
            pages: pagesData.data || [],
        };
    } catch (error) {
        console.error('Error performing search:', error);
        searchResults.value = { products: [], pages: [] };
    } finally {
        isSearching.value = false;
    }
};

const handleSearchInput = (value: string | number) => {
    const query = String(value);
    searchQuery.value = query;

    if (searchTimeout) {
        clearTimeout(searchTimeout);
    }

    if (query.trim().length >= 2) {
        searchTimeout = setTimeout(() => {
            performSearch(query);
        }, 300);
    } else {
        searchResults.value = { products: [], pages: [] };
    }
};

const handleSearchResultClick = (type: 'product' | 'page', slug: string) => {
    isSearchOpen.value = false;
    searchQuery.value = '';
    searchResults.value = { products: [], pages: [] };
    
    if (type === 'product') {
        router.visit(`/products/${slug}`);
    } else {
        router.visit(`/${slug}`);
    }
};

const handleViewAllResults = () => {
    isSearchOpen.value = false;
    const query = searchQuery.value;
    searchQuery.value = '';
    searchResults.value = { products: [], pages: [] };
    router.visit(`/products?search=${encodeURIComponent(query)}`);
};

watch(isSearchOpen, (open) => {
    if (!open) {
        searchQuery.value = '';
        searchResults.value = { products: [], pages: [] };
        if (searchTimeout) {
            clearTimeout(searchTimeout);
        }
    } else {
        // Focus input when dialog opens
        setTimeout(() => {
            searchInputRef.value?.focus();
        }, 100);
    }
});

onMounted(async () => {
    // Batch all initial requests in parallel
    await Promise.all([
        fetchCartCount(),
        fetchNavigationItems(),
        fetchCategories(),
        fetchSiteSettings(),
    ]);
    window.addEventListener('cart-updated', handleCartUpdate);
});

onUnmounted(() => {
    window.removeEventListener('cart-updated', handleCartUpdate);
    if (searchTimeout) {
        clearTimeout(searchTimeout);
    }
});
</script>

<template>
    <header class="sticky top-0 z-50 border-b border-gray-200/80 bg-white/80 backdrop-blur-md shadow-sm transition-all duration-300 dark:border-gray-700/80 dark:bg-gray-900/80">
        <div class="mx-auto max-w-7xl px-4 md:px-6">
            <div class="flex h-16 items-center justify-between">
                <!-- Logo -->
                <Link :href="home()" class="group flex items-center gap-2 transition-transform duration-200 hover:scale-105">
                    <div
                        v-if="siteSettings?.site_logo && (siteSettings?.show_logo ?? true)"
                        class="flex h-10 w-auto items-center transition-opacity duration-200 group-hover:opacity-90"
                    >
                        <img
                            :src="siteSettings.site_logo"
                            :alt="siteSettings.site_name || 'Logo'"
                            class="h-full w-auto object-contain"
                        />
                    </div>
                    <AppLogo v-else-if="siteSettings?.show_logo ?? true" />
                    <span
                        v-if="siteSettings?.site_name && (siteSettings?.show_site_name ?? true)"
                        class="hidden bg-gradient-to-r from-gray-900 to-gray-700 bg-clip-text text-lg font-bold text-transparent transition-all duration-200 group-hover:from-primary group-hover:to-primary/80 dark:from-white dark:to-gray-300 sm:block"
                    >
                        {{ siteSettings.site_name }}
                    </span>
                </Link>

                <!-- Categories Dropdown -->
                <div class="hidden md:block">
                    <DropdownMenu v-if="!isCategoriesLoading && categories.length > 0">
                        <DropdownMenuTrigger as-child>
                            <Button variant="ghost" size="sm" class="h-9 transition-all duration-200 hover:bg-primary/10 hover:text-primary">
                                <List class="mr-2 h-4 w-4 transition-transform duration-200 group-hover:rotate-12" />
                                {{ t('categories') }}
                                <ChevronDown class="ml-2 h-4 w-4 transition-transform duration-200" />
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="start" class="w-56 max-h-[80vh] overflow-y-auto border-gray-200/50 bg-white/95 backdrop-blur-sm dark:border-gray-700/50 dark:bg-gray-900/95">
                            <template v-for="category in categories" :key="category.name">
                                <DropdownMenuSub v-if="category.items && category.items.length > 0">
                                    <DropdownMenuSubTrigger class="transition-colors duration-150 hover:bg-primary/5">
                                        {{ category.name }}
                                    </DropdownMenuSubTrigger>
                                    <DropdownMenuSubContent class="border-gray-200/50 bg-white/95 backdrop-blur-sm dark:border-gray-700/50 dark:bg-gray-900/95">
                                        <DropdownMenuItem
                                            v-for="item in category.items"
                                            :key="item.id"
                                            as-child
                                            class="transition-colors duration-150"
                                        >
                                            <Link
                                                :href="item.href"
                                                :target="item.is_external ? (item.target || '_blank') : '_self'"
                                                :rel="item.is_external ? 'noopener noreferrer' : undefined"
                                                class="transition-colors duration-150 hover:bg-primary/5 hover:text-primary"
                                            >
                                                {{ item.title }}
                                            </Link>
                                        </DropdownMenuItem>
                                    </DropdownMenuSubContent>
                                </DropdownMenuSub>
                                <template v-else>
                                    <DropdownMenuItem disabled class="opacity-50">
                                        {{ category.name }}
                                    </DropdownMenuItem>
                                </template>
                            </template>
                        </DropdownMenuContent>
                    </DropdownMenu>
                </div>

                <!-- Desktop Navigation -->
                <nav v-if="!isLoading && menuItems.length > 0" class="hidden items-center gap-1 md:flex">
                    <Link
                        v-for="item in menuItems"
                        :key="item.id"
                        :href="item.href"
                        :target="item.is_external ? (item.target || '_blank') : '_self'"
                        :rel="item.is_external ? 'noopener noreferrer' : undefined"
                        class="group relative rounded-md px-3 py-2 text-sm font-medium text-gray-700 transition-all duration-200 hover:bg-primary/5 hover:text-primary dark:text-gray-300 dark:hover:text-white"
                    >
                        <span class="relative z-10">{{ item.title }}</span>
                        <span class="absolute inset-x-0 bottom-0 h-0.5 origin-left scale-x-0 bg-gradient-to-r from-primary to-primary/50 transition-transform duration-200 group-hover:scale-x-100"></span>
                    </Link>
                </nav>
                <div v-else-if="isLoading" class="hidden items-center gap-6 md:flex">
                    <div class="h-4 w-20 animate-pulse rounded-md bg-gradient-to-r from-gray-200 via-gray-100 to-gray-200 dark:from-gray-700 dark:via-gray-600 dark:to-gray-700"></div>
                    <div class="h-4 w-20 animate-pulse rounded-md bg-gradient-to-r from-gray-200 via-gray-100 to-gray-200 dark:from-gray-700 dark:via-gray-600 dark:to-gray-700"></div>
                    <div class="h-4 w-20 animate-pulse rounded-md bg-gradient-to-r from-gray-200 via-gray-100 to-gray-200 dark:from-gray-700 dark:via-gray-600 dark:to-gray-700"></div>
                </div>
                <div v-else-if="menuItems.length === 0" class="hidden items-center gap-6 md:flex">
                    <span class="text-sm text-gray-500">{{ t('no_navigation_items') }}</span>
                </div>

                <!-- Right Side Actions -->
                <div class="flex items-center gap-2">
                    <!-- Search Dialog -->
                    <Dialog :open="isSearchOpen" @update:open="isSearchOpen = $event">
                        <DialogTrigger :as-child="true">
                            <Button variant="ghost" size="icon" class="group relative h-9 w-9 transition-all duration-200 hover:bg-primary/10 hover:text-primary">
                                <Search class="h-5 w-5 transition-transform duration-200 group-hover:scale-110" />
                            </Button>
                        </DialogTrigger>
                        <DialogContent class="max-w-2xl border-gray-200/50 bg-white/95 backdrop-blur-md dark:border-gray-700/50 dark:bg-gray-900/95">
                            <DialogHeader>
                                <DialogTitle class="text-xl font-semibold">{{ t('search_title') }}</DialogTitle>
                            </DialogHeader>
                            <div class="space-y-4">
                                <!-- Search Input -->
                                <div class="relative">
                                    <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400 transition-colors duration-200 group-focus-within:text-primary" />
                                    <Input
                                        ref="searchInputRef"
                                        :model-value="searchQuery"
                                        @update:model-value="handleSearchInput"
                                        :placeholder="t('search_placeholder')"
                                        class="group h-12 border-2 border-gray-200 pl-10 transition-all duration-200 focus:border-primary focus:ring-2 focus:ring-primary/20 dark:border-gray-700"
                                    />
                                </div>

                                <!-- Search Results -->
                                <div v-if="isSearching" class="flex items-center justify-center py-12">
                                    <div class="relative">
                                        <div class="h-8 w-8 animate-spin rounded-full border-[3px] border-gray-200 border-t-primary"></div>
                                        <div class="absolute inset-0 h-8 w-8 animate-ping rounded-full border-2 border-primary opacity-20"></div>
                                    </div>
                                </div>

                                <div v-else-if="searchQuery.length >= 2 && (searchResults.products.length > 0 || searchResults.pages.length > 0)" class="max-h-96 space-y-4 overflow-y-auto">
                                    <!-- Products Results -->
                                    <div v-if="searchResults.products.length > 0">
                                        <h3 class="mb-3 flex items-center gap-2 text-sm font-semibold text-gray-700 dark:text-gray-300">
                                            <Package class="h-4 w-4 text-primary" />
                                            {{ t('products_count') }} ({{ searchResults.products.length }})
                                        </h3>
                                        <div class="space-y-2">
                                            <button
                                                v-for="product in searchResults.products"
                                                :key="product.id"
                                                @click="handleSearchResultClick('product', product.slug)"
                                                class="group flex w-full items-center gap-3 rounded-lg border border-gray-200 bg-white p-3 text-left transition-all duration-200 hover:border-primary/50 hover:bg-primary/5 hover:shadow-md dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700"
                                            >
                                                <div class="relative overflow-hidden rounded-md">
                                                    <img
                                                        :src="product.image"
                                                        :alt="product.name"
                                                        class="h-14 w-14 object-cover transition-transform duration-200 group-hover:scale-110"
                                                    />
                                                    <div v-if="product.discount" class="absolute top-0 right-0 rounded-bl-md bg-red-500 px-1.5 py-0.5 text-xs font-bold text-white">
                                                        -{{ product.discount }}%
                                                    </div>
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <p class="truncate font-medium text-gray-900 transition-colors duration-200 group-hover:text-primary dark:text-white">
                                                        {{ product.name }}
                                                    </p>
                                                    <p v-if="product.description" class="mt-0.5 line-clamp-1 text-xs text-gray-500 dark:text-gray-400">
                                                        {{ product.description }}
                                                    </p>
                                                    <div class="mt-1.5 flex items-center gap-2">
                                                        <p class="text-sm font-bold text-primary">
                                                            {{ product.price.toFixed(2) }} RON
                                                        </p>
                                                        <span
                                                            v-if="product.original_price"
                                                            class="text-xs text-gray-400 line-through"
                                                        >
                                                            {{ product.original_price.toFixed(2) }} RON
                                                        </span>
                                                    </div>
                                                </div>
                                                <Package class="h-5 w-5 flex-shrink-0 text-gray-400 transition-colors duration-200 group-hover:text-primary" />
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Pages Results -->
                                    <div v-if="searchResults.pages.length > 0">
                                        <h3 class="mb-3 flex items-center gap-2 text-sm font-semibold text-gray-700 dark:text-gray-300">
                                            <FileText class="h-4 w-4 text-primary" />
                                            {{ t('pages_count') }} ({{ searchResults.pages.length }})
                                        </h3>
                                        <div class="space-y-2">
                                            <button
                                                v-for="page in searchResults.pages"
                                                :key="page.id"
                                                @click="handleSearchResultClick('page', page.slug)"
                                                class="group flex w-full items-center gap-3 rounded-lg border border-gray-200 bg-white p-3 text-left transition-all duration-200 hover:border-primary/50 hover:bg-primary/5 hover:shadow-md dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700"
                                            >
                                                <div class="flex h-14 w-14 flex-shrink-0 items-center justify-center rounded-md bg-gradient-to-br from-primary/10 to-primary/5 transition-all duration-200 group-hover:from-primary/20 group-hover:to-primary/10 dark:from-primary/20 dark:to-primary/10">
                                                    <FileText class="h-6 w-6 text-primary transition-transform duration-200 group-hover:scale-110" />
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <p class="font-medium text-gray-900 transition-colors duration-200 group-hover:text-primary dark:text-white">
                                                        {{ page.title }}
                                                    </p>
                                                    <p
                                                        v-if="page.excerpt"
                                                        class="mt-1 line-clamp-2 text-xs text-gray-500 dark:text-gray-400"
                                                    >
                                                        {{ page.excerpt }}
                                                    </p>
                                                </div>
                                            </button>
                                        </div>
                                    </div>

                                    <!-- View All Results -->
                                    <div class="border-t border-gray-200 pt-4 dark:border-gray-700">
                                        <Button
                                            @click="handleViewAllResults"
                                            variant="outline"
                                            class="w-full border-2 transition-all duration-200 hover:border-primary hover:bg-primary hover:text-white"
                                        >
                                            {{ t('view_all_results') }} "{{ searchQuery }}"
                                        </Button>
                                    </div>
                                </div>

                                <div
                                    v-else-if="searchQuery.length >= 2 && !isSearching"
                                    class="flex flex-col items-center justify-center py-12 text-center"
                                >
                                    <div class="mb-3 flex h-16 w-16 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800">
                                        <Search class="h-8 w-8 text-gray-400" />
                                    </div>
                                    <p class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                        {{ t('no_search_results') }}
                                    </p>
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                        "{{ searchQuery }}"
                                    </p>
                                </div>

                                <div
                                    v-else-if="searchQuery.length < 2"
                                    class="flex flex-col items-center justify-center py-12 text-center"
                                >
                                    <div class="mb-3 flex h-16 w-16 items-center justify-center rounded-full bg-primary/10">
                                        <Search class="h-8 w-8 text-primary" />
                                    </div>
                                    <p class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                        {{ t('search_min_chars') }}
                                    </p>
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                        {{ t('search_min_chars_desc') }}
                                    </p>
                                </div>
                            </div>
                        </DialogContent>
                    </Dialog>
                    
                    <!-- Dark Mode Toggle -->
                    <Button
                        variant="ghost"
                        size="icon"
                        @click="toggleDarkMode"
                        class="group relative h-9 w-9 transition-all duration-200 hover:bg-primary/10 hover:text-primary"
                        :title="isDark ? t('toggle_light_mode') : t('toggle_dark_mode')"
                    >
                        <Sun
                            v-if="isDark"
                            class="h-5 w-5 transition-all duration-300 group-hover:rotate-180"
                        />
                        <Moon
                            v-else
                            class="h-5 w-5 transition-all duration-300 group-hover:-rotate-12"
                        />
                    </Button>
                    
                    <!-- Language Switcher -->
                    <LanguageSwitcher />
                    
                    <Link href="/cart">
                        <Button
                            variant="ghost"
                            size="icon"
                            class="group relative h-9 w-9 transition-all duration-200 hover:bg-primary/10 hover:text-primary"
                        >
                            <ShoppingCart class="h-5 w-5 transition-transform duration-200 group-hover:scale-110" />
                            <span
                                v-if="cartCount > 0"
                                class="absolute -right-1 -top-1 flex h-5 w-5 items-center justify-center rounded-full bg-gradient-to-br from-red-500 to-red-600 text-xs font-bold text-white shadow-lg ring-2 ring-white transition-all duration-300 dark:ring-gray-900"
                            >
                                {{ cartCount > 99 ? '99+' : cartCount }}
                            </span>
                        </Button>
                    </Link>

                    <!-- Mobile Menu -->
                    <div class="md:hidden">
                        <Sheet>
                            <SheetTrigger :as-child="true">
                                <Button variant="ghost" size="icon" class="group h-9 w-9 transition-all duration-200 hover:bg-primary/10 hover:text-primary">
                                    <Menu class="h-5 w-5 transition-transform duration-200 group-hover:rotate-90" />
                                </Button>
                            </SheetTrigger>
                            <SheetContent side="right" class="w-[300px] border-gray-200/50 bg-white/95 backdrop-blur-md dark:border-gray-700/50 dark:bg-gray-900/95">
                                <SheetHeader>
                                    <SheetTitle class="text-xl font-semibold">{{ t('menu') }}</SheetTitle>
                                </SheetHeader>
                                <!-- Categories Section -->
                                <div v-if="!isCategoriesLoading && categories.length > 0" class="mt-6 space-y-1 border-b border-gray-200 pb-4 dark:border-gray-700">
                                    <h3 class="mb-3 flex items-center gap-2 text-sm font-semibold text-gray-900 dark:text-white">
                                        <List class="h-4 w-4 text-primary" />
                                        {{ t('categories') }}
                                    </h3>
                                    <div
                                        v-for="category in categories"
                                        :key="category.name"
                                        class="border-b border-gray-100 last:border-0 dark:border-gray-700"
                                    >
                                        <button
                                            @click="toggleCategory(category.name)"
                                            class="flex w-full items-center justify-between rounded-md px-3 py-3 text-left text-sm font-semibold text-gray-900 transition-all duration-200 hover:bg-primary/5 hover:text-primary dark:text-white dark:hover:bg-gray-800"
                                        >
                                            <div class="flex items-center gap-2">
                                                <component
                                                    :is="isCategoryExpanded(category.name) ? ChevronDown : ChevronRight"
                                                    class="h-4 w-4 text-gray-500 transition-transform duration-200"
                                                    :class="{ 'rotate-90': isCategoryExpanded(category.name) }"
                                                />
                                                <span>{{ category.name }}</span>
                                            </div>
                                        </button>
                                        <nav
                                            v-show="isCategoryExpanded(category.name)"
                                            class="space-y-1 pb-2 pl-7 transition-all duration-200"
                                        >
                                            <Link
                                                v-for="item in category.items"
                                                :key="item.id"
                                                :href="item.href"
                                                :target="item.is_external ? (item.target || '_blank') : '_self'"
                                                :rel="item.is_external ? 'noopener noreferrer' : undefined"
                                                class="block rounded-md px-3 py-2 text-sm text-gray-700 transition-all duration-200 hover:bg-primary/5 hover:text-primary hover:translate-x-1 dark:text-gray-300 dark:hover:text-white"
                                            >
                                                {{ item.title }}
                                            </Link>
                                        </nav>
                                    </div>
                                </div>
                                <!-- Navigation Items -->
                                <nav v-if="!isLoading && menuItems.length > 0" class="mt-6 space-y-2">
                                    <Link
                                        v-for="item in menuItems"
                                        :key="item.id"
                                        :href="item.href"
                                        :target="item.is_external ? (item.target || '_blank') : '_self'"
                                        :rel="item.is_external ? 'noopener noreferrer' : undefined"
                                        class="block rounded-md px-3 py-2.5 text-sm font-medium text-gray-700 transition-all duration-200 hover:bg-primary/5 hover:text-primary hover:translate-x-1 dark:text-gray-300 dark:hover:text-white"
                                    >
                                        {{ item.title }}
                                    </Link>
                                </nav>
                                <div v-else-if="isLoading" class="mt-6 space-y-3">
                                    <div class="h-4 w-full animate-pulse rounded-md bg-gradient-to-r from-gray-200 via-gray-100 to-gray-200 dark:from-gray-700 dark:via-gray-600 dark:to-gray-700"></div>
                                    <div class="h-4 w-full animate-pulse rounded-md bg-gradient-to-r from-gray-200 via-gray-100 to-gray-200 dark:from-gray-700 dark:via-gray-600 dark:to-gray-700"></div>
                                    <div class="h-4 w-full animate-pulse rounded-md bg-gradient-to-r from-gray-200 via-gray-100 to-gray-200 dark:from-gray-700 dark:via-gray-600 dark:to-gray-700"></div>
                                </div>
                                <div v-else-if="menuItems.length === 0" class="mt-6">
                                    <p class="text-sm text-gray-500">Nu există elemente de navigare</p>
                                </div>
                                
                                <!-- Dark Mode Toggle Mobile -->
                                <div class="mt-6 border-t border-gray-200 pt-4 dark:border-gray-700">
                                    <Button
                                        @click="toggleDarkMode"
                                        variant="outline"
                                        class="w-full transition-all duration-200 hover:scale-[1.02]"
                                    >
                                        <Sun
                                            v-if="isDark"
                                            class="mr-2 h-4 w-4 transition-all duration-300"
                                        />
                                        <Moon
                                            v-else
                                            class="mr-2 h-4 w-4 transition-all duration-300"
                                        />
                                        {{ isDark ? t('light_mode') : t('dark_mode') }}
                                    </Button>
                                </div>
                                
                                <div class="mt-6 space-y-2 border-t border-gray-200 pt-4 dark:border-gray-700">
                                    <template v-if="auth.user">
                                        <Link href="/profile">
                                            <Button class="w-full transition-all duration-200 hover:scale-[1.02]" variant="outline">
                                                <User class="mr-2 h-4 w-4" />
                                                {{ t('my_profile') }}
                                            </Button>
                                        </Link>
                                        <form @submit.prevent="() => { cacheManager.invalidateOnAuthChange(); router.post(logout().url); }">
                                            <Button type="submit" class="w-full transition-all duration-200 hover:scale-[1.02]" variant="ghost">
                                                {{ t('logout') }}
                                            </Button>
                                        </form>
                                    </template>
                                    <template v-else>
                                        <Link :href="login().url">
                                            <Button class="w-full transition-all duration-200 hover:scale-[1.02]" variant="outline">
                                                {{ t('login') }}
                                            </Button>
                                        </Link>
                                        <Link :href="register()">
                                            <Button class="w-full transition-all duration-200 hover:scale-[1.02]">{{ t('register') }}</Button>
                                        </Link>
                                    </template>
                                </div>
                            </SheetContent>
                        </Sheet>
                    </div>

                    <!-- Desktop Auth Buttons -->
                    <div class="hidden items-center gap-2 md:flex">
                        <template v-if="auth.user">
                            <Link href="/profile">
                                <Button variant="outline" size="sm" class="transition-all duration-200 hover:scale-105 hover:border-primary hover:text-primary">
                                    <User class="mr-2 h-4 w-4" />
                                    {{ t('my_profile') }}
                                </Button>
                            </Link>
                            <form @submit.prevent="() => { cacheManager.invalidateOnAuthChange(); router.post(logout().url); }" class="inline">
                                <Button type="submit" variant="ghost" size="sm" class="transition-all duration-200 hover:bg-primary/10 hover:text-primary">
                                    {{ t('logout') }}
                                </Button>
                            </form>
                        </template>
                        <template v-else>
                            <Link :href="login().url">
                                <Button variant="ghost" size="sm" class="transition-all duration-200 hover:bg-primary/10 hover:text-primary">
                                    {{ t('login') }}
                                </Button>
                            </Link>
                            <Link :href="register()">
                                <Button size="sm" class="bg-gradient-to-r from-primary to-primary/80 transition-all duration-200 hover:scale-105 hover:from-primary/90 hover:to-primary/70 hover:shadow-lg">
                                    {{ t('register') }}
                                </Button>
                            </Link>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </header>
</template>

