<script setup lang="ts">
import AppFooter from '@/components/AppFooter.vue';
import PublicHeader from '@/components/PublicHeader.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { ArrowLeft, MapPin, Star } from 'lucide-vue-next';
import { ref, computed } from 'vue';

interface CartItem {
    id: number;
    name: string;
    price: number;
    image: string;
    quantity: number;
    subtotal: number;
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

interface Props {
    items: CartItem[];
    subtotal: number;
    tax: number;
    shippingCost: number;
    total: number;
    deliveryAddresses?: DeliveryAddress[];
}

const props = defineProps<Props>();
const page = usePage();
const auth = computed(() => page.props.auth);

const selectedAddressId = ref<number | null>(
    props.deliveryAddresses?.find(a => a.is_default)?.id || null
);
const useSavedAddress = ref(!!selectedAddressId.value);

const form = useForm({
    shipping_name: auth.value?.user?.name || '',
    shipping_email: auth.value?.user?.email || '',
    shipping_phone: '',
    shipping_address: '',
    shipping_city: '',
    shipping_postal_code: '',
    shipping_country: 'Republica Moldova',
    notes: '',
});

const formatPrice = (price: number) => {
    return new Intl.NumberFormat('ro-MD', {
        style: 'currency',
        currency: 'MDL',
    }).format(price);
};

const selectAddress = (address: DeliveryAddress) => {
    selectedAddressId.value = address.id;
    form.shipping_phone = address.phone;
    form.shipping_address = address.address;
    form.shipping_city = address.city;
    form.shipping_postal_code = address.postal_code || '';
    form.shipping_country = address.country;
    useSavedAddress.value = true;
};

const useNewAddress = () => {
    useSavedAddress.value = false;
    selectedAddressId.value = null;
    form.shipping_phone = '';
    form.shipping_address = '';
    form.shipping_city = '';
    form.shipping_postal_code = '';
    form.shipping_country = 'Republica Moldova';
};

const submit = () => {
    form.post('/orders');
};
</script>

<template>
    <Head title="Finalizare comandă" />
    <div class="flex min-h-screen flex-col">
        <PublicHeader />

        <main class="flex-1">
            <div class="mx-auto max-w-7xl px-4 py-6 md:px-6">
                <div class="mb-6 flex items-center gap-4">
                    <Link
                        href="/cart"
                        class="inline-flex items-center text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100"
                    >
                        <ArrowLeft class="mr-2 h-4 w-4" />
                        Înapoi la coș
                    </Link>
                </div>

                <h1 class="mb-6 text-3xl font-bold text-gray-900 dark:text-white">
                    Finalizare comandă
                </h1>

                <form @submit.prevent="submit">
                    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                        <!-- Shipping Form -->
                        <div class="lg:col-span-2 space-y-6">
                            <!-- Saved Addresses (if user is logged in and has addresses) -->
                            <Card v-if="auth.user && props.deliveryAddresses && props.deliveryAddresses.length > 0">
                                <CardHeader>
                                    <CardTitle>Adrese salvate</CardTitle>
                                </CardHeader>
                                <CardContent class="space-y-3">
                                    <div
                                        v-for="address in props.deliveryAddresses"
                                        :key="address.id"
                                        class="rounded-lg border p-3 cursor-pointer transition-all"
                                        :class="
                                            selectedAddressId === address.id && useSavedAddress
                                                ? 'border-primary bg-primary/5'
                                                : 'border-gray-200 dark:border-gray-700 hover:border-gray-300'
                                        "
                                        @click="selectAddress(address)"
                                    >
                                        <div class="flex items-start justify-between">
                                            <div class="flex-1">
                                                <div class="flex items-center gap-2 mb-1">
                                                    <h4 class="font-medium text-gray-900 dark:text-white">
                                                        {{ address.name || 'Adresă de livrare' }}
                                                    </h4>
                                                    <span
                                                        v-if="address.is_default"
                                                        class="flex items-center gap-1 rounded-full bg-primary/10 px-2 py-0.5 text-xs font-medium text-primary"
                                                    >
                                                        <Star class="h-3 w-3 fill-primary" />
                                                        Implicită
                                                    </span>
                                                </div>
                                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                                    {{ address.phone }}
                                                </p>
                                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                                    {{ address.full_address }}
                                                </p>
                                            </div>
                                            <input
                                                type="radio"
                                                :checked="selectedAddressId === address.id && useSavedAddress"
                                                @change="selectAddress(address)"
                                                class="h-4 w-4 text-primary"
                                            />
                                        </div>
                                    </div>
                                    <Button
                                        type="button"
                                        variant="outline"
                                        class="w-full"
                                        @click="useNewAddress"
                                    >
                                        Folosește o adresă nouă
                                    </Button>
                                </CardContent>
                            </Card>

                            <Card>
                                <CardHeader>
                                    <CardTitle>
                                        {{ useSavedAddress && selectedAddressId ? 'Date de livrare (selectate)' : 'Date de livrare' }}
                                    </CardTitle>
                                </CardHeader>
                                <CardContent class="space-y-4">
                                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                        <div>
                                            <label
                                                for="shipping_name"
                                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"
                                            >
                                                Nume complet *
                                            </label>
                                            <input
                                                id="shipping_name"
                                                v-model="form.shipping_name"
                                                type="text"
                                                required
                                                class="w-full rounded-lg border border-gray-300 px-4 py-2 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                                                :class="{ 'border-red-500': form.errors.shipping_name }"
                                            />
                                            <p
                                                v-if="form.errors.shipping_name"
                                                class="mt-1 text-sm text-red-500"
                                            >
                                                {{ form.errors.shipping_name }}
                                            </p>
                                        </div>
                                        <div>
                                            <label
                                                for="shipping_email"
                                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"
                                            >
                                                Email *
                                            </label>
                                            <input
                                                id="shipping_email"
                                                v-model="form.shipping_email"
                                                type="email"
                                                required
                                                class="w-full rounded-lg border border-gray-300 px-4 py-2 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                                                :class="{ 'border-red-500': form.errors.shipping_email }"
                                            />
                                            <p
                                                v-if="form.errors.shipping_email"
                                                class="mt-1 text-sm text-red-500"
                                            >
                                                {{ form.errors.shipping_email }}
                                            </p>
                                        </div>
                                    </div>
                                    <div>
                                        <label
                                            for="shipping_phone"
                                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"
                                        >
                                            Telefon *
                                        </label>
                                        <input
                                            id="shipping_phone"
                                            v-model="form.shipping_phone"
                                            type="tel"
                                            required
                                            :disabled="useSavedAddress && selectedAddressId"
                                            class="w-full rounded-lg border border-gray-300 px-4 py-2 dark:border-gray-600 dark:bg-gray-800 dark:text-white disabled:opacity-50 disabled:cursor-not-allowed"
                                            :class="{ 'border-red-500': form.errors.shipping_phone }"
                                        />
                                        <p
                                            v-if="form.errors.shipping_phone"
                                            class="mt-1 text-sm text-red-500"
                                        >
                                            {{ form.errors.shipping_phone }}
                                        </p>
                                    </div>
                                    <div>
                                        <label
                                            for="shipping_address"
                                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"
                                        >
                                            Adresă *
                                        </label>
                                        <input
                                            id="shipping_address"
                                            v-model="form.shipping_address"
                                            type="text"
                                            required
                                            :disabled="useSavedAddress && selectedAddressId"
                                            class="w-full rounded-lg border border-gray-300 px-4 py-2 dark:border-gray-600 dark:bg-gray-800 dark:text-white disabled:opacity-50 disabled:cursor-not-allowed"
                                            :class="{ 'border-red-500': form.errors.shipping_address }"
                                        />
                                        <p
                                            v-if="form.errors.shipping_address"
                                            class="mt-1 text-sm text-red-500"
                                        >
                                            {{ form.errors.shipping_address }}
                                        </p>
                                    </div>
                                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                                        <div>
                                            <label
                                                for="shipping_city"
                                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"
                                            >
                                                Oraș *
                                            </label>
                                            <input
                                                id="shipping_city"
                                                v-model="form.shipping_city"
                                                type="text"
                                                required
                                                :disabled="useSavedAddress && selectedAddressId"
                                                class="w-full rounded-lg border border-gray-300 px-4 py-2 dark:border-gray-600 dark:bg-gray-800 dark:text-white disabled:opacity-50 disabled:cursor-not-allowed"
                                                :class="{ 'border-red-500': form.errors.shipping_city }"
                                            />
                                            <p
                                                v-if="form.errors.shipping_city"
                                                class="mt-1 text-sm text-red-500"
                                            >
                                                {{ form.errors.shipping_city }}
                                            </p>
                                        </div>
                                        <div>
                                            <label
                                                for="shipping_postal_code"
                                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"
                                            >
                                                Cod poștal
                                            </label>
                                            <input
                                                id="shipping_postal_code"
                                                v-model="form.shipping_postal_code"
                                                type="text"
                                                :disabled="useSavedAddress && selectedAddressId"
                                                class="w-full rounded-lg border border-gray-300 px-4 py-2 dark:border-gray-600 dark:bg-gray-800 dark:text-white disabled:opacity-50 disabled:cursor-not-allowed"
                                                :class="{ 'border-red-500': form.errors.shipping_postal_code }"
                                            />
                                        </div>
                                        <div>
                                            <label
                                                for="shipping_country"
                                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"
                                            >
                                                Țară *
                                            </label>
                                            <input
                                                id="shipping_country"
                                                v-model="form.shipping_country"
                                                type="text"
                                                required
                                                :disabled="useSavedAddress && selectedAddressId"
                                                class="w-full rounded-lg border border-gray-300 px-4 py-2 dark:border-gray-600 dark:bg-gray-800 dark:text-white disabled:opacity-50 disabled:cursor-not-allowed"
                                                :class="{ 'border-red-500': form.errors.shipping_country }"
                                            />
                                        </div>
                                    </div>
                                    <div>
                                        <label
                                            for="notes"
                                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"
                                        >
                                            Note (opțional)
                                        </label>
                                        <textarea
                                            id="notes"
                                            v-model="form.notes"
                                            rows="3"
                                            placeholder="Note suplimentare pentru comandă..."
                                            class="w-full rounded-lg border border-gray-300 px-4 py-2 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                                        />
                                    </div>
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
                                    <div class="space-y-2">
                                        <div
                                            v-for="item in props.items"
                                            :key="item.id"
                                            class="flex items-center gap-2 text-sm"
                                        >
                                            <img
                                                :src="item.image"
                                                :alt="item.name"
                                                class="h-12 w-12 rounded object-cover"
                                            />
                                            <div class="flex-1">
                                                <p class="font-medium">{{ item.name }}</p>
                                                <p class="text-gray-500">
                                                    {{ item.quantity }} x {{ formatPrice(item.price) }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="border-t pt-4 space-y-2">
                                        <div class="flex justify-between text-sm">
                                            <span class="text-gray-600 dark:text-gray-400">
                                                Subtotal
                                            </span>
                                            <span class="font-medium">
                                                {{ formatPrice(props.subtotal) }}
                                            </span>
                                        </div>
                                        <div class="flex justify-between text-sm">
                                            <span class="text-gray-600 dark:text-gray-400">
                                                TVA (19%)
                                            </span>
                                            <span class="font-medium">
                                                {{ formatPrice(props.tax) }}
                                            </span>
                                        </div>
                                        <div class="flex justify-between text-sm">
                                            <span class="text-gray-600 dark:text-gray-400">
                                                Transport
                                            </span>
                                            <span class="font-medium">
                                                {{ props.shippingCost === 0 ? 'Gratuit' : formatPrice(props.shippingCost) }}
                                            </span>
                                        </div>
                                        <div class="border-t pt-2">
                                            <div class="flex justify-between text-lg font-bold">
                                                <span>Total</span>
                                                <span>{{ formatPrice(props.total) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <Button
                                        type="submit"
                                        class="w-full"
                                        size="lg"
                                        :disabled="form.processing"
                                    >
                                        {{ form.processing ? 'Se procesează...' : 'Plasează comanda' }}
                                    </Button>
                                </CardContent>
                            </Card>
                        </div>
                    </div>
                </form>
            </div>
        </main>

        <AppFooter />
    </div>
</template>

