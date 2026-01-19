<script setup lang="ts">
import { Separator } from '@/components/ui/separator';
import CategoriesFilter from './CategoriesFilter.vue';
import ProductFilters from './ProductFilters.vue';

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
    priceRange?: {
        min: number;
        max: number;
    };
}

const props = defineProps<Props>();

const emit = defineEmits<{
    'filter-change': [filters: Record<string, any>];
}>();

const handleFilterChange = (newFilters: Record<string, any>) => {
    // Merge the new filters with existing filters and emit
    const mergedFilters: Record<string, any> = {
        ...props.filters,
        ...newFilters,
    };

    // Remove undefined, null, and empty string values
    Object.keys(mergedFilters).forEach(key => {
        const value = mergedFilters[key];
        if (value === undefined || value === null || value === '' || (typeof value === 'string' && value.trim() === '')) {
            delete mergedFilters[key];
        }
    });

    emit('filter-change', mergedFilters);
};
</script>

<template>
    <aside class="space-y-6 mt-6 w-full rounded-lg">
        <!-- Categories Filter -->
        <CategoriesFilter
            v-if="categories && categories.length > 0"
            :categories="categories"
            :active-category-slug="activeCategorySlug"
            :filters="filters"
            @filter-change="handleFilterChange"
        />

        <Separator v-if="categories && categories.length > 0" />

        <!-- Product Filters -->
        <ProductFilters
            :filters="filters"
            :price-range="priceRange"
            @filter-change="handleFilterChange"
        />
    </aside>
</template>

