<script setup lang="ts">
import { Separator } from '@/components/ui/separator';
import { useTranslations } from '@/composables/useTranslations';
import CategoryChildren from './CategoryChildren.vue';

interface Category {
    id: number;
    name: string;
    slug: string;
    count?: number;
    children?: Category[];
}

interface Props {
    categories?: Category[];
    activeCategorySlug?: string;
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
}

const props = defineProps<Props>();
const { t } = useTranslations();

const emit = defineEmits<{
    'filter-change': [filters: Record<string, any>];
}>();

const handleCategoryClick = (categorySlug: string) => {
    const filters: Record<string, any> = {
        ...props.filters,
        category: categorySlug === props.filters.category ? undefined : categorySlug,
    };

    // Remove undefined, null, and empty string values
    Object.keys(filters).forEach(key => {
        const value = filters[key];
        if (value === undefined || value === null || value === '' || (typeof value === 'string' && value.trim() === '')) {
            delete filters[key];
        }
    });

    // Emit filter change event
    emit('filter-change', filters);
};
</script>

<template>
    <div v-if="categories && categories.length > 0">
        <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">
            {{ t('categories') }}
        </h3>
        <div class="space-y-2">
            <template v-for="category in categories" :key="category.id">
                <button
                    @click="handleCategoryClick(category.slug)"
                    :class="[
                        'w-full rounded-lg px-3 py-2 text-left text-sm transition-colors',
                        filters.category === category.slug
                            ? 'bg-gray-900 text-white dark:bg-gray-700'
                            : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-white dark:hover:bg-gray-600',
                    ]"
                >
                    <div class="flex items-center justify-between">
                        <span class="dark:text-white">{{ category.name }}</span>
                        <span
                            v-if="category.count !== undefined && category.count !== null"
                            class="text-xs opacity-70 dark:text-white"
                        >
                            ({{ category.count }})
                        </span>
                    </div>
                </button>
                <!-- Nested Subcategories - Recursive -->
                <CategoryChildren
                    v-if="category.children && category.children.length > 0"
                    :categories="category.children"
                    :level="1"
                    :filters="filters"
                    @category-click="handleCategoryClick"
                />
            </template>
        </div>
    </div>
</template>
