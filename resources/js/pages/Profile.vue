<script setup lang="ts">
import AppFooter from '@/components/AppFooter.vue';
import PublicHeader from '@/components/PublicHeader.vue';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { User, Package, Gift, Edit2, Save, X, MapPin, Phone, Mail, Plus, Trash2, Star, Heart } from 'lucide-vue-next';
import { ref, computed, onMounted } from 'vue';
import { useTranslations } from '@/composables/useTranslations';
import { useSiteSettings } from '@/composables/useSiteSettings';
import ProductCard from '@/components/ProductCard.vue';

interface UserData {
    id: number;
    name: string;
    email: string;
    loyalty_points: number;
}

interface DeliveryAddress {
    id: number;
    name?: string;
    phone: string;
    address: string;
    city: string;
    postal_code?: string;
    country: string;
    is_default: boolean;
    full_address: string;
}

interface Order {
    id: number;
    order_number: string;
    status: string;
    payment_status: string;
    total: number;
    created_at: string;
    items_count: number;
}

interface WishlistItem {
    id: number;
    product_id: number;
    product: {
        id: number;
        name: string;
        slug: string;
        price: number;
        original_price?: number;
        image: string;
        discount?: number;
        in_stock: boolean;
        stock_quantity: number;
    };
    created_at: string;
}

interface Props {
    user: UserData;
    deliveryAddresses: DeliveryAddress[];
    orders: {
        data: Order[];
        links: any[];
        meta: any;
    };
    wishlist: WishlistItem[];
}

const props = defineProps<Props>();

const { t } = useTranslations();
const { siteSettings, fetchSiteSettings } = useSiteSettings();

const showLoyaltyPoints = computed(() => {
    return siteSettings.value?.show_loyalty_points ?? true;
});

onMounted(() => {
    fetchSiteSettings();
});

const isAddingAddress = ref(false);
const editingAddressId = ref<number | null>(null);

const addressForm = useForm({
    name: '',
    phone: '',
    address: '',
    city: '',
    postal_code: '',
    country: 'Republica Moldova',
    is_default: false,
});

const formatPrice = (price: number) => {
    return new Intl.NumberFormat('ro-MD', {
        style: 'currency',
        currency: 'MDL',
    }).format(price);
};

const formatPoints = (points: number) => {
    return new Intl.NumberFormat('ro-MD').format(points);
};

const getStatusLabel = (status: string) => {
    const labels: Record<string, string> = {
        pending: t('common.status_pending'),
        processing: t('common.status_processing'),
        shipped: t('common.status_shipped'),
        delivered: t('common.status_delivered'),
        cancelled: t('common.status_cancelled'),
    };
    return labels[status] || status;
};

const getPaymentStatusLabel = (status: string) => {
    const labels: Record<string, string> = {
        pending: t('common.payment_pending'),
        paid: t('common.payment_paid'),
        failed: t('common.payment_failed'),
        refunded: t('common.payment_refunded'),
    };
    return labels[status] || status;
};

const getStatusColor = (status: string) => {
    const colors: Record<string, string> = {
        pending: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400',
        processing: 'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400',
        shipped: 'bg-purple-100 text-purple-800 dark:bg-purple-900/20 dark:text-purple-400',
        delivered: 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400',
        cancelled: 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400',
    };
    return colors[status] || 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200';
};

const startAddAddress = () => {
    addressForm.reset();
    addressForm.clearErrors();
    isAddingAddress.value = true;
    editingAddressId.value = null;
};

const startEditAddress = (address: DeliveryAddress) => {
    addressForm.name = address.name || '';
    addressForm.phone = address.phone;
    addressForm.address = address.address;
    addressForm.city = address.city;
    addressForm.postal_code = address.postal_code || '';
    addressForm.country = address.country;
    addressForm.is_default = address.is_default;
    addressForm.clearErrors();
    isAddingAddress.value = true;
    editingAddressId.value = address.id;
};

const cancelAddressForm = () => {
    addressForm.reset();
    addressForm.clearErrors();
    isAddingAddress.value = false;
    editingAddressId.value = null;
};

const submitAddressForm = () => {
    if (editingAddressId.value) {
        addressForm.put(`/profile/addresses/${editingAddressId.value}`, {
            preserveScroll: true,
            onSuccess: () => {
                cancelAddressForm();
            },
        });
    } else {
        addressForm.post('/profile/addresses', {
            preserveScroll: true,
            onSuccess: () => {
                cancelAddressForm();
            },
        });
    }
};

const deleteAddress = (id: number) => {
    if (confirm(t('common.delete_address_confirm'))) {
        router.delete(`/profile/addresses/${id}`, {
            preserveScroll: true,
        });
    }
};

const setDefaultAddress = (id: number) => {
    router.post(`/profile/addresses/${id}/set-default`, {
        preserveScroll: true,
    });
};

const removeFromWishlist = async (productId: number) => {
    if (confirm(t('common.remove_from_wishlist_confirm'))) {
        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            
            if (!csrfToken) {
                alert(t('error_csrf_missing'));
                return;
            }

            const response = await fetch(`/wishlist/${productId}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
            });

            if (!response.ok) {
                const errorData = await response.json().catch(() => ({ message: t('error_unknown') }));
                alert(errorData.message || t('common.error_removing_from_wishlist'));
                return;
            }

            // Reload page to refresh wishlist
            router.reload();
        } catch (error) {
            console.error('Error removing from wishlist:', error);
            alert(t('common.error_removing_from_wishlist'));
        }
    }
};
</script>

<template>
    <Head :title="t('common.my_profile')" />
    <div class="flex min-h-screen flex-col dark:bg-gray-900">
        <PublicHeader />

        <main class="flex-1">
            <div class="mx-auto max-w-7xl px-4 py-6 md:px-6">
                <!-- Page Header -->
                <div class="mb-6">
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ t('common.my_profile') }}</h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">
                        {{ t('common.manage_profile_description') }}
                    </p>
                </div>

                <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                    <!-- Left Column: User Info & Delivery -->
                    <div class="lg:col-span-1 space-y-6">
                        <!-- User Info Card -->
                        <Card>
                            <CardHeader>
                                <CardTitle class="flex items-center gap-2">
                                    <User class="h-5 w-5" />
                                    {{ t('common.personal_info') }}
                                </CardTitle>
                            </CardHeader>
                            <CardContent class="space-y-4">
                                <div>
                                    <Label class="text-sm text-gray-500 dark:text-gray-400">{{ t('common.name') }}</Label>
                                    <p class="mt-1 font-medium text-gray-900 dark:text-white">{{ props.user.name }}</p>
                                </div>
                                <div>
                                    <Label class="text-sm text-gray-500 dark:text-gray-400">{{ t('common.email') }}</Label>
                                    <p class="mt-1 font-medium text-gray-900 dark:text-white">{{ props.user.email }}</p>
                                </div>
                            </CardContent>
                        </Card>

                        <!-- Loyalty Points Card -->
                        <Card v-if="showLoyaltyPoints">
                            <CardHeader>
                                <CardTitle class="flex items-center gap-2">
                                    <Gift class="h-5 w-5" />
                                    {{ t('common.loyalty_points') }}
                                </CardTitle>
                                <CardDescription>
                                    {{ t('common.points_description') }}
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <div class="text-center">
                                    <div class="text-4xl font-bold text-primary">
                                        {{ formatPoints(props.user.loyalty_points) }}
                                    </div>
                                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">{{ t('common.points_accumulated') }}</p>
                                </div>
                            </CardContent>
                        </Card>

                        <!-- Delivery Addresses Card -->
                        <Card>
                            <CardHeader>
                                <div class="flex items-center justify-between">
                                    <CardTitle class="flex items-center gap-2">
                                        <MapPin class="h-5 w-5" />
                                        {{ t('common.delivery_addresses') }}
                                    </CardTitle>
                                    <Button
                                        v-if="!isAddingAddress"
                                        variant="ghost"
                                        size="sm"
                                        @click="startAddAddress"
                                    >
                                        <Plus class="h-4 w-4" />
                                    </Button>
                                </div>
                            </CardHeader>
                            <CardContent>
                                <!-- Add/Edit Form -->
                                <form v-if="isAddingAddress" @submit.prevent="submitAddressForm" class="space-y-4 mb-4">
                                    <div>
                                        <Label for="address_name">{{ t('common.address_name') }}</Label>
                                        <Input
                                            id="address_name"
                                            v-model="addressForm.name"
                                            type="text"
                                            :placeholder="t('common.address_name_placeholder')"
                                            :class="{ 'border-red-500': addressForm.errors.name }"
                                        />
                                        <p v-if="addressForm.errors.name" class="mt-1 text-sm text-red-600">
                                            {{ addressForm.errors.name }}
                                        </p>
                                    </div>

                                    <div>
                                        <Label for="address_phone">{{ t('common.phone') }} *</Label>
                                        <Input
                                            id="address_phone"
                                            v-model="addressForm.phone"
                                            type="tel"
                                            :placeholder="t('common.phone_placeholder')"
                                            required
                                            :class="{ 'border-red-500': addressForm.errors.phone }"
                                        />
                                        <p v-if="addressForm.errors.phone" class="mt-1 text-sm text-red-600">
                                            {{ addressForm.errors.phone }}
                                        </p>
                                    </div>

                                    <div>
                                        <Label for="address_address">{{ t('common.address') }} *</Label>
                                        <Input
                                            id="address_address"
                                            v-model="addressForm.address"
                                            type="text"
                                            :placeholder="t('common.address_placeholder')"
                                            required
                                            :class="{ 'border-red-500': addressForm.errors.address }"
                                        />
                                        <p v-if="addressForm.errors.address" class="mt-1 text-sm text-red-600">
                                            {{ addressForm.errors.address }}
                                        </p>
                                    </div>

                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <Label for="address_city">{{ t('common.city') }} *</Label>
                                            <Input
                                                id="address_city"
                                                v-model="addressForm.city"
                                                type="text"
                                                :placeholder="t('common.city_placeholder')"
                                                required
                                                :class="{ 'border-red-500': addressForm.errors.city }"
                                            />
                                            <p v-if="addressForm.errors.city" class="mt-1 text-sm text-red-600">
                                                {{ addressForm.errors.city }}
                                            </p>
                                        </div>

                                        <div>
                                            <Label for="address_postal_code">{{ t('common.postal_code') }}</Label>
                                            <Input
                                                id="address_postal_code"
                                                v-model="addressForm.postal_code"
                                                type="text"
                                                :placeholder="t('common.postal_code_placeholder')"
                                                :class="{ 'border-red-500': addressForm.errors.postal_code }"
                                            />
                                        </div>
                                    </div>

                                    <div>
                                        <Label for="address_country">{{ t('common.country') }} *</Label>
                                        <Input
                                            id="address_country"
                                            v-model="addressForm.country"
                                            type="text"
                                            :placeholder="t('common.country_placeholder')"
                                            required
                                            :class="{ 'border-red-500': addressForm.errors.country }"
                                        />
                                        <p v-if="addressForm.errors.country" class="mt-1 text-sm text-red-600">
                                            {{ addressForm.errors.country }}
                                        </p>
                                    </div>

                                    <div class="flex items-center gap-2">
                                        <input
                                            id="address_is_default"
                                            v-model="addressForm.is_default"
                                            type="checkbox"
                                            class="rounded border-gray-300"
                                        />
                                        <Label for="address_is_default" class="text-sm">{{ t('common.set_as_default') }}</Label>
                                    </div>

                                    <div class="flex gap-2">
                                        <Button type="submit" :disabled="addressForm.processing" class="flex-1">
                                            <Save class="mr-2 h-4 w-4" />
                                            {{ editingAddressId ? t('common.update') : t('common.add') }}
                                        </Button>
                                        <Button type="button" variant="outline" @click="cancelAddressForm" class="flex-1">
                                            <X class="mr-2 h-4 w-4" />
                                            {{ t('common.cancel') }}
                                        </Button>
                                    </div>
                                </form>

                                <!-- Addresses List -->
                                <div v-if="props.deliveryAddresses.length > 0" class="space-y-3">
                                    <div
                                        v-for="address in props.deliveryAddresses"
                                        :key="address.id"
                                        class="rounded-lg p-3 transition-all"
                                        :class="address.is_default ? 'bg-primary/5' : ''"
                                    >
                                        <div class="flex items-start justify-between">
                                            <div class="flex-1">
                                                <div class="flex items-center gap-2 mb-1">
                                                    <h4 class="font-medium text-gray-900 dark:text-white">
                                                        {{ address.name || t('common.delivery_addresses') }}
                                                    </h4>
                                                    <span
                                                        v-if="address.is_default"
                                                        class="flex items-center gap-1 rounded-full bg-primary/10 px-2 py-0.5 text-xs font-medium text-primary"
                                                    >
                                                        <Star class="h-3 w-3 fill-primary" />
                                                        {{ t('common.default_address') }}
                                                    </span>
                                                </div>
                                                <div class="space-y-1 text-sm text-gray-600 dark:text-gray-400">
                                                    <p class="flex items-center gap-2">
                                                        <Phone class="h-3 w-3" />
                                                        {{ address.phone }}
                                                    </p>
                                                    <p class="flex items-center gap-2">
                                                        <MapPin class="h-3 w-3" />
                                                        {{ address.full_address }}
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="flex gap-1 ml-2">
                                                <Button
                                                    variant="ghost"
                                                    size="sm"
                                                    @click="startEditAddress(address)"
                                                    class="h-8 w-8 p-0"
                                                >
                                                    <Edit2 class="h-4 w-4" />
                                                </Button>
                                                <Button
                                                    variant="ghost"
                                                    size="sm"
                                                    @click="deleteAddress(address.id)"
                                                    class="h-8 w-8 p-0 text-red-600 hover:text-red-700"
                                                >
                                                    <Trash2 class="h-4 w-4" />
                                                </Button>
                                            </div>
                                        </div>
                                        <div v-if="!address.is_default" class="mt-2">
                                            <Button
                                                variant="ghost"
                                                size="sm"
                                                @click="setDefaultAddress(address.id)"
                                                class="text-xs"
                                            >
                                                <Star class="mr-1 h-3 w-3" />
                                                {{ t('common.set_as_default') }}
                                            </Button>
                                        </div>
                                    </div>
                                </div>

                                <div v-else-if="!isAddingAddress" class="text-center py-4">
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">
                                        {{ t('common.no_addresses') }}
                                    </p>
                                    <Button variant="outline" size="sm" @click="startAddAddress">
                                        <Plus class="mr-2 h-4 w-4" />
                                        {{ t('common.add_first_address') }}
                                    </Button>
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                    <!-- Right Column: Wishlist & Orders -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Wishlist Card -->
                        <Card>
                            <CardHeader>
                                <CardTitle class="flex items-center gap-2">
                                    <Heart class="h-5 w-5" />
                                    {{ t('common.my_wishlist') }}
                                </CardTitle>
                                <CardDescription>
                                    {{ t('common.wishlist_description') }}
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <div v-if="props.wishlist && props.wishlist.length > 0" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                                    <div
                                        v-for="item in props.wishlist"
                                        :key="item.id"
                                        class="group relative rounded-lg p-4 transition-all"
                                    >
                                        <button
                                            @click="removeFromWishlist(item.product_id)"
                                            class="absolute right-2 top-2 z-10 rounded-full bg-white/80 p-1.5 opacity-0 transition-opacity hover:bg-red-100 hover:text-red-600 group-hover:opacity-100 dark:bg-gray-800"
                                            :title="t('common.remove_from_wishlist')"
                                        >
                                            <X class="h-4 w-4" />
                                        </button>
                                        <Link :href="`/products/${item.product.slug}`" class="block">
                                            <div class="aspect-square w-full overflow-hidden rounded-lg bg-gray-100 mb-3">
                                                <img
                                                    :src="item.product.image"
                                                    :alt="item.product.name"
                                                    class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105"
                                                />
                                            </div>
                                            <h3 class="mb-2 line-clamp-2 font-semibold text-gray-900 dark:text-white">
                                                {{ item.product.name }}
                                            </h3>
                                            <div class="flex items-center gap-2">
                                                <span class="text-lg font-bold text-gray-900 dark:text-white">
                                                    {{ formatPrice(item.product.price) }}
                                                </span>
                                                <span
                                                    v-if="item.product.original_price"
                                                    class="text-sm text-gray-500 line-through"
                                                >
                                                    {{ formatPrice(item.product.original_price) }}
                                                </span>
                                                <span
                                                    v-if="item.product.discount"
                                                    class="rounded bg-red-500 px-2 py-0.5 text-xs font-bold text-white"
                                                >
                                                    -{{ item.product.discount }}%
                                                </span>
                                            </div>
                                            <div class="mt-2">
                                                <span
                                                    :class="[
                                                        item.product.in_stock
                                                            ? 'text-green-600 dark:text-green-400'
                                                            : 'text-red-600 dark:text-red-400',
                                                        'text-sm font-medium',
                                                    ]"
                                                >
                                                    {{ item.product.in_stock ? t('common.in_stock') : t('common.out_of_stock') }}
                                                </span>
                                            </div>
                                        </Link>
                                    </div>
                                </div>

                                <div v-else class="py-12 text-center">
                                    <Heart class="mx-auto h-12 w-12 text-gray-400" />
                                    <p class="mt-4 text-lg font-medium text-gray-900 dark:text-white">
                                        {{ t('common.no_wishlist_items') }}
                                    </p>
                                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                        {{ t('common.no_wishlist_items_description') }}
                                    </p>
                                    <Link href="/products">
                                        <Button class="mt-4">{{ t('common.view_products') }}</Button>
                                    </Link>
                                </div>
                            </CardContent>
                        </Card>

                        <!-- Orders Card -->
                        <Card>
                            <CardHeader>
                                <CardTitle class="flex items-center gap-2">
                                    <Package class="h-5 w-5" />
                                    {{ t('common.my_orders') }}
                                </CardTitle>
                                <CardDescription>
                                    {{ t('common.orders_history') }}
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <div v-if="props.orders.data.length > 0" class="space-y-4">
                                    <div
                                        v-for="order in props.orders.data"
                                        :key="order.id"
                                        class="rounded-lg p-4 transition-all"
                                    >
                                        <div class="flex items-start justify-between">
                                            <div class="flex-1">
                                                <div class="flex items-center gap-3">
                                                    <Link
                                                        :href="`/orders/${order.id}`"
                                                        class="font-semibold text-primary hover:underline"
                                                    >
                                                        {{ order.order_number }}
                                                    </Link>
                                                    <span
                                                        :class="getStatusColor(order.status)"
                                                        class="rounded-full px-2 py-1 text-xs font-medium"
                                                    >
                                                        {{ getStatusLabel(order.status) }}
                                                    </span>
                                                    <span
                                                        v-if="order.payment_status === 'paid'"
                                                        class="rounded-full bg-green-100 px-2 py-1 text-xs font-medium text-green-800 dark:bg-green-900/20 dark:text-green-400"
                                                    >
                                                        {{ getPaymentStatusLabel(order.payment_status) }}
                                                    </span>
                                                </div>
                                                <div class="mt-2 flex items-center gap-4 text-sm text-gray-600 dark:text-gray-400">
                                                    <span>{{ order.created_at }}</span>
                                                    <span>•</span>
                                                    <span>{{ order.items_count }} {{ order.items_count === 1 ? t('common.product') : t('common.products') }}</span>
                                                    <span>•</span>
                                                    <span class="font-semibold text-gray-900 dark:text-white">
                                                        {{ formatPrice(order.total) }}
                                                    </span>
                                                </div>
                                            </div>
                                            <Link :href="`/orders/${order.id}`">
                                                <Button variant="outline" size="sm">
                                                    {{ t('common.order_details') }}
                                                </Button>
                                            </Link>
                                        </div>
                                    </div>

                                    <!-- Pagination -->
                                    <div v-if="props.orders.links.length > 3" class="mt-6 flex justify-center">
                                        <div class="flex gap-2">
                                            <template v-for="link in props.orders.links" :key="link.label">
                                                <Link
                                                    v-if="link.url"
                                                    :href="link.url"
                                                    :class="[
                                                        'px-3 py-2 rounded-md text-sm font-medium transition-colors',
                                                        link.active
                                                            ? 'bg-primary text-white'
                                                            : 'bg-white text-gray-700 hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700',
                                                    ]"
                                                >
                                                    <span v-html="link.label"></span>
                                                </Link>
                                                <span
                                                    v-else
                                                    :class="[
                                                        'px-3 py-2 rounded-md text-sm font-medium transition-colors opacity-50 cursor-not-allowed',
                                                        link.active
                                                            ? 'bg-primary text-white'
                                                            : 'bg-white text-gray-700 dark:bg-gray-800 dark:text-gray-300',
                                                    ]"
                                                    v-html="link.label"
                                                />
                                            </template>
                                        </div>
                                    </div>
                                </div>

                                <div v-else class="py-12 text-center">
                                    <Package class="mx-auto h-12 w-12 text-gray-400" />
                                    <p class="mt-4 text-lg font-medium text-gray-900 dark:text-white">
                                        {{ t('common.no_orders') }}
                                    </p>
                                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                        {{ t('common.no_orders_description') }}
                                    </p>
                                    <Link href="/products">
                                        <Button class="mt-4">{{ t('common.view_products') }}</Button>
                                    </Link>
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

