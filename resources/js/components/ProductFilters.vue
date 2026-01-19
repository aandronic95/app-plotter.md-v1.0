<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import { Checkbox } from '@/components/ui/checkbox';
import { Separator } from '@/components/ui/separator';
import { X } from 'lucide-vue-next';
import { ref, computed, watch } from 'vue';
import { useTranslations } from '@/composables/useTranslations';

interface Props {
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
const { t } = useTranslations();

const emit = defineEmits<{
    'filter-change': [filters: Record<string, any>];
}>();

const minPrice = ref(props.filters.min_price || '');
const maxPrice = ref(props.filters.max_price || '');
const inStock = ref(props.filters.in_stock === '1' || props.filters.in_stock === 'true');
const hasDiscount = ref(props.filters.has_discount === '1' || props.filters.has_discount === 'true');
const isFeatured = ref(props.filters.is_featured === '1' || props.filters.is_featured === 'true');
const isNew = ref(props.filters.is_new === '1' || props.filters.is_new === 'true');
const lowStock = ref(props.filters.low_stock === '1' || props.filters.low_stock === 'true');
const minDiscount = ref(props.filters.min_discount || '');
const maxDiscount = ref(props.filters.max_discount || '');

// Watch for prop changes
watch(
    () => props.filters,
    (newFilters) => {
        minPrice.value = newFilters.min_price || '';
        maxPrice.value = newFilters.max_price || '';
        inStock.value = newFilters.in_stock === '1' || newFilters.in_stock === 'true';
        hasDiscount.value = newFilters.has_discount === '1' || newFilters.has_discount === 'true';
        isFeatured.value = newFilters.is_featured === '1' || newFilters.is_featured === 'true';
        isNew.value = newFilters.is_new === '1' || newFilters.is_new === 'true';
        lowStock.value = newFilters.low_stock === '1' || newFilters.low_stock === 'true';
        minDiscount.value = newFilters.min_discount || '';
        maxDiscount.value = newFilters.max_discount || '';
    },
    { deep: true }
);

// Debounce timer for price and discount filters
let priceFilterTimeout: ReturnType<typeof setTimeout> | null = null;
let discountFilterTimeout: ReturnType<typeof setTimeout> | null = null;

// Watch for price changes with debounce
watch([minPrice, maxPrice], () => {
    if (priceFilterTimeout) {
        clearTimeout(priceFilterTimeout);
    }
    priceFilterTimeout = setTimeout(() => {
        applyFilters();
    }, 500);
});

// Watch for discount changes with debounce
watch([minDiscount, maxDiscount], () => {
    if (discountFilterTimeout) {
        clearTimeout(discountFilterTimeout);
    }
    discountFilterTimeout = setTimeout(() => {
        applyFilters();
    }, 500);
});

const hasActiveFilters = computed(() => {
    return !!(
        props.filters.category ||
        minPrice.value ||
        maxPrice.value ||
        inStock.value ||
        hasDiscount.value ||
        isFeatured.value ||
        isNew.value ||
        lowStock.value ||
        minDiscount.value ||
        maxDiscount.value
    );
});

const applyFilters = () => {
    const filters: Record<string, any> = {
        search: props.filters.search || undefined,
        category: props.filters.category || undefined,
        min_price: minPrice.value && minPrice.value.toString().trim() !== '' ? minPrice.value.toString() : undefined,
        max_price: maxPrice.value && maxPrice.value.toString().trim() !== '' ? maxPrice.value.toString() : undefined,
        in_stock: inStock.value ? '1' : undefined,
        has_discount: hasDiscount.value ? '1' : undefined,
        is_featured: isFeatured.value ? '1' : undefined,
        is_new: isNew.value ? '1' : undefined,
        low_stock: lowStock.value ? '1' : undefined,
        min_discount: minDiscount.value && minDiscount.value.toString().trim() !== '' ? minDiscount.value.toString() : undefined,
        max_discount: maxDiscount.value && maxDiscount.value.toString().trim() !== '' ? maxDiscount.value.toString() : undefined,
        sort_by: props.filters.sort_by || 'created_at',
        sort_order: props.filters.sort_order || 'desc',
    };

    // Remove undefined, null, and empty string values
    Object.keys(filters).forEach(key => {
        const value = filters[key];
        if (value === undefined || value === null || value === '' || (typeof value === 'string' && value.trim() === '')) {
            delete filters[key];
        }
    });

    // Emit filter change event for real-time filtering
    emit('filter-change', filters);
};

const clearFilters = () => {
    minPrice.value = '';
    maxPrice.value = '';
    inStock.value = false;
    hasDiscount.value = false;
    isFeatured.value = false;
    isNew.value = false;
    lowStock.value = false;
    minDiscount.value = '';
    maxDiscount.value = '';

    const filters: Record<string, any> = {
        search: props.filters.search || undefined,
        sort_by: props.filters.sort_by || 'created_at',
        sort_order: props.filters.sort_order || 'desc',
    };

    // Remove undefined values
    Object.keys(filters).forEach(key => {
        if (filters[key] === undefined || filters[key] === '') {
            delete filters[key];
        }
    });

    // Emit filter change event
    emit('filter-change', filters);
};
</script>

<template>
    <div class="space-y-6">
        <!-- Price Range -->
        <div>
            <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">
                {{ t('price') }}
            </h3>
            <div class="space-y-3">
                <div class="flex items-center gap-2">
                    <Label for="min_price" class="w-20 text-sm dark:text-white">{{ t('from') }}:</Label>
                    <Input
                        id="min_price"
                        v-model="minPrice"
                        type="number"
                        :placeholder="t('min')"
                        class="flex-1 dark:border-gray-600 dark:text-white dark:placeholder:text-gray-400"
                        min="0"
                        step="0.01"
                    />
                </div>
                <div class="flex items-center gap-2">
                    <Label for="max_price" class="w-20 text-sm dark:text-white">{{ t('to') }}:</Label>
                    <Input
                        id="max_price"
                        v-model="maxPrice"
                        type="number"
                        :placeholder="t('max')"
                        class="flex-1 dark:border-gray-600 dark:text-white dark:placeholder:text-gray-400"
                        min="0"
                        step="0.01"
                    />
                </div>
                <Button
                    @click="applyFilters"
                    class="w-full dark:bg-primary dark:text-white dark:hover:bg-primary/90"
                    size="sm"
                >
                    {{ t('apply_price') }}
                </Button>
            </div>
        </div>

        <Separator />

        <!-- Availability -->
        <div>
            <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">
                {{ t('availability') }}
            </h3>
            <div class="space-y-3">
                <div class="flex items-center space-x-2">
                    <Checkbox
                        id="in_stock"
                        :checked="inStock"
                        class="dark:border-gray-600"
                        @update:checked="(checked: boolean | 'indeterminate') => { inStock = checked === true; applyFilters(); }"
                    />
                    <Label
                        for="in_stock"
                        class="text-sm font-normal cursor-pointer dark:text-white"
                    >
                        {{ t('in_stock_only') }}
                    </Label>
                </div>
            </div>
        </div>

        <Separator />

        <!-- Discount -->
        <div>
            <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">
                {{ t('offers') }}
            </h3>
            <div class="space-y-3">
                <div class="flex items-center space-x-2">
                    <Checkbox
                        id="has_discount"
                        :checked="hasDiscount"
                        class="dark:border-gray-600"
                        @update:checked="(checked: boolean | 'indeterminate') => { hasDiscount = checked === true; applyFilters(); }"
                    />
                    <Label
                        for="has_discount"
                        class="text-sm font-normal cursor-pointer dark:text-white"
                    >
                        {{ t('products_with_discount_only') }}
                    </Label>
                </div>
                <div class="space-y-2 pt-2">
                    <div class="flex items-center gap-2">
                        <Label for="min_discount" class="w-20 text-xs dark:text-white">Min %:</Label>
                        <Input
                            id="min_discount"
                            v-model="minDiscount"
                            type="number"
                            placeholder="0"
                            class="flex-1 text-sm dark:border-gray-600 dark:text-white dark:placeholder:text-gray-400"
                            min="0"
                            max="100"
                            @keyup.enter="applyFilters"
                        />
                    </div>
                    <div class="flex items-center gap-2">
                        <Label for="max_discount" class="w-20 text-xs dark:text-white">Max %:</Label>
                        <Input
                            id="max_discount"
                            v-model="maxDiscount"
                            type="number"
                            placeholder="100"
                            class="flex-1 text-sm dark:border-gray-600 dark:text-white dark:placeholder:text-gray-400"
                            min="0"
                            max="100"
                            @keyup.enter="applyFilters"
                        />
                    </div>
                    <Button
                        @click="applyFilters"
                        class="w-full dark:border-gray-600 dark:text-white dark:hover:bg-gray-800"
                        size="sm"
                        variant="outline"
                    >
                        {{ t('apply_discount') }}
                    </Button>
                </div>
            </div>
        </div>

        <Separator />

        <!-- Product Type -->
        <div>
            <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">
                {{ t('product_type') }}
            </h3>
            <div class="space-y-3">
                <div class="flex items-center space-x-2">
                    <Checkbox
                        id="is_featured"
                        :checked="isFeatured"
                        class="dark:border-gray-600"
                        @update:checked="(checked: boolean | 'indeterminate') => { isFeatured = checked === true; applyFilters(); }"
                    />
                    <Label
                        for="is_featured"
                        class="text-sm font-normal cursor-pointer dark:text-white"
                    >
                        {{ t('featured_products') }}
                    </Label>
                </div>
                <div class="flex items-center space-x-2">
                    <Checkbox
                        id="is_new"
                        :checked="isNew"
                        class="dark:border-gray-600"
                        @update:checked="(checked: boolean | 'indeterminate') => { isNew = checked === true; applyFilters(); }"
                    />
                    <Label
                        for="is_new"
                        class="text-sm font-normal cursor-pointer dark:text-white"
                    >
                        {{ t('new_products') }}
                    </Label>
                </div>
            </div>
        </div>

        <Separator />

        <!-- Stock Status -->
        <div>
            <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">
                {{ t('stock') }}
            </h3>
            <div class="space-y-3">
                <div class="flex items-center space-x-2">
                    <Checkbox
                        id="low_stock"
                        :checked="lowStock"
                        class="dark:border-gray-600"
                        @update:checked="(checked: boolean | 'indeterminate') => { lowStock = checked === true; applyFilters(); }"
                    />
                    <Label
                        for="low_stock"
                        class="text-sm font-normal cursor-pointer dark:text-white"
                    >
                        {{ t('low_stock') }}
                    </Label>
                </div>
            </div>
        </div>

        <!-- Clear Filters -->
        <div v-if="hasActiveFilters">
            <Separator />
            <Button
                @click="clearFilters"
                variant="outline"
                class="w-full dark:border-gray-600 dark:text-white dark:hover:bg-gray-800"
                size="sm"
            >
                <X class="mr-2 h-4 w-4" />
                {{ t('clear_filters') }}
            </Button>
        </div>
    </div>
</template>

