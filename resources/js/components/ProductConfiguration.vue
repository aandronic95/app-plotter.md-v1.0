<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import { useTranslations } from '@/composables/useTranslations';

interface Configuration {
    id: number;
    print_size: string;
    print_sides: string;
    format?: string | null;
    suport?: string | null;
    culoare?: string | null;
    colturi?: string | null;
    quantity: number;
    price: number;
    price_per_unit: number;
    production_days: number;
    production_date: string;
    production_date_raw: string;
    formatted_price: string;
    formatted_price_per_unit: string;
    is_active?: boolean;
}

interface CategoryConfiguration {
    name: string;
    image?: string;
    description?: string;
}

interface CategoryConfigurations {
    formats?: CategoryConfiguration[];
    suport?: CategoryConfiguration[];
    culoare?: CategoryConfiguration[];
    colturi?: CategoryConfiguration[];
}

interface Props {
    configurations: Configuration[];
    categoryConfigurations?: CategoryConfigurations | null;
}

const props = defineProps<Props>();

const emit = defineEmits<{
    'configuration-selected': [config: Configuration | null];
}>();

const { t } = useTranslations();

const selectedSize = ref<string>('');
const selectedSides = ref<string>('');
const selectedFormat = ref<string | null>(null);
const selectedSuport = ref<string | null>(null);
const selectedCuloare = ref<string | null>(null);
const selectedColturi = ref<string | null>(null);
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

// Get available formats from category configurations
const availableFormatsForSelection = computed(() => {
    if (!props.categoryConfigurations?.formats || !Array.isArray(props.categoryConfigurations.formats) || props.categoryConfigurations.formats.length === 0) {
        return [];
    }
    return props.categoryConfigurations.formats.map(f => f.name).filter(name => name && name.trim() !== '');
});

// Get available options for category configurations
const availableSuportOptions = computed(() => {
    if (!props.categoryConfigurations?.suport) return [];
    return props.categoryConfigurations.suport.map(s => s.name);
});

const availableCuloareOptions = computed(() => {
    if (!props.categoryConfigurations?.culoare) return [];
    return props.categoryConfigurations.culoare.map(c => c.name);
});

const availableColturiOptions = computed(() => {
    if (!props.categoryConfigurations?.colturi) return [];
    return props.categoryConfigurations.colturi.map(c => c.name);
});

// Get available quantities for selected size, sides, format and other configurations
const availableQuantities = computed(() => {
    if (!selectedSize.value || !selectedSides.value) return [];
    
    // Dacă există formate disponibile, trebuie să fie selectat un format
    if (availableFormatsForSelection.value.length > 0 && !selectedFormat.value) {
        return [];
    }
    
    return props.configurations
        .filter(c => {
            const matchesSize = c.print_size === selectedSize.value;
            const matchesSides = c.print_sides === selectedSides.value;
            
            if (!matchesSize || !matchesSides) return false;
            
            // Format matching: dacă există formate disponibile, trebuie să se potrivească
            let matchesFormat = true;
            if (availableFormatsForSelection.value.length > 0) {
                matchesFormat = selectedFormat.value !== null && c.format === selectedFormat.value;
            } else {
                // Dacă nu există formate disponibile, acceptă orice (inclusiv null)
                matchesFormat = true;
            }
            
            if (!matchesFormat) return false;
            
            // Suport matching: dacă este selectat, trebuie să se potrivească; altfel acceptă orice
            let matchesSuport = true;
            if (selectedSuport.value !== null) {
                matchesSuport = c.suport === selectedSuport.value;
            }
            
            // Culoare matching: dacă este selectată, trebuie să se potrivească; altfel acceptă orice
            let matchesCuloare = true;
            if (selectedCuloare.value !== null) {
                matchesCuloare = c.culoare === selectedCuloare.value;
            }
            
            // Colturi matching: dacă sunt selectate, trebuie să se potrivească; altfel acceptă orice
            let matchesColturi = true;
            if (selectedColturi.value !== null) {
                matchesColturi = c.colturi === selectedColturi.value;
            }
            
            const isActive = c.is_active !== false;
            
            return matchesSuport && matchesCuloare && matchesColturi && isActive;
        })
        .sort((a, b) => a.quantity - b.quantity);
});

// Get selected configuration
const selectedConfiguration = computed(() => {
    if (!selectedSize.value || !selectedSides.value || !selectedQuantity.value) {
        return null;
    }
    
    // Dacă există formate disponibile, trebuie să fie selectat un format
    if (availableFormatsForSelection.value.length > 0 && !selectedFormat.value) {
        return null;
    }
    
    return props.configurations.find(
        c => {
            const matchesSize = c.print_size === selectedSize.value;
            const matchesSides = c.print_sides === selectedSides.value;
            
            if (!matchesSize || !matchesSides) return false;
            
            // Format matching
            let matchesFormat = true;
            if (availableFormatsForSelection.value.length > 0) {
                matchesFormat = selectedFormat.value !== null && c.format === selectedFormat.value;
            }
            
            if (!matchesFormat) return false;
            
            // Suport matching: dacă este selectat, trebuie să se potrivească; altfel acceptă orice
            let matchesSuport = true;
            if (selectedSuport.value !== null) {
                matchesSuport = c.suport === selectedSuport.value;
            }
            
            // Culoare matching: dacă este selectată, trebuie să se potrivească; altfel acceptă orice
            let matchesCuloare = true;
            if (selectedCuloare.value !== null) {
                matchesCuloare = c.culoare === selectedCuloare.value;
            }
            
            // Colturi matching: dacă sunt selectate, trebuie să se potrivească; altfel acceptă orice
            let matchesColturi = true;
            if (selectedColturi.value !== null) {
                matchesColturi = c.colturi === selectedColturi.value;
            }
            
            const matchesQuantity = c.quantity === selectedQuantity.value;
            
            return matchesSuport && matchesCuloare && matchesColturi && matchesQuantity;
        }
    ) || null;
});

// Get unique formats for selected size and sides
const availableFormats = computed(() => {
    if (!selectedSize.value || !selectedSides.value) return [];
    const formats = new Set(
        props.configurations
            .filter(c => 
                c.print_size === selectedSize.value && 
                c.print_sides === selectedSides.value &&
                c.format
            )
            .map(c => c.format)
            .filter((f): f is string => f !== null && f !== undefined)
    );
    return Array.from(formats).sort();
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
    selectedFormat.value = null;
    selectedSuport.value = null;
    selectedCuloare.value = null;
    selectedColturi.value = null;
    selectedQuantity.value = null;
});

watch(selectedSides, () => {
    // Auto-select primul format disponibil dacă există formate
    if (availableFormatsForSelection.value.length > 0) {
        selectedFormat.value = availableFormatsForSelection.value[0];
    } else {
        selectedFormat.value = null;
    }
    selectedQuantity.value = null;
});

watch(selectedFormat, () => {
    selectedQuantity.value = null;
});

watch([selectedSuport, selectedCuloare, selectedColturi], () => {
    selectedQuantity.value = null;
});

watch(availableQuantities, () => {
    // Auto-select prima cantitate disponibilă când se schimbă configurațiile
    if (availableQuantities.value.length > 0 && !selectedQuantity.value) {
        selectedQuantity.value = availableQuantities.value[0].quantity;
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

const selectFormat = (format: string | null) => {
    selectedFormat.value = format;
};

const selectSuport = (suport: string | null) => {
    selectedSuport.value = suport;
};

const selectCuloare = (culoare: string | null) => {
    selectedCuloare.value = culoare;
};

const selectColturi = (colturi: string | null) => {
    selectedColturi.value = colturi;
};

const selectQuantity = (quantity: number) => {
    selectedQuantity.value = quantity;
};

const getConfigImage = (type: 'suport' | 'culoare' | 'colturi', name: string): string | undefined => {
    const configs = props.categoryConfigurations?.[type];
    if (!configs) return undefined;
    const config = configs.find(c => c.name === name);
    return config?.image;
};

const getFormatImage = (name: string): string | undefined => {
    const formats = props.categoryConfigurations?.formats;
    if (!formats) return undefined;
    const format = formats.find(f => f.name === name);
    return format?.image;
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

                <!-- Format Selection (if available) -->
                <div v-if="selectedSize && selectedSides && availableFormatsForSelection.length > 0">
                    <h4 class="mb-3 text-sm font-medium text-gray-700 dark:text-gray-300">
                        {{ t('format') }}
                    </h4>
                    <div class="flex flex-wrap gap-3">
                        <button
                            v-for="format in availableFormatsForSelection"
                            :key="format"
                            type="button"
                            :class="[
                                'relative rounded-lg border-2 px-4 py-2 text-sm font-medium transition-all hover:shadow-md flex items-center gap-2',
                                selectedFormat === format
                                    ? 'border-green-500 bg-green-50 dark:bg-green-900/20 text-green-900 dark:text-green-100'
                                    : 'border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800 text-gray-900 dark:text-white'
                            ]"
                            @click="selectFormat(selectedFormat === format ? null : format)"
                        >
                            <img 
                                v-if="getFormatImage(format)" 
                                :src="getFormatImage(format)" 
                                :alt="format"
                                class="w-6 h-6 object-cover rounded"
                            />
                            {{ format }}
                        </button>
                    </div>
                </div>

                <!-- Suport Selection (if available) -->
                <div v-if="selectedSize && selectedSides && availableSuportOptions.length > 0">
                    <h4 class="mb-3 text-sm font-medium text-gray-700 dark:text-gray-300">
                        {{ t('suport') || 'Suport' }}
                    </h4>
                    <div class="flex flex-wrap gap-3">
                        <button
                            v-for="suport in availableSuportOptions"
                            :key="suport"
                            type="button"
                            :class="[
                                'relative rounded-lg border-2 px-4 py-2 text-sm font-medium transition-all hover:shadow-md flex items-center gap-2',
                                selectedSuport === suport
                                    ? 'border-green-500 bg-green-50 dark:bg-green-900/20 text-green-900 dark:text-green-100'
                                    : 'border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800 text-gray-900 dark:text-white'
                            ]"
                            @click="selectSuport(selectedSuport === suport ? null : suport)"
                        >
                            <img 
                                v-if="getConfigImage('suport', suport)" 
                                :src="getConfigImage('suport', suport)" 
                                :alt="suport"
                                class="w-6 h-6 object-cover rounded"
                            />
                            {{ suport }}
                        </button>
                    </div>
                </div>

                <!-- Culoare Selection (if available) -->
                <div v-if="selectedSize && selectedSides && availableCuloareOptions.length > 0">
                    <h4 class="mb-3 text-sm font-medium text-gray-700 dark:text-gray-300">
                        {{ t('culoare') || 'Culoare' }}
                    </h4>
                    <div class="flex flex-wrap gap-3">
                        <button
                            v-for="culoare in availableCuloareOptions"
                            :key="culoare"
                            type="button"
                            :class="[
                                'relative rounded-lg border-2 px-4 py-2 text-sm font-medium transition-all hover:shadow-md flex items-center gap-2',
                                selectedCuloare === culoare
                                    ? 'border-green-500 bg-green-50 dark:bg-green-900/20 text-green-900 dark:text-green-100'
                                    : 'border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800 text-gray-900 dark:text-white'
                            ]"
                            @click="selectCuloare(selectedCuloare === culoare ? null : culoare)"
                        >
                            <img 
                                v-if="getConfigImage('culoare', culoare)" 
                                :src="getConfigImage('culoare', culoare)" 
                                :alt="culoare"
                                class="w-6 h-6 object-cover rounded"
                            />
                            {{ culoare }}
                        </button>
                    </div>
                </div>

                <!-- Colturi Selection (if available) -->
                <div v-if="selectedSize && selectedSides && availableColturiOptions.length > 0">
                    <h4 class="mb-3 text-sm font-medium text-gray-700 dark:text-gray-300">
                        {{ t('colturi') || 'Colțuri' }}
                    </h4>
                    <div class="flex flex-wrap gap-3">
                        <button
                            v-for="colturi in availableColturiOptions"
                            :key="colturi"
                            type="button"
                            :class="[
                                'relative rounded-lg border-2 px-4 py-2 text-sm font-medium transition-all hover:shadow-md flex items-center gap-2',
                                selectedColturi === colturi
                                    ? 'border-green-500 bg-green-50 dark:bg-green-900/20 text-green-900 dark:text-green-100'
                                    : 'border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800 text-gray-900 dark:text-white'
                            ]"
                            @click="selectColturi(selectedColturi === colturi ? null : colturi)"
                        >
                            <img 
                                v-if="getConfigImage('colturi', colturi)" 
                                :src="getConfigImage('colturi', colturi)" 
                                :alt="colturi"
                                class="w-6 h-6 object-cover rounded"
                            />
                            {{ colturi }}
                        </button>
                    </div>
                </div>
            </CardContent>
        </Card>

        <!-- Section 2: Select quantity -->
        <Card v-if="selectedSize && selectedSides && (availableFormatsForSelection.length === 0 || selectedFormat !== null) && availableQuantities.length > 0">
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
                                <th 
                                    v-if="availableQuantities.some(c => c.format)"
                                    class="px-4 py-3 text-left text-sm font-medium text-gray-700 dark:text-gray-300"
                                >
                                    {{ t('format') }}
                                </th>
                                <th 
                                    v-if="availableQuantities.some(c => c.suport)"
                                    class="px-4 py-3 text-left text-sm font-medium text-gray-700 dark:text-gray-300"
                                >
                                    {{ t('suport') || 'Suport' }}
                                </th>
                                <th 
                                    v-if="availableQuantities.some(c => c.culoare)"
                                    class="px-4 py-3 text-left text-sm font-medium text-gray-700 dark:text-gray-300"
                                >
                                    {{ t('culoare') || 'Culoare' }}
                                </th>
                                <th 
                                    v-if="availableQuantities.some(c => c.colturi)"
                                    class="px-4 py-3 text-left text-sm font-medium text-gray-700 dark:text-gray-300"
                                >
                                    {{ t('colturi') || 'Colțuri' }}
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
                                <td 
                                    v-if="availableQuantities.some(c => c.format)"
                                    class="px-4 py-4 text-sm text-gray-600 dark:text-gray-400"
                                >
                                    <span 
                                        v-if="config.format"
                                        class="inline-flex items-center rounded-full bg-blue-100 dark:bg-blue-900/40 px-2 py-1 text-xs font-medium text-blue-800 dark:text-blue-200"
                                    >
                                        {{ config.format }}
                                    </span>
                                    <span v-else class="text-gray-400 dark:text-gray-500">—</span>
                                </td>
                                <td 
                                    v-if="availableQuantities.some(c => c.suport)"
                                    class="px-4 py-4 text-sm text-gray-600 dark:text-gray-400"
                                >
                                    <span 
                                        v-if="config.suport"
                                        class="inline-flex items-center rounded-full bg-purple-100 dark:bg-purple-900/40 px-2 py-1 text-xs font-medium text-purple-800 dark:text-purple-200"
                                    >
                                        {{ config.suport }}
                                    </span>
                                    <span v-else class="text-gray-400 dark:text-gray-500">—</span>
                                </td>
                                <td 
                                    v-if="availableQuantities.some(c => c.culoare)"
                                    class="px-4 py-4 text-sm text-gray-600 dark:text-gray-400"
                                >
                                    <span 
                                        v-if="config.culoare"
                                        class="inline-flex items-center rounded-full bg-pink-100 dark:bg-pink-900/40 px-2 py-1 text-xs font-medium text-pink-800 dark:text-pink-200"
                                    >
                                        {{ config.culoare }}
                                    </span>
                                    <span v-else class="text-gray-400 dark:text-gray-500">—</span>
                                </td>
                                <td 
                                    v-if="availableQuantities.some(c => c.colturi)"
                                    class="px-4 py-4 text-sm text-gray-600 dark:text-gray-400"
                                >
                                    <span 
                                        v-if="config.colturi"
                                        class="inline-flex items-center rounded-full bg-orange-100 dark:bg-orange-900/40 px-2 py-1 text-xs font-medium text-orange-800 dark:text-orange-200"
                                    >
                                        {{ config.colturi }}
                                    </span>
                                    <span v-else class="text-gray-400 dark:text-gray-500">—</span>
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

