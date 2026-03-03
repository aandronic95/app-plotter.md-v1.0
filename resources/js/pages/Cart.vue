<script setup lang="ts">
import AppFooter from '@/components/AppFooter.vue';
import PublicHeader from '@/components/PublicHeader.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import Dialog from '@/components/ui/dialog/Dialog.vue';
import DialogContent from '@/components/ui/dialog/DialogContent.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ShoppingCart, Trash2, Plus, Minus, ArrowLeft, Edit2, Upload, FileCheck, AlertCircle } from 'lucide-vue-next';
import { onMounted, ref, computed, watch } from 'vue';
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
    price_coefficient?: number;
    effective_price_per_unit?: number;
    effective_price?: number;
    formatted_effective_price_per_unit?: string;
    formatted_effective_price?: string;
    production_days: number;
    formatted_price: string;
    formatted_price_per_unit: string;
}

interface CartItem {
    id: number;
    name: string;
    slug: string;
    price: number;
    image: string;
    quantity: number;
    subtotal: number;
    stock_quantity: number;
    print_size?: string | null;
    print_sides?: string | null;
    format?: string | null;
    suport?: string | null;
    culoare?: string | null;
    colturi?: string | null;
    configuration_quantity?: number | null;
    configurations?: Configuration[];
    mockup_path?: string | null;
    mockup_filename?: string | null;
    mockup_url?: string | null;
    elaborate_mockup?: boolean;
    elaborate_mockup_price?: number | null;
}

interface CartData {
    items: CartItem[];
    total: number;
    count: number;
    minimum_order_amount?: number;
    meets_minimum_order?: boolean;
}

const cart = ref<CartData>({
    items: [],
    total: 0,
    count: 0,
    minimum_order_amount: 300,
    meets_minimum_order: true,
});

const loading = ref(false);
const mockupUploadingForId = ref<number | null>(null);
const editingConfigItemId = ref<number | null>(null);
const pendingConfigChanges = ref<Map<number, Configuration | null>>(new Map());
const { t } = useTranslations();
const page = usePage();
const flashError = computed(() => (page.props.flash as { error?: string })?.error);
const showMinimumOrderDialog = ref(false);

const defaultElaborateMockupPrice = 500;

// Deschide dialogul când coșul are produse dar nu atinge minimul
watch(
    () => [cart.value.items.length, cart.value.meets_minimum_order],
    ([count, meets]) => {
        if (count > 0 && meets === false) {
            showMinimumOrderDialog.value = true;
        }
    },
    { immediate: false }
);

function triggerMockupInput(cartIndex: number) {
    if (typeof document === 'undefined') return;
    const el = document.getElementById(`mockup-input-${cartIndex}`);
    if (el instanceof HTMLInputElement) el.click();
}

function getMockupPayload(item: CartItem) {
    const payload: Record<string, string | number | null> = {
        product_id: item.id,
    };
    if (item.print_size) payload.print_size = item.print_size;
    if (item.print_sides) payload.print_sides = item.print_sides;
    if (item.format) payload.format = item.format;
    if (item.suport) payload.suport = item.suport;
    if (item.culoare) payload.culoare = item.culoare;
    if (item.colturi) payload.colturi = item.colturi;
    if (item.configuration_quantity) payload.configuration_quantity = item.configuration_quantity;
    return payload;
}

const onMockupFileSelect = async (item: CartItem, event: Event) => {
    const input = event.target as HTMLInputElement;
    const file = input?.files?.[0];
    if (!file) return;
    mockupUploadingForId.value = item.id;
    try {
        const formData = new FormData();
        formData.append('product_id', String(item.id));
        formData.append('mockup', file);
        if (item.print_size) formData.append('print_size', item.print_size);
        if (item.print_sides) formData.append('print_sides', item.print_sides);
        if (item.format) formData.append('format', item.format);
        if (item.suport) formData.append('suport', item.suport);
        if (item.culoare) formData.append('culoare', item.culoare);
        if (item.colturi) formData.append('colturi', item.colturi);
        if (item.configuration_quantity) formData.append('configuration_quantity', String(item.configuration_quantity));
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        const response = await fetch('/cart/mockup', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: formData,
            credentials: 'same-origin',
        });
        if (response.ok) {
            await fetchCart();
        } else {
            const data = await response.json().catch(() => ({}));
            const msg = data.errors?.mockup?.[0] ?? data.message ?? t('error_uploading_mockup');
            alert(msg);
        }
    } catch (e) {
        console.error(e);
        alert(t('error_uploading_mockup'));
    } finally {
        mockupUploadingForId.value = null;
        input.value = '';
    }
};

const toggleElaborateMockup = async (item: CartItem, checked: boolean) => {
    loading.value = true;
    try {
        const payload = getMockupPayload(item);
        (payload as Record<string, unknown>).elaborate_mockup = checked;
        (payload as Record<string, unknown>).elaborate_mockup_price = checked ? (item.elaborate_mockup_price ?? defaultElaborateMockupPrice) : 0;
        const response = await fetch('/cart/mockup-option', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                'Accept': 'application/json',
            },
            body: JSON.stringify(payload),
        });
        if (response.ok) {
            await fetchCart();
        } else {
            const data = await response.json().catch(() => ({}));
            alert(data.message || t('error_updating_quantity'));
        }
    } catch (e) {
        console.error(e);
        alert(t('error_updating_quantity'));
    } finally {
        loading.value = false;
    }
};

const getSizeLabel = (size: string) => {
    if (size === 'A3') return t('print_size_a3');
    if (size === 'A4') return t('print_size_a4');
    return size;
};

const getSidesLabel = (sides: string) => {
    if (sides === '4+0') return t('print_sides_one_sided');
    if (sides === '4+4') return t('print_sides_two_sided');
    return sides;
};

const getAvailableSizes = (item: CartItem) => {
    if (!item.configurations || item.configurations.length === 0) return [];
    const sizes = new Set(item.configurations.map(c => c.print_size));
    return Array.from(sizes).sort();
};

const getAvailableSides = (item: CartItem, size: string) => {
    if (!item.configurations || item.configurations.length === 0) return [];
    const sides = new Set(
        item.configurations
            .filter(c => c.print_size === size)
            .map(c => c.print_sides)
    );
    return Array.from(sides).sort();
};

const getAvailableQuantities = (item: CartItem, size: string, sides: string, format?: string | null, suport?: string | null, culoare?: string | null, colturi?: string | null) => {
    if (!item.configurations || item.configurations.length === 0) return [];
    return item.configurations
        .filter(c => {
            const matchesSize = c.print_size === size;
            const matchesSides = c.print_sides === sides;
            
            if (!matchesSize || !matchesSides) return false;
            
            // Format matching: dacă este specificat, trebuie să se potrivească; altfel acceptă orice
            let matchesFormat = true;
            if (format !== null && format !== undefined) {
                matchesFormat = c.format === format;
            } else {
                matchesFormat = !c.format || c.format === '';
            }
            
            // Suport matching
            let matchesSuport = true;
            if (suport !== null && suport !== undefined) {
                matchesSuport = c.suport === suport;
            } else {
                matchesSuport = !c.suport || c.suport === '';
            }
            
            // Culoare matching
            let matchesCuloare = true;
            if (culoare !== null && culoare !== undefined) {
                matchesCuloare = c.culoare === culoare;
            } else {
                matchesCuloare = !c.culoare || c.culoare === '';
            }
            
            // Colturi matching
            let matchesColturi = true;
            if (colturi !== null && colturi !== undefined) {
                matchesColturi = c.colturi === colturi;
            } else {
                matchesColturi = !c.colturi || c.colturi === '';
            }
            
            return matchesFormat && matchesSuport && matchesCuloare && matchesColturi;
        })
        .sort((a, b) => a.quantity - b.quantity);
};

const startEditConfig = (item: CartItem) => {
    editingConfigItemId.value = item.id;
    // Inițializează configurația curentă ca pending
    if (item.print_size && item.print_sides && item.configuration_quantity) {
        const currentConfig = item.configurations?.find(
            c => c.print_size === item.print_size &&
                 c.print_sides === item.print_sides &&
                 c.quantity === item.configuration_quantity
        );
        pendingConfigChanges.value.set(item.id, currentConfig || null);
    } else {
        pendingConfigChanges.value.set(item.id, null);
    }
};

const cancelEditConfig = (item: CartItem) => {
    editingConfigItemId.value = null;
    pendingConfigChanges.value.delete(item.id);
};

const selectConfiguration = (item: CartItem, config: Configuration | null) => {
    // Doar actualizează configurația pending, fără să salveze
    pendingConfigChanges.value.set(item.id, config);
};

const saveConfiguration = async (item: CartItem) => {
    const pendingConfig = pendingConfigChanges.value.get(item.id);
    await updateConfiguration(item, pendingConfig || null);
    // Păstrează editorul deschis
    // editingConfigItemId.value rămâne item.id
};

const hasUnsavedChanges = computed(() => {
    return pendingConfigChanges.value.size > 0;
});

const getPendingConfig = (item: CartItem): Configuration | null => {
    return pendingConfigChanges.value.get(item.id) || null;
};

const fetchCart = async () => {
    try {
        const response = await fetch('/cart/data');
        cart.value = await response.json();
    } catch (error) {
        console.error('Error fetching cart:', error);
    }
};

const updateQuantity = async (item: CartItem, quantity: number) => {
    if (quantity < 1) {
        return;
    }

    loading.value = true;
    try {
        const response = await fetch('/cart/update', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
            body: JSON.stringify({
                product_id: item.id,
                quantity: quantity,
                print_size: item.print_size,
                print_sides: item.print_sides,
                configuration_quantity: item.configuration_quantity,
                old_print_size: item.print_size,
                old_print_sides: item.print_sides,
                old_configuration_quantity: item.configuration_quantity,
            }),
        });

        if (response.ok) {
            await fetchCart();
        } else {
            const data = await response.json();
            alert(data.message || t('error_updating_quantity'));
        }
    } catch (error) {
        console.error('Error updating quantity:', error);
        alert(t('error_updating_quantity'));
    } finally {
        loading.value = false;
    }
};

const updateConfiguration = async (item: CartItem, config: Configuration | null) => {
    loading.value = true;
    try {
        const response = await fetch('/cart/update', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
            body: JSON.stringify({
                product_id: item.id,
                quantity: item.quantity,
                print_size: config?.print_size || null,
                print_sides: config?.print_sides || null,
                format: config?.format || null,
                suport: config?.suport || null,
                culoare: config?.culoare || null,
                colturi: config?.colturi || null,
                configuration_quantity: config?.quantity || null,
                old_print_size: item.print_size,
                old_print_sides: item.print_sides,
                old_format: item.format,
                old_suport: item.suport,
                old_culoare: item.culoare,
                old_colturi: item.colturi,
                old_configuration_quantity: item.configuration_quantity,
            }),
        });

        if (response.ok) {
            await fetchCart();
            editingConfigItemId.value = null;
            pendingConfigChanges.value.delete(item.id);
        } else {
            const data = await response.json();
            alert(data.message || t('error_updating_quantity'));
        }
    } catch (error) {
        console.error('Error updating configuration:', error);
        alert(t('error_updating_quantity'));
    } finally {
        loading.value = false;
    }
};

const removeItem = async (item: CartItem) => {
    if (!confirm(t('remove_item_confirm'))) {
        return;
    }

    loading.value = true;
    try {
        // Construiește URL-ul cu parametri pentru configurații dacă există
        let url = `/cart/remove/${item.id}`;
        const params = new URLSearchParams();
        
        if (item.print_size && item.print_sides && item.configuration_quantity) {
            params.append('print_size', item.print_size);
            params.append('print_sides', item.print_sides);
            params.append('configuration_quantity', item.configuration_quantity.toString());
            if (item.format) params.append('format', item.format);
            if (item.suport) params.append('suport', item.suport);
            if (item.culoare) params.append('culoare', item.culoare);
            if (item.colturi) params.append('colturi', item.colturi);
            url += '?' + params.toString();
        }

        const response = await fetch(url, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
        });

        if (response.ok) {
            await fetchCart();
        } else {
            const data = await response.json().catch(() => ({}));
            alert(data.message || t('error_removing_item'));
        }
    } catch (error) {
        console.error('Error removing item:', error);
        alert(t('error_removing_item'));
    } finally {
        loading.value = false;
    }
};

const formatPrice = (price: number) => {
    return new Intl.NumberFormat('ro-MD', {
        style: 'currency',
        currency: 'MDL',
    }).format(price);
};

onMounted(async () => {
    await fetchCart();
    if (cart.value.items.length > 0 && cart.value.meets_minimum_order === false) {
        showMinimumOrderDialog.value = true;
    }
});
</script>

<template>
    <Head :title="t('cart_title')" />
    <div class="flex min-h-screen flex-col bg-gray-50 dark:bg-gray-900">
        <PublicHeader />

        <main class="flex-1">
            <div class="mx-auto max-w-7xl px-4 py-6 md:px-6">
                <div class="mb-6 flex items-center gap-4">
                    <Link
                        href="/"
                        class="inline-flex items-center text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100"
                    >
                        <ArrowLeft class="mr-2 h-4 w-4" />
                        {{ t('back_to_products') }}
                    </Link>
                </div>

                <h1 class="mb-6 text-3xl font-bold text-gray-900 dark:text-white">
                    {{ t('cart_title') }}
                </h1>

                <div
                    v-if="flashError"
                    class="mb-6 rounded-lg bg-amber-50 p-4 text-amber-800 dark:bg-amber-900/20 dark:text-amber-200"
                >
                    {{ flashError }}
                </div>

                <div
                    v-if="cart.items.length === 0"
                    class="flex flex-col items-center justify-center py-12"
                >
                    <ShoppingCart class="mb-4 h-16 w-16 text-gray-400" />
                    <p class="mb-4 text-lg text-gray-500 dark:text-gray-400">
                        {{ t('cart_empty') }}
                    </p>
                    <Link href="/">
                        <Button class="bg-gray-200 border border-gray-300 text-gray-900 hover:bg-gray-300 dark:bg-gray-800 dark:border-gray-700 dark:text-white dark:hover:bg-gray-700">
                            {{ t('continue_shopping') }}
                        </Button>
                    </Link>
                </div>

                <div
                    v-else
                    class="grid grid-cols-1 gap-6 lg:grid-cols-3"
                >
                    <!-- Cart Items -->
                    <div class="lg:col-span-2 space-y-4">
                        <Card
                            v-for="(item, cartIndex) in cart.items"
                            :key="item.id + '-' + (item.print_size ?? '') + '-' + (item.configuration_quantity ?? '')"
                        >
                            <CardContent class="p-4">
                                <div class="flex gap-4">
                                    <Link
                                        :href="`/products/${item.slug}`"
                                        class="flex-shrink-0"
                                    >
                                        <img
                                            :src="item.image"
                                            :alt="item.name"
                                            class="h-24 w-24 rounded-lg object-cover"
                                        />
                                    </Link>
                                    <div class="flex flex-1 flex-col justify-between">
                                        <div>
                                            <Link
                                                :href="`/products/${item.slug}`"
                                                class="text-lg font-semibold text-gray-900 hover:underline dark:text-white"
                                            >
                                                {{ item.name }}
                                            </Link>
                                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                                {{ formatPrice(item.price) }} {{ t('per_item') }}
                                            </p>
                                            
                                            <!-- Configurații curente -->
                                            <div
                                                v-if="item.print_size || item.print_sides || item.configuration_quantity || item.format || item.suport || item.culoare || item.colturi"
                                                class="mt-2 space-y-1 text-xs text-gray-500 dark:text-gray-400"
                                            >
                                                <p v-if="item.print_size">
                                                    {{ t('print_size') }}: {{ getSizeLabel(item.print_size) }}
                                                </p>
                                                <p v-if="item.print_sides">
                                                    {{ t('print_sides') }}: {{ getSidesLabel(item.print_sides) }}
                                                </p>
                                                <p v-if="item.format">
                                                    {{ t('format') }}: {{ item.format }}
                                                </p>
                                                <p v-if="item.suport">
                                                    {{ t('suport') || 'Suport' }}: {{ item.suport }}
                                                </p>
                                                <p v-if="item.culoare">
                                                    {{ t('culoare') || 'Culoare' }}: {{ item.culoare }}
                                                </p>
                                                <p v-if="item.colturi">
                                                    {{ t('colturi') || 'Colturi' }}: {{ item.colturi }}
                                                </p>
                                                <p v-if="item.configuration_quantity">
                                                    {{ t('circulation_pieces') }}: {{ item.configuration_quantity }}
                                                </p>
                                            </div>
                                            
                                            <!-- Buton pentru editare configurație -->
                                            <Button
                                                v-if="item.configurations && item.configurations.length > 0"
                                                variant="ghost"
                                                size="sm"
                                                class="mt-2 text-xs"
                                                @click="startEditConfig(item)"
                                            >
                                                <Edit2 class="mr-1 h-3 w-3" />
                                                {{ editingConfigItemId === item.id ? t('cancel') : t('edit') + ' ' + t('select_print_characteristics') }}
                                            </Button>
                                        </div>
                                        
                                        <!-- Editor configurație -->
                                        <div
                                            v-if="editingConfigItemId === item.id && item.configurations && item.configurations.length > 0"
                                            class="mt-4 rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-900"
                                        >
                                            <h4 class="mb-3 text-sm font-semibold text-gray-900 dark:text-white">
                                                {{ t('select_print_characteristics') }}
                                            </h4>
                                            
                                            <!-- Dimensiuni -->
                                            <div v-if="getAvailableSizes(item).length > 0" class="mb-4">
                                                <label class="mb-2 block text-xs font-medium text-gray-700 dark:text-gray-300">
                                                    {{ t('sizes_in_unfolded_view') }}
                                                </label>
                                                <div class="grid grid-cols-2 gap-2">
                                                    <button
                                                        v-for="size in getAvailableSizes(item)"
                                                        :key="size"
                                                        type="button"
                                                        :class="[
                                                            'rounded-lg border-2 p-2 text-xs transition-all',
                                                            (getPendingConfig(item)?.print_size || item.print_size) === size
                                                                ? 'border-green-500 bg-green-50 dark:bg-green-900/20'
                                                                : 'border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800'
                                                        ]"
                                                        @click="() => {
                                                            const currentConfig = getPendingConfig(item);
                                                            const newSize = size;
                                                            const currentSides = currentConfig?.print_sides || item.print_sides;
                                                            const currentQty = currentConfig?.quantity || item.configuration_quantity;
                                                            // Caută configurația care se potrivește sau prima disponibilă pentru noua dimensiune
                                                            const matchingConfig = item.configurations?.find(c => 
                                                                c.print_size === newSize && 
                                                                c.print_sides === currentSides && 
                                                                c.quantity === currentQty
                                                            ) || item.configurations?.find(c => c.print_size === newSize && c.print_sides === currentSides) || null;
                                                            selectConfiguration(item, matchingConfig);
                                                        }"
                                                    >
                                                        {{ getSizeLabel(size) }}
                                                    </button>
                                                </div>
                                            </div>
                                            
                                            <!-- Laturi -->
                                            <div v-if="(getPendingConfig(item)?.print_size || item.print_size) && getAvailableSides(item, getPendingConfig(item)?.print_size || item.print_size || '').length > 0" class="mb-4">
                                                <label class="mb-2 block text-xs font-medium text-gray-700 dark:text-gray-300">
                                                    {{ t('print_sides') }}
                                                </label>
                                                <div class="grid grid-cols-2 gap-2">
                                                    <button
                                                        v-for="sides in getAvailableSides(item, getPendingConfig(item)?.print_size || item.print_size || '')"
                                                        :key="sides"
                                                        type="button"
                                                        :class="[
                                                            'rounded-lg border-2 p-2 text-xs transition-all',
                                                            (getPendingConfig(item)?.print_sides || item.print_sides) === sides
                                                                ? 'border-green-500 bg-green-50 dark:bg-green-900/20'
                                                                : 'border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800'
                                                        ]"
                                                        @click="() => {
                                                            const currentConfig = getPendingConfig(item);
                                                            const currentSize = currentConfig?.print_size || item.print_size;
                                                            const newSides = sides;
                                                            const currentQty = currentConfig?.quantity || item.configuration_quantity;
                                                            const matchingConfig = item.configurations?.find(c => 
                                                                c.print_size === currentSize && 
                                                                c.print_sides === newSides && 
                                                                c.quantity === currentQty
                                                            ) || item.configurations?.find(c => c.print_size === currentSize && c.print_sides === newSides) || null;
                                                            selectConfiguration(item, matchingConfig);
                                                        }"
                                                    >
                                                        {{ getSidesLabel(sides) }}
                                                    </button>
                                                </div>
                                            </div>
                                            
                                            <!-- Tiraj -->
                                            <div v-if="(getPendingConfig(item)?.print_size || item.print_size) && (getPendingConfig(item)?.print_sides || item.print_sides) && getAvailableQuantities(item, getPendingConfig(item)?.print_size || item.print_size || '', getPendingConfig(item)?.print_sides || item.print_sides || '').length > 0" class="mb-4">
                                                <label class="mb-2 block text-xs font-medium text-gray-700 dark:text-gray-300">
                                                    {{ t('select_quantity') }}
                                                </label>
                                                <div class="space-y-2">
                                                    <button
                                                        v-for="config in getAvailableQuantities(item, getPendingConfig(item)?.print_size || item.print_size || '', getPendingConfig(item)?.print_sides || item.print_sides || '')"
                                                        :key="config.id"
                                                        type="button"
                                                        :class="[
                                                            'w-full rounded-lg border-2 p-3 text-left transition-all',
                                                            (getPendingConfig(item)?.quantity || item.configuration_quantity) === config.quantity
                                                                ? 'border-green-500 bg-green-50 dark:bg-green-900/20'
                                                                : 'border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800'
                                                        ]"
                                                        @click="selectConfiguration(item, config)"
                                                    >
                                                        <div class="flex items-center justify-between">
                                                            <div>
                                                                <div class="font-medium text-gray-900 dark:text-white">
                                                                    {{ config.quantity }} {{ t('pieces') }}
                                                                </div>
                                                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                                                    {{ config.formatted_effective_price_per_unit ?? config.formatted_price_per_unit }} {{ t('per_piece') }}
                                                                    <span v-if="config.price_coefficient && config.price_coefficient !== 1" class="text-gray-400">(×{{ config.price_coefficient }})</span>
                                                                </div>
                                                            </div>
                                                            <div class="text-right">
                                                                <div class="font-semibold text-gray-900 dark:text-white">
                                                                    {{ config.formatted_effective_price ?? config.formatted_price }}
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </button>
                                                </div>
                                            </div>
                                            
                                            <div class="flex gap-2">
                                                <Button
                                                    variant="outline"
                                                    size="sm"
                                                    class="flex-1"
                                                    @click="cancelEditConfig(item)"
                                                >
                                                    {{ t('cancel') }}
                                                </Button>
                                                <Button
                                                    size="sm"
                                                    class="flex-1"
                                                    :disabled="loading || !getPendingConfig(item)"
                                                    @click="saveConfiguration(item)"
                                                >
                                                    {{ t('save') }}
                                                </Button>
                                            </div>
                                        </div>

                                        <!-- Maketă: încarcă fișier sau elaborare -->
                                        <div class="mt-4 flex flex-wrap items-center gap-3 rounded-lg border border-gray-200 bg-gray-50 p-3 dark:border-gray-700 dark:bg-gray-800/50">
                                            <div class="flex items-center gap-2">
                                                <input
                                                    :id="`mockup-input-${cartIndex}`"
                                                    type="file"
                                                    class="hidden"
                                                    accept="*/*"
                                                    @change="onMockupFileSelect(item, $event)"
                                                />
                                                <Button
                                                    type="button"
                                                    size="sm"
                                                    variant="default"
                                                    class="bg-green-600 hover:bg-green-700"
                                                    :disabled="loading || mockupUploadingForId === item.id"
                                                    @click="triggerMockupInput(cartIndex)"
                                                >
                                                    <Upload class="mr-1.5 h-4 w-4" />
                                                    {{ mockupUploadingForId === item.id ? t('loading') : t('upload_personal_mockup') }}
                                                </Button>
                                                <span v-if="item.mockup_filename" class="flex items-center gap-1 text-xs text-gray-600 dark:text-gray-400">
                                                    <FileCheck class="h-3.5 w-3.5" />
                                                    {{ item.mockup_filename }}
                                                </span>
                                            </div>
                                            <label class="flex cursor-pointer items-center gap-2">
                                                <input
                                                    type="checkbox"
                                                    :checked="!!item.elaborate_mockup"
                                                    :disabled="loading"
                                                    class="h-4 w-4 rounded border-gray-300"
                                                    @change="toggleElaborateMockup(item, ($event.target as HTMLInputElement).checked)"
                                                />
                                                <span class="text-sm text-gray-700 dark:text-gray-300">{{ t('elaborate_mockup') }}</span>
                                                <span class="text-sm font-medium text-gray-600 dark:text-gray-400">+{{ item.elaborate_mockup_price ?? defaultElaborateMockupPrice }} MDL</span>
                                            </label>
                                        </div>

                                        <div class="mt-4 flex items-center justify-between">
                                            <div class="flex items-center gap-2">
                                                <Button
                                                    variant="outline"
                                                    size="icon"
                                                    :disabled="item.quantity <= 1 || loading"
                                                    class="bg-gray-200 border border-gray-300 text-gray-900 hover:bg-gray-300 dark:bg-gray-800 dark:border-gray-700 dark:text-white dark:hover:bg-gray-700"
                                                    @click="updateQuantity(item, item.quantity - 1)"
                                                >
                                                    <Minus class="h-4 w-4" />
                                                </Button>
                                                <span class="w-12 text-center font-medium">
                                                    {{ item.quantity }}
                                                </span>
                                                <Button
                                                    variant="outline"
                                                    size="icon"
                                                    :disabled="item.quantity >= item.stock_quantity || loading"
                                                    class="bg-gray-200 border border-gray-300 text-gray-900 hover:bg-gray-300 dark:bg-gray-800 dark:border-gray-700 dark:text-white dark:hover:bg-gray-700"
                                                    @click="updateQuantity(item, item.quantity + 1)"
                                                >
                                                    <Plus class="h-4 w-4" />
                                                </Button>
                                            </div>
                                            <div class="flex items-center gap-4">
                                                <span class="text-lg font-bold text-gray-900 dark:text-white">
                                                    {{ formatPrice(item.subtotal) }}
                                                </span>
                                                <Button
                                                    variant="ghost"
                                                    size="icon"
                                                    :disabled="loading"
                                                    class="bg-gray-200 border border-gray-300 text-gray-900 hover:bg-gray-300 dark:bg-gray-800 dark:border-gray-700 dark:text-white dark:hover:bg-gray-700"
                                                    @click="removeItem(item)"
                                                >
                                                    <Trash2 class="h-4 w-4 text-red-500" />
                                                </Button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                    <!-- Order Summary -->
                    <div class="lg:col-span-1">
                        <Card class="sticky top-24">
                            <CardHeader>
                                <CardTitle>{{ t('order_summary') }}</CardTitle>
                            </CardHeader>
                            <CardContent class="space-y-4">
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600 dark:text-gray-400">
                                        {{ t('subtotal') }}
                                    </span>
                                    <span class="font-medium">
                                        {{ formatPrice(cart.total) }}
                                    </span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600 dark:text-gray-400">
                                        {{ t('shipping') }}
                                    </span>
                                    <span class="font-medium">
                                        {{ cart.total > 500 ? t('free') : formatPrice(50) }}
                                    </span>
                                </div>
                                <div class="pt-4">
                                    <div class="flex justify-between text-lg font-bold">
                                        <span>{{ t('total') }}</span>
                                        <span>
                                            {{ formatPrice(cart.total + (cart.total > 500 ? 0 : 50) + cart.total * 0.19) }}
                                        </span>
                                    </div>
                                    <p class="mt-1 text-xs text-gray-500">
                                        {{ t('vat_included') }}
                                    </p>
                                </div>
                                <Link
                                    v-if="cart.meets_minimum_order !== false"
                                    href="/checkout"
                                    class="block"
                                >
                                    <Button
                                        class="w-full bg-gray-200 border border-gray-300 text-gray-900 hover:bg-gray-300 dark:bg-gray-800 dark:border-gray-700 dark:text-white dark:hover:bg-gray-700"
                                        size="lg"
                                        :disabled="hasUnsavedChanges"
                                    >
                                        {{ t('complete_order') }}
                                    </Button>
                                </Link>
                                <div
                                    v-else
                                    class="block"
                                >
                                    <Button
                                        class="w-full bg-gray-200 border border-gray-300 text-gray-900 dark:bg-gray-800 dark:border-gray-700 dark:text-white dark:hover:bg-gray-700 cursor-not-allowed opacity-60"
                                        size="lg"
                                        disabled
                                    >
                                        {{ t('complete_order') }}
                                    </Button>
                                </div>
                                <p
                                    v-if="hasUnsavedChanges"
                                    class="mt-2 text-xs text-amber-600 dark:text-amber-400 text-center"
                                >
                                    {{ t('save_changes_before_checkout') }}
                                </p>
                                <Link
                                    href="/"
                                    class="block"
                                >
                                    <Button
                                        variant="outline"
                                        class="w-full bg-gray-200 border border-gray-300 text-gray-900 hover:bg-gray-300 dark:bg-gray-800 dark:border-gray-700 dark:text-white dark:hover:bg-gray-700"
                                    >
                                        {{ t('continue_shopping') }}
                                    </Button>
                                </Link>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </main>

        <AppFooter />

        <!-- Dialog comandă minimă -->
        <Dialog v-model:open="showMinimumOrderDialog">
            <DialogContent class="max-w-md">
                <div class="flex flex-col items-center text-center space-y-4">
                    <div class="flex items-center justify-center w-14 h-14 rounded-full bg-amber-100 dark:bg-amber-900/30">
                        <AlertCircle class="w-8 h-8 text-amber-600 dark:text-amber-400" />
                    </div>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                        {{ t('minimum_order_title') }}
                    </h2>
                    <p class="text-gray-600 dark:text-gray-400">
                        {{ t('minimum_order_message', { amount: cart.minimum_order_amount ?? 300 }) }}
                    </p>
                    <Button
                        class="w-full"
                        @click="showMinimumOrderDialog = false"
                    >
                        {{ t('understood_thanks') }}
                    </Button>
                </div>
            </DialogContent>
        </Dialog>
    </div>
</template>

