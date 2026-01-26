<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import { useTranslations } from '@/composables/useTranslations';

interface Configuration {
    id: number;
    print_size: string;
    print_sides: string;
    quantity: number;
    price: number;
    price_per_unit: number;
    production_days: number;
    production_date: string;
    production_date_raw: string;
    formatted_price: string;
    formatted_price_per_unit: string;
}

interface Props {
    configurations: Configuration[];
}

const props = defineProps<Props>();

const emit = defineEmits<{
    'configuration-selected': [config: Configuration | null];
}>();

const { t } = useTranslations();

const selectedSize = ref<string>('');
const selectedSides = ref<string>('');
const selectedQuantity = ref<number | null>(null);

// Get unique sizes
const availableSizes = computed(() => {
    const sizes = new Set(props.configurations.map(c => c.print_size));
    return Array.from(sizes).sort();
});

// Get unique sides for selected size
const availableSides = computed(() => {
    if (!selectedSize.value) return [];
    const sides = new Set(
        props.configurations
            .filter(c => c.print_size === selectedSize.value)
            .map(c => c.print_sides)
    );
    return Array.from(sides).sort();
});

// Get available quantities for selected size and sides
const availableQuantities = computed(() => {
    if (!selectedSize.value || !selectedSides.value) return [];
    return props.configurations
        .filter(c => c.print_size === selectedSize.value && c.print_sides === selectedSides.value)
        .sort((a, b) => a.quantity - b.quantity);
});

// Get selected configuration
const selectedConfiguration = computed(() => {
    if (!selectedSize.value || !selectedSides.value || !selectedQuantity.value) {
        return null;
    }
    return props.configurations.find(
        c =>
            c.print_size === selectedSize.value &&
            c.print_sides === selectedSides.value &&
            c.quantity === selectedQuantity.value
    ) || null;
});

// Auto-select first available options
watch(
    () => props.configurations,
    (configs) => {
        if (configs.length > 0 && !selectedSize.value) {
            selectedSize.value = availableSizes.value[0] || '';
        }
    },
    { immediate: true }
);

watch(selectedSize, () => {
    if (availableSides.value.length > 0) {
        selectedSides.value = availableSides.value[0] || '';
    } else {
        selectedSides.value = '';
    }
    selectedQuantity.value = null;
});

watch(selectedSides, () => {
    if (availableQuantities.value.length > 0) {
        selectedQuantity.value = availableQuantities.value[0].quantity;
    } else {
        selectedQuantity.value = null;
    }
});

watch(selectedConfiguration, (config) => {
    emit('configuration-selected', config);
}, { immediate: true });

const selectSize = (size: string) => {
    selectedSize.value = size;
};

const selectSides = (sides: string) => {
    selectedSides.value = sides;
};

const selectQuantity = (quantity: number) => {
    selectedQuantity.value = quantity;
};

const getSidesLabel = (sides: string) => {
    if (sides === '4+0') return t('print_sides_one_sided');
    if (sides === '4+4') return t('print_sides_two_sided');
    return sides;
};

const getSizeLabel = (size: string) => {
    if (size === 'A3') return t('print_size_a3');
    if (size === 'A4') return t('print_size_a4');
    return size;
};
</script>

<template>
    <div v-if="configurations.length > 0" class="space-y-6">
        <!-- Section 1: Select print characteristics -->
        <Card>
            <CardHeader>
                <div class="flex items-center gap-2">
                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-primary text-sm font-bold text-primary-foreground">
                        1
                    </div>
                    <h3 class="text-lg font-semibold">{{ t('select_print_characteristics') }}</h3>
                </div>
            </CardHeader>
            <CardContent class="space-y-6">
                <!-- Print Sizes -->
                <div>
                    <h4 class="mb-3 text-sm font-medium text-gray-700 dark:text-gray-300">
                        {{ t('sizes_in_unfolded_view') }}
                    </h4>
                    <div class="grid grid-cols-2 gap-4">
                        <button
                            v-for="size in availableSizes"
                            :key="size"
                            type="button"
                            :class="[
                                'relative rounded-lg border-2 p-4 text-left transition-all hover:shadow-md',
                                selectedSize === size
                                    ? 'border-green-500 bg-green-50 dark:bg-green-900/20'
                                    : 'border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800'
                            ]"
                            @click="selectSize(size)"
                        >
                            <div class="flex items-center justify-center mb-2">
                                <div
                                    :class="[
                                        'h-16 w-24 rounded border-2 border-dashed',
                                        selectedSize === size ? 'border-green-500' : 'border-gray-300 dark:border-gray-600'
                                    ]"
                                >
                                    <div
                                        v-if="size === 'A3'"
                                        class="h-full w-full flex items-center justify-center"
                                    >
                                        <div class="h-full w-1/3 border-r-2 border-dashed border-gray-400"></div>
                                        <div class="h-full w-1/3 border-r-2 border-dashed border-gray-400"></div>
                                        <div class="h-full w-1/3"></div>
                                    </div>
                                    <div
                                        v-else-if="size === 'A4'"
                                        class="h-full w-full flex items-center justify-center"
                                    >
                                        <div class="h-full w-1/2 border-r-2 border-dashed border-gray-400"></div>
                                        <div class="h-full w-1/2"></div>
                                    </div>
                                </div>
                            </div>
                            <p class="text-sm font-medium text-gray-900 dark:text-white">
                                {{ getSizeLabel(size) }}
                            </p>
                        </button>
                    </div>
                </div>

                <!-- Print Sides -->
                <div v-if="selectedSize">
                    <h4 class="mb-3 text-sm font-medium text-gray-700 dark:text-gray-300">
                        {{ t('print_sides') }}
                    </h4>
                    <div class="grid grid-cols-2 gap-4">
                        <button
                            v-for="sides in availableSides"
                            :key="sides"
                            type="button"
                            :class="[
                                'relative rounded-lg border-2 p-4 text-left transition-all hover:shadow-md',
                                selectedSides === sides
                                    ? 'border-green-500 bg-green-50 dark:bg-green-900/20'
                                    : 'border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800'
                            ]"
                            @click="selectSides(sides)"
                        >
                            <div class="flex items-center justify-center mb-2 gap-1">
                                <!-- Left page -->
                                <div class="h-12 w-16 rounded border border-gray-300 dark:border-gray-600 p-1">
                                    <div class="flex gap-0.5">
                                        <div class="h-2 w-2 rounded-full bg-cyan-500"></div>
                                        <div class="h-2 w-2 rounded-full bg-magenta-500"></div>
                                        <div class="h-2 w-2 rounded-full bg-yellow-500"></div>
                                        <div class="h-2 w-2 rounded-full bg-black dark:bg-white"></div>
                                    </div>
                                </div>
                                <!-- Right page -->
                                <div
                                    :class="[
                                        'h-12 w-16 rounded border p-1',
                                        sides === '4+0'
                                            ? 'border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-900'
                                            : 'border-gray-300 dark:border-gray-600'
                                    ]"
                                >
                                    <div v-if="sides === '4+4'" class="flex gap-0.5">
                                        <div class="h-2 w-2 rounded-full bg-cyan-500"></div>
                                        <div class="h-2 w-2 rounded-full bg-magenta-500"></div>
                                        <div class="h-2 w-2 rounded-full bg-yellow-500"></div>
                                        <div class="h-2 w-2 rounded-full bg-black dark:bg-white"></div>
                                    </div>
                                </div>
                            </div>
                            <p class="text-sm font-medium text-gray-900 dark:text-white">
                                {{ getSidesLabel(sides) }}
                            </p>
                        </button>
                    </div>
                </div>
            </CardContent>
        </Card>

        <!-- Section 2: Select quantity -->
        <Card v-if="selectedSize && selectedSides">
            <CardHeader>
                <div class="flex items-center gap-2">
                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-primary text-sm font-bold text-primary-foreground">
                        2
                    </div>
                    <h3 class="text-lg font-semibold">{{ t('select_quantity') }}</h3>
                </div>
            </CardHeader>
            <CardContent>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-gray-700">
                                <th class="px-4 py-3 text-left text-sm font-medium text-gray-700 dark:text-gray-300">
                                    {{ t('quantity_pcs') }}
                                </th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-gray-700 dark:text-gray-300">
                                    {{ t('production_time') }}
                                </th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-gray-700 dark:text-gray-300">
                                    {{ t('price') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="config in availableQuantities"
                                :key="config.id"
                                :class="[
                                    'cursor-pointer border-b border-gray-100 dark:border-gray-800 transition-colors',
                                    selectedQuantity === config.quantity
                                        ? 'bg-green-50 dark:bg-green-900/20 border-green-500 border-2'
                                        : 'hover:bg-gray-50 dark:hover:bg-gray-800'
                                ]"
                                @click="selectQuantity(config.quantity)"
                            >
                                <td class="px-4 py-4 text-sm font-medium text-gray-900 dark:text-white">
                                    {{ config.quantity }}
                                </td>
                                <td class="px-4 py-4 text-sm text-gray-600 dark:text-gray-400">
                                    <div>{{ config.production_date }}</div>
                                </td>
                                <td class="px-4 py-4 text-sm text-gray-900 dark:text-white">
                                    <div class="font-semibold">{{ config.formatted_price }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ config.formatted_price_per_unit }} {{ t('per_piece') }}
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </CardContent>
        </Card>
    </div>
</template>

