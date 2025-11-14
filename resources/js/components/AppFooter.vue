<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    Facebook,
    Instagram,
    Twitter,
    Mail,
    Phone,
    MapPin,
} from 'lucide-vue-next';

const currentYear = new Date().getFullYear();

const footerLinks = {
    company: [
        { name: 'Despre noi', href: '/about' },
        { name: 'Contact', href: '/contact' },
        { name: 'Cariere', href: '/careers' },
        { name: 'Blog', href: '/blog' },
    ],
    customer: [
        { name: 'Centru de ajutor', href: '/help' },
        { name: 'Livrare', href: '/shipping' },
        { name: 'Returnări', href: '/returns' },
        { name: 'FAQ', href: '/faq' },
    ],
    legal: [
        { name: 'Termeni și condiții', href: '/terms' },
        { name: 'Politica de confidențialitate', href: '/privacy' },
        { name: 'Cookie-uri', href: '/cookies' },
        { name: 'GDPR', href: '/gdpr' },
    ],
};
</script>

<template>
    <footer class="border-t bg-gray-50 dark:bg-gray-900">
        <div class="mx-auto max-w-7xl px-4 py-12 md:px-6">
            <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-4">
                <!-- Company Info -->
                <div>
                    <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">
                        Despre noi
                    </h3>
                    <p class="mb-4 text-sm text-gray-600 dark:text-gray-400">
                        Magazinul tău online de încredere pentru toate nevoile.
                        Oferim produse de calitate la prețuri competitive.
                    </p>
                    <div class="flex gap-4">
                        <a
                            href="#"
                            class="text-gray-600 transition-colors hover:text-gray-900 dark:text-gray-400 dark:hover:text-white"
                        >
                            <Facebook class="h-5 w-5" />
                        </a>
                        <a
                            href="#"
                            class="text-gray-600 transition-colors hover:text-gray-900 dark:text-gray-400 dark:hover:text-white"
                        >
                            <Instagram class="h-5 w-5" />
                        </a>
                        <a
                            href="#"
                            class="text-gray-600 transition-colors hover:text-gray-900 dark:text-gray-400 dark:hover:text-white"
                        >
                            <Twitter class="h-5 w-5" />
                        </a>
                    </div>
                </div>

                <!-- Company Links -->
                <div>
                    <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">
                        Companie
                    </h3>
                    <ul class="space-y-2">
                        <li
                            v-for="link in footerLinks.company"
                            :key="link.name"
                        >
                            <Link
                                :href="link.href"
                                class="text-sm text-gray-600 transition-colors hover:text-gray-900 dark:text-gray-400 dark:hover:text-white"
                            >
                                {{ link.name }}
                            </Link>
                        </li>
                    </ul>
                </div>

                <!-- Customer Service -->
                <div>
                    <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">
                        Servicii Clienți
                    </h3>
                    <ul class="space-y-2">
                        <li
                            v-for="link in footerLinks.customer"
                            :key="link.name"
                        >
                            <Link
                                :href="link.href"
                                class="text-sm text-gray-600 transition-colors hover:text-gray-900 dark:text-gray-400 dark:hover:text-white"
                            >
                                {{ link.name }}
                            </Link>
                        </li>
                    </ul>
                </div>

                <!-- Contact Info -->
                <div>
                    <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">
                        Contact
                    </h3>
                    <ul class="space-y-3">
                        <li class="flex items-start gap-3">
                            <MapPin class="mt-0.5 h-5 w-5 text-gray-600 dark:text-gray-400" />
                            <span class="text-sm text-gray-600 dark:text-gray-400">
                                Strada Exemplu nr. 123<br />București, România
                            </span>
                        </li>
                        <li class="flex items-center gap-3">
                            <Phone class="h-5 w-5 text-gray-600 dark:text-gray-400" />
                            <a
                                href="tel:+40123456789"
                                class="text-sm text-gray-600 transition-colors hover:text-gray-900 dark:text-gray-400 dark:hover:text-white"
                            >
                                +40 123 456 789
                            </a>
                        </li>
                        <li class="flex items-center gap-3">
                            <Mail class="h-5 w-5 text-gray-600 dark:text-gray-400" />
                            <a
                                href="mailto:contact@example.com"
                                class="text-sm text-gray-600 transition-colors hover:text-gray-900 dark:text-gray-400 dark:hover:text-white"
                            >
                                contact@example.com
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Bottom Bar -->
            <div
                class="mt-8 border-t border-gray-200 pt-8 dark:border-gray-800"
            >
                <div
                    class="flex flex-col items-center justify-between gap-4 md:flex-row"
                >
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        © {{ currentYear }} Toate drepturile rezervate.
                    </p>
                    <div class="flex gap-6">
                        <Link
                            v-for="link in footerLinks.legal"
                            :key="link.name"
                            :href="link.href"
                            class="text-sm text-gray-600 transition-colors hover:text-gray-900 dark:text-gray-400 dark:hover:text-white"
                        >
                            {{ link.name }}
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </footer>
</template>

