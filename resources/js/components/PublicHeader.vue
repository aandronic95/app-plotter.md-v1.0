<script setup lang="ts">
import AppLogo from '@/components/AppLogo.vue';
import { Button } from '@/components/ui/button';
import {
    Sheet,
    SheetContent,
    SheetHeader,
    SheetTitle,
    SheetTrigger,
} from '@/components/ui/sheet';
import { home, register, dashboard } from '@/routes';
import { Link, usePage } from '@inertiajs/vue3';
import { Menu, Search, ShoppingCart, User } from 'lucide-vue-next';
import { computed, onMounted, onUnmounted, ref } from 'vue';

const page = usePage();
const auth = computed(() => page.props.auth);
const cartCount = ref(0);

const menuItems = [
    { name: 'Acasă', href: home() },
    { name: 'Produse', href: '/products' },
    { name: 'Categorii', href: '/categories' },
    { name: 'Despre noi', href: '/about' },
    { name: 'Contact', href: '/contact' },
];

const fetchCartCount = async () => {
    try {
        const response = await fetch('/cart/data');
        const data = await response.json();
        cartCount.value = data.count || 0;
    } catch (error) {
        console.error('Error fetching cart count:', error);
    }
};

const handleCartUpdate = () => {
    fetchCartCount();
};

onMounted(() => {
    fetchCartCount();
    window.addEventListener('cart-updated', handleCartUpdate);
});

onUnmounted(() => {
    window.removeEventListener('cart-updated', handleCartUpdate);
});
</script>

<template>
    <header class="sticky top-0 z-50 border-b bg-white shadow-sm dark:bg-gray-800">
        <div class="mx-auto max-w-7xl px-4 md:px-6">
            <div class="flex h-16 items-center justify-between">
                <!-- Logo -->
                <Link :href="home()" class="flex items-center">
                    <AppLogo />
                </Link>

                <!-- Desktop Navigation -->
                <nav class="hidden items-center gap-6 md:flex">
                    <Link
                        v-for="item in menuItems"
                        :key="item.name"
                        :href="item.href"
                        class="text-sm font-medium text-gray-700 transition-colors hover:text-gray-900 dark:text-gray-300 dark:hover:text-white"
                    >
                        {{ item.name }}
                    </Link>
                </nav>

                <!-- Right Side Actions -->
                <div class="flex items-center gap-2">
                    <Button variant="ghost" size="icon" class="h-9 w-9">
                        <Search class="h-5 w-5" />
                    </Button>
                    <Link href="/cart">
                        <Button
                            variant="ghost"
                            size="icon"
                            class="relative h-9 w-9"
                        >
                            <ShoppingCart class="h-5 w-5" />
                            <span
                                v-if="cartCount > 0"
                                class="absolute -right-1 -top-1 flex h-5 w-5 items-center justify-center rounded-full bg-red-500 text-xs font-bold text-white"
                            >
                                {{ cartCount > 99 ? '99+' : cartCount }}
                            </span>
                        </Button>
                    </Link>

                    <!-- Mobile Menu -->
                    <div class="md:hidden">
                        <Sheet>
                            <SheetTrigger :as-child="true">
                                <Button variant="ghost" size="icon" class="h-9 w-9">
                                    <Menu class="h-5 w-5" />
                                </Button>
                            </SheetTrigger>
                            <SheetContent side="right" class="w-[300px]">
                                <SheetHeader>
                                    <SheetTitle>Meniu</SheetTitle>
                                </SheetHeader>
                                <nav class="mt-6 space-y-4">
                                    <Link
                                        v-for="item in menuItems"
                                        :key="item.name"
                                        :href="item.href"
                                        class="block text-sm font-medium text-gray-700 transition-colors hover:text-gray-900 dark:text-gray-300 dark:hover:text-white"
                                    >
                                        {{ item.name }}
                                    </Link>
                                </nav>
                                <div class="mt-8 space-y-2 border-t pt-4">
                                    <template v-if="auth.user">
                                        <a :href="dashboard().url" target="_blank" rel="noopener noreferrer">
                                            <Button class="w-full" variant="outline">
                                                <User class="mr-2 h-4 w-4" />
                                                Dashboard
                                            </Button>
                                        </a>
                                    </template>
                                    <template v-else>
                                        <a href="/admin/login" target="_blank" rel="noopener noreferrer">
                                            <Button class="w-full" variant="outline">
                                                Autentificare
                                            </Button>
                                        </a>
                                        <Link :href="register()">
                                            <Button class="w-full">Înregistrare</Button>
                                        </Link>
                                    </template>
                                </div>
                            </SheetContent>
                        </Sheet>
                    </div>

                    <!-- Desktop Auth Buttons -->
                    <div class="hidden items-center gap-2 md:flex">
                        <template v-if="auth.user">
                            <a href="/admin" target="_blank" rel="noopener noreferrer">
                                <Button variant="outline" size="sm">
                                    <User class="mr-2 h-4 w-4" />
                                    Dashboard
                                </Button>
                            </a>
                        </template>
                        <template v-else>
                            <a href="/admin/login" target="_blank" rel="noopener noreferrer">
                                <Button variant="ghost" size="sm">
                                    Autentificare
                                </Button>
                            </a>
                            <Link :href="register()">
                                <Button size="sm">Înregistrare</Button>
                            </Link>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </header>
</template>

