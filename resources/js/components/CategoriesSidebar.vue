<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import {
    ChevronRight,
    ChevronDown,
    Gift,
    Apple,
    Smartphone,
    Laptop,
    Tv,
    Headphones,
    WashingMachine,
    ChefHat,
    Wind,
    Coffee,
    Home,
    Baby,
    Car,
    type LucideIcon,
} from 'lucide-vue-next';
import { computed, ref, onMounted, watch } from 'vue';

interface CategoryChild {
    id: number;
    name: string;
    slug: string;
    icon?: string;
}

interface Category {
    id: number;
    name: string;
    slug: string;
    icon?: string;
    count?: number;
    children?: CategoryChild[];
}

const props = withDefaults(
    defineProps<{
        categories?: Category[];
        activeCategorySlug?: string;
    }>(),
    {
        categories: () => [],
        activeCategorySlug: undefined,
    },
);

const page = usePage();
const expandedCategories = ref<Set<number>>(new Set());

// Map icon names to Lucide icons
const iconMap: Record<string, LucideIcon> = {
    gift: Gift,
    apple: Apple,
    smartphone: Smartphone,
    phone: Smartphone,
    laptop: Laptop,
    tv: Tv,
    television: Tv,
    headphones: Headphones,
    washingmachine: WashingMachine,
    oven: ChefHat,
    wind: Wind,
    fan: Wind,
    coffee: Coffee,
    kettle: Coffee,
    home: Home,
    house: Home,
    baby: Baby,
    car: Car,
};

// Get icon component or default
const getIcon = (iconName?: string): LucideIcon => {
    if (!iconName) return Gift;
    const icon = iconMap[iconName.toLowerCase()];
    return icon || Gift;
};

// Detect active category from URL
const currentSlug = computed(() => {
    if (props.activeCategorySlug) {
        return props.activeCategorySlug;
    }
    const url = page.url;
    const match = url.match(/\/categories\/([^\/]+)/);
    return match ? match[1] : null;
});

const isActive = (slug: string) => {
    return currentSlug.value === slug;
};

const isExpanded = (categoryId: number) => {
    return expandedCategories.value.has(categoryId);
};

const toggleExpand = (categoryId: number, event: Event) => {
    event.preventDefault();
    event.stopPropagation();
    if (expandedCategories.value.has(categoryId)) {
        expandedCategories.value.delete(categoryId);
    } else {
        expandedCategories.value.add(categoryId);
    }
};

// Auto-expand categories that have active children
const hasActiveChild = (category: Category) => {
    if (!category.children || category.children.length === 0) {
        return false;
    }
    return category.children.some((child) => isActive(child.slug));
};

// Auto-expand categories with active children
const expandActiveCategories = () => {
    props.categories.forEach((category) => {
        if (hasActiveChild(category) || isActive(category.slug)) {
            expandedCategories.value.add(category.id);
        }
    });
};

// Auto-expand on mount and when categories or active slug changes
onMounted(() => {
    expandActiveCategories();
});

watch(
    () => [props.categories, currentSlug.value],
    () => {
        expandActiveCategories();
    },
    { deep: true },
);
</script>

<template>
    <div class="w-full bg-white dark:bg-gray-800">
        <nav class="space-y-0">
            <div
                v-for="category in categories"
                :key="category.id"
                class="w-full"
            >
                <div class="relative">
                    <Link
                        :href="`/categories/${category.slug}`"
                        :class="[
                            'flex w-full items-center justify-between px-4 py-3 text-sm font-medium transition-colors',
                            isActive(category.slug)
                                ? 'bg-gray-200 text-gray-900 dark:bg-gray-700 dark:text-white'
                                : 'text-gray-900 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-700',
                        ]"
                    >
                        <div class="flex items-center gap-3 flex-1">
                            <component
                                :is="getIcon(category.icon)"
                                class="h-5 w-5 flex-shrink-0"
                                :class="[
                                    isActive(category.slug)
                                        ? 'text-gray-900 dark:text-white'
                                        : 'text-cyan-500 dark:text-cyan-400',
                                ]"
                            />
                            <span class="text-sm font-medium">{{ category.name }}</span>
                        </div>
                        <div class="flex items-center gap-1">
                            <button
                                v-if="category.children && category.children.length > 0"
                                @click="toggleExpand(category.id, $event)"
                                class="p-1 hover:bg-gray-200 dark:hover:bg-gray-600 rounded transition-colors"
                                :aria-expanded="isExpanded(category.id)"
                            >
                                <ChevronDown
                                    v-if="isExpanded(category.id)"
                                    class="h-4 w-4 text-gray-400"
                                />
                                <ChevronRight
                                    v-else
                                    class="h-4 w-4 text-gray-400"
                                />
                            </button>
                            <ChevronRight
                                v-else
                                class="h-4 w-4 flex-shrink-0 text-gray-400"
                            />
                        </div>
                    </Link>
                </div>
                <!-- Subcategories -->
                <div
                    v-if="category.children && category.children.length > 0 && isExpanded(category.id)"
                    class="bg-gray-50 dark:bg-gray-900/50"
                >
                    <Link
                        v-for="child in category.children"
                        :key="child.id"
                        :href="`/categories/${child.slug}`"
                        :class="[
                            'flex w-full items-center gap-3 px-4 py-2 pl-12 text-sm font-medium transition-colors',
                            isActive(child.slug)
                                ? 'bg-gray-200 text-gray-900 dark:bg-gray-700 dark:text-white'
                                : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700',
                        ]"
                    >
                        <span class="text-sm">{{ child.name }}</span>
                    </Link>
                </div>
            </div>
        </nav>
    </div>
</template>

