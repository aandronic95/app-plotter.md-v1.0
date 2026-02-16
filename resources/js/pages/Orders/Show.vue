<script setup lang="ts">
import AppFooter from '@/components/AppFooter.vue';
import PublicHeader from '@/components/PublicHeader.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { CheckCircle, ArrowLeft, X, AlertCircle } from 'lucide-vue-next';
import { computed, ref, watch, onMounted } from 'vue';
import { useTranslations } from '@/composables/useTranslations';

interface OrderItem {
    id: number;
    product_name: string;
    product_sku?: string;
    print_size?: string | null;
    print_sides?: string | null;
    format?: string | null;
    suport?: string | null;
    culoare?: string | null;
    colturi?: string | null;
    configuration_quantity?: number | null;
    quantity: number;
    price: number;
    subtotal: number;
}

interface Order {
    id: number;
    order_number: string;
    status: string;
    payment_status: string;
    subtotal: number;
    tax: number;
    shipping_cost: number;
    total: number;
    shipping_name: string;
    shipping_email: string;
    shipping_phone: string;
    shipping_address: string;
    shipping_city: string;
    shipping_postal_code?: string;
    shipping_country: string;
    delivery_method?: {
        id: number;
        name: string;
        logo?: string | null;
    } | null;
    delivery_tracking_number?: string | null;
    notes?: string;
    created_at: string;
    items: OrderItem[];
}

interface Props {
    order: Order;
}

const props = defineProps<Props>();
const page = usePage();
const { t } = useTranslations();

// Flash messagesRR
const flash = computed(() => page.props.flash as { success?: string; error?: string } | undefined);
const showSuccessMessage = ref(false);
const showErrorMessage = ref(false);
const successMessage = ref('');
const errorMessage = ref('');

// Watch for flash messages
watch(flash, (newFlash) => {
    if (newFlash?.success) {
        successMessage.value = newFlash.success;
        showSuccessMessage.value = true;
        setTimeout(() => {
            showSuccessMessage.value = false;
        }, 5000);
    }
    if (newFlash?.error) {
        errorMessage.value = newFlash.error;
        showErrorMessage.value = true;
        setTimeout(() => {
            showErrorMessage.value = false;
        }, 5000);
    }
}, { immediate: true });

// Also check on mount
onMounted(() => {
    if (flash.value?.success) {
        successMessage.value = flash.value.success;
        showSuccessMessage.value = true;
        setTimeout(() => {
            showSuccessMessage.value = false;
        }, 5000);
    }
    if (flash.value?.error) {
        errorMessage.value = flash.value.error;
        showErrorMessage.value = true;
        setTimeout(() => {
            showErrorMessage.value = false;
        }, 5000);
    }
});

const formatPrice = (price: number) => {
    return new Intl.NumberFormat('ro-MD', {
        style: 'currency',
        currency: 'MDL',
    }).format(price);
};

const getStatusLabel = (status: string) => {
    const labels: Record<string, string> = {
        pending: 'În așteptare',
        processing: 'În procesare',
        shipped: 'Expediată',
        delivered: 'Livrată',
        cancelled: 'Anulată',
    };
    return labels[status] || status;
};


const getPaymentStatusLabel = (status: string) => {
    const labels: Record<string, string> = {
        pending: 'În așteptare',
        paid: 'Plătită',
        failed: 'Eșuată',
        refunded: 'Rambursată',
    };
    return labels[status] || status;
};
</script>

<template>
    <Head :title="`Comandă ${props.order.order_number}`" />
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
                        Înapoi la produse
                    </Link>
                </div>

                <!-- Success Message -->
                <div
                    v-if="showSuccessMessage"
                    class="mb-6 relative rounded-lg bg-green-50 p-4 dark:bg-green-900/20"
                >
                    <div class="flex items-start gap-3">
                        <CheckCircle class="h-5 w-5 text-green-600 dark:text-green-400 mt-0.5" />
                        <div class="flex-1">
                            <h4 class="text-sm font-semibold text-green-800 dark:text-green-200 mb-1">
                                Succes!
                            </h4>
                            <p class="text-sm text-green-700 dark:text-green-300">
                                {{ successMessage }}
                            </p>
                        </div>
                        <button
                            @click="showSuccessMessage = false"
                            class="text-green-600 hover:text-green-800 dark:text-green-400 dark:hover:text-green-200"
                        >
                            <X class="h-4 w-4" />
                        </button>
                    </div>
                </div>

                <!-- Error Message -->
                <div
                    v-if="showErrorMessage"
                    class="mb-6 relative rounded-lg bg-red-50 p-4 dark:bg-red-900/20"
                >
                    <div class="flex items-start gap-3">
                        <AlertCircle class="h-5 w-5 text-red-600 dark:text-red-400 mt-0.5" />
                        <div class="flex-1">
                            <h4 class="text-sm font-semibold text-red-800 dark:text-red-200 mb-1">
                                Eroare
                            </h4>
                            <p class="text-sm text-red-700 dark:text-red-300">
                                {{ errorMessage }}
                            </p>
                        </div>
                        <button
                            @click="showErrorMessage = false"
                            class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-200"
                        >
                            <X class="h-4 w-4" />
                        </button>
                    </div>
                </div>

                <div class="mb-6">
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                        Comandă {{ props.order.order_number }}
                    </h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">
                        Plasată pe {{ props.order.created_at }}
                    </p>
                </div>

                <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                    <!-- Order Details -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Order Items -->
                        <Card>
                            <CardHeader>
                                <CardTitle>Produse comandate</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div class="space-y-4">
                                    <div
                                        v-for="item in props.order.items"
                                        :key="item.id"
                                        class="rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800"
                                    >
                                        <div class="flex items-start justify-between">
                                            <div class="flex-1">
                                                <p class="font-semibold text-gray-900 dark:text-white">
                                                    {{ item.product_name }}
                                                </p>
                                                <p
                                                    v-if="item.product_sku"
                                                    class="mt-1 text-sm text-gray-500 dark:text-gray-400"
                                                >
                                                    SKU: {{ item.product_sku }}
                                                </p>
                                                
                                                <!-- Configurații produs -->
                                                <div
                                                    v-if="item.print_size || item.print_sides || item.format || item.suport || item.culoare || item.colturi || item.configuration_quantity"
                                                    class="mt-4 space-y-4"
                                                >
                                                    <!-- Section 1: Caracteristici de printare -->
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
                                                            <!-- Dimensiuni -->
                                                            <div v-if="item.print_size">
                                                                <h4 class="mb-3 text-sm font-medium text-gray-700 dark:text-gray-300">
                                                                    {{ t('sizes_in_unfolded_view') }}
                                                                </h4>
                                                                <div class="grid grid-cols-2 gap-4">
                                                                    <div
                                                                        class="relative rounded-lg border-2 border-green-500 bg-green-50 p-4 text-left dark:bg-green-900/20"
                                                                    >
                                                                        <div class="flex items-center justify-center mb-2">
                                                                            <div class="h-16 w-24 rounded border-2 border-dashed border-green-500">
                                                                                <div
                                                                                    v-if="item.print_size === 'A3'"
                                                                                    class="h-full w-full flex items-center justify-center"
                                                                                >
                                                                                    <div class="h-full w-1/3 border-r-2 border-dashed border-gray-400"></div>
                                                                                    <div class="h-full w-1/3 border-r-2 border-dashed border-gray-400"></div>
                                                                                    <div class="h-full w-1/3"></div>
                                                                                </div>
                                                                                <div
                                                                                    v-else-if="item.print_size === 'A4'"
                                                                                    class="h-full w-full flex items-center justify-center"
                                                                                >
                                                                                    <div class="h-full w-1/2 border-r-2 border-dashed border-gray-400"></div>
                                                                                    <div class="h-full w-1/2"></div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <p class="text-sm font-medium text-gray-900 dark:text-white">
                                                                            {{ item.print_size === 'A3' ? t('print_size_a3') : (item.print_size === 'A4' ? t('print_size_a4') : item.print_size) }}
                                                                        </p>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <!-- Laturi de printare -->
                                                            <div v-if="item.print_sides">
                                                                <h4 class="mb-3 text-sm font-medium text-gray-700 dark:text-gray-300">
                                                                    {{ t('print_sides') }}
                                                                </h4>
                                                                <div class="grid grid-cols-2 gap-4">
                                                                    <div
                                                                        class="relative rounded-lg border-2 border-green-500 bg-green-50 p-4 text-left dark:bg-green-900/20"
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
                                                                                    item.print_sides === '4+0'
                                                                                        ? 'border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-900'
                                                                                        : 'border-gray-300 dark:border-gray-600'
                                                                                ]"
                                                                            >
                                                                                <div v-if="item.print_sides === '4+4'" class="flex gap-0.5">
                                                                                    <div class="h-2 w-2 rounded-full bg-cyan-500"></div>
                                                                                    <div class="h-2 w-2 rounded-full bg-magenta-500"></div>
                                                                                    <div class="h-2 w-2 rounded-full bg-yellow-500"></div>
                                                                                    <div class="h-2 w-2 rounded-full bg-black dark:bg-white"></div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <p class="text-sm font-medium text-gray-900 dark:text-white">
                                                                            {{ item.print_sides === '4+0' ? t('print_sides_one_sided') : (item.print_sides === '4+4' ? t('print_sides_two_sided') : item.print_sides) }}
                                                                        </p>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            
                                                            <!-- Format -->
                                                            <div v-if="item.format">
                                                                <h4 class="mb-3 text-sm font-medium text-gray-700 dark:text-gray-300">
                                                                    {{ t('format') }}
                                                                </h4>
                                                                <div class="rounded-lg border-2 border-green-500 bg-green-50 p-3 dark:bg-green-900/20">
                                                                    <p class="text-sm font-medium text-gray-900 dark:text-white">
                                                                        {{ item.format }}
                                                                    </p>
                                                                </div>
                                                            </div>
                                                            
                                                            <!-- Suport -->
                                                            <div v-if="item.suport">
                                                                <h4 class="mb-3 text-sm font-medium text-gray-700 dark:text-gray-300">
                                                                    {{ t('suport') || 'Suport' }}
                                                                </h4>
                                                                <div class="rounded-lg border-2 border-green-500 bg-green-50 p-3 dark:bg-green-900/20">
                                                                    <p class="text-sm font-medium text-gray-900 dark:text-white">
                                                                        {{ item.suport }}
                                                                    </p>
                                                                </div>
                                                            </div>
                                                            
                                                            <!-- Culoare -->
                                                            <div v-if="item.culoare">
                                                                <h4 class="mb-3 text-sm font-medium text-gray-700 dark:text-gray-300">
                                                                    {{ t('culoare') || 'Culoare' }}
                                                                </h4>
                                                                <div class="rounded-lg border-2 border-green-500 bg-green-50 p-3 dark:bg-green-900/20">
                                                                    <p class="text-sm font-medium text-gray-900 dark:text-white">
                                                                        {{ item.culoare }}
                                                                    </p>
                                                                </div>
                                                            </div>
                                                            
                                                            <!-- Colturi -->
                                                            <div v-if="item.colturi">
                                                                <h4 class="mb-3 text-sm font-medium text-gray-700 dark:text-gray-300">
                                                                    {{ t('colturi') || 'Colturi' }}
                                                                </h4>
                                                                <div class="rounded-lg border-2 border-green-500 bg-green-50 p-3 dark:bg-green-900/20">
                                                                    <p class="text-sm font-medium text-gray-900 dark:text-white">
                                                                        {{ item.colturi }}
                                                                    </p>
                                                                </div>
                                                            </div>
                                                        </CardContent>
                                                    </Card>

                                                    <!-- Section 2: Tiraj -->
                                                    <Card v-if="item.configuration_quantity">
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
                                                                        <tr class="bg-green-50 dark:bg-green-900/20 border-green-500 border-2">
                                                                            <td class="px-4 py-4 text-sm font-medium text-gray-900 dark:text-white">
                                                                                {{ item.configuration_quantity }}
                                                                            </td>
                                                                            <td class="px-4 py-4 text-sm text-gray-600 dark:text-gray-400">
                                                                                <!-- Calculăm data de producție aproximativă (3-5 zile) -->
                                                                                {{ new Date(Date.now() + 5 * 24 * 60 * 60 * 1000).toLocaleDateString('ro-RO', { day: '2-digit', month: '2-digit', year: 'numeric' }) }}
                                                                            </td>
                                                                            <td class="px-4 py-4 text-sm text-gray-900 dark:text-white">
                                                                                <div class="font-semibold">{{ formatPrice(item.subtotal) }}</div>
                                                                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                                                                    {{ formatPrice(item.subtotal / item.configuration_quantity) }} {{ t('per_piece') }}
                                                                                </div>
                                                                            </td>
                                                                        </tr>
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </CardContent>
                                                    </Card>
                                                </div>
                                            </div>
                                            <div class="ml-4 text-right">
                                                <p class="text-sm font-medium text-gray-900 dark:text-white">
                                                    {{ item.quantity }} x {{ formatPrice(item.price) }}
                                                </p>
                                                <p class="mt-1 text-base font-bold text-gray-900 dark:text-white">
                                                    {{ formatPrice(item.subtotal) }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        <!-- Delivery Method -->
                        <Card v-if="props.order.delivery_method">
                            <CardHeader>
                                <CardTitle>Metodă de livrare</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div class="flex items-center gap-3">
                                    <img
                                        v-if="props.order.delivery_method.logo"
                                        :src="props.order.delivery_method.logo"
                                        :alt="props.order.delivery_method.name"
                                        class="h-12 w-12 object-contain rounded"
                                    />
                                    <div>
                                        <p class="font-medium">{{ props.order.delivery_method.name }}</p>
                                        <p
                                            v-if="props.order.delivery_tracking_number"
                                            class="text-sm text-gray-600 dark:text-gray-400 mt-1"
                                        >
                                            AWB / Tracking: <span class="font-mono">{{ props.order.delivery_tracking_number }}</span>
                                        </p>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        <!-- Shipping Address -->
                        <Card>
                            <CardHeader>
                                <CardTitle>Adresă de livrare</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div class="space-y-1">
                                    <p class="font-medium">{{ props.order.shipping_name }}</p>
                                    <p class="text-gray-600 dark:text-gray-400">
                                        {{ props.order.shipping_address }}
                                    </p>
                                    <p class="text-gray-600 dark:text-gray-400">
                                        {{ props.order.shipping_city }}
                                        <span v-if="props.order.shipping_postal_code">
                                            , {{ props.order.shipping_postal_code }}
                                        </span>
                                    </p>
                                    <p class="text-gray-600 dark:text-gray-400">
                                        {{ props.order.shipping_country }}
                                    </p>
                                    <p class="mt-2 text-gray-600 dark:text-gray-400">
                                        {{ props.order.shipping_phone }}
                                    </p>
                                    <p class="text-gray-600 dark:text-gray-400">
                                        {{ props.order.shipping_email }}
                                    </p>
                                </div>
                            </CardContent>
                        </Card>

                        <Card
                            v-if="props.order.notes"
                        >
                            <CardHeader>
                                <CardTitle>Note</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <p class="text-gray-700 dark:text-gray-300">
                                    {{ props.order.notes }}
                                </p>
                            </CardContent>
                        </Card>
                    </div>

                    <!-- Order Summary -->
                    <div class="lg:col-span-1">
                        <Card class="sticky top-24">
                            <CardHeader>
                                <CardTitle>Rezumat comandă</CardTitle>
                            </CardHeader>
                            <CardContent class="space-y-4">
                                <div>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                        Status comandă
                                    </p>
                                    <p class="font-medium">
                                        {{ getStatusLabel(props.order.status) }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                        Status plată
                                    </p>
                                    <p class="font-medium">
                                        {{ getPaymentStatusLabel(props.order.payment_status) }}
                                    </p>
                                </div>
                                <div class="pt-4 space-y-2">
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600 dark:text-gray-400">
                                            Subtotal
                                        </span>
                                        <span class="font-medium">
                                            {{ formatPrice(props.order.subtotal) }}
                                        </span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600 dark:text-gray-400">
                                            TVA (19%)
                                        </span>
                                        <span class="font-medium">
                                            {{ formatPrice(props.order.tax) }}
                                        </span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600 dark:text-gray-400">
                                            Transport
                                        </span>
                                        <span class="font-medium">
                                            {{ props.order.shipping_cost === 0 ? 'Gratuit' : formatPrice(props.order.shipping_cost) }}
                                        </span>
                                    </div>
                                    <div class="pt-2">
                                        <div class="flex justify-between text-lg font-bold">
                                            <span>Total</span>
                                            <span>{{ formatPrice(props.order.total) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </main>

        <AppFooter />
    </div>
</template>

