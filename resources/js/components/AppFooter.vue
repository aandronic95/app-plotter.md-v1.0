<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    Facebook,
    Instagram,
    Twitter,
    Linkedin,
    Mail,
    Phone,
    MapPin,
} from 'lucide-vue-next';
import { ref, onMounted, computed } from 'vue';
import { useSiteSettings } from '@/composables/useSiteSettings';
import { useTranslations } from '@/composables/useTranslations';

interface NavigationItem {
    id: number;
    title: string;
    href: string;
    category?: string;
    is_external?: boolean;
    target?: string;
}

const currentYear = new Date().getFullYear();
const { siteSettings, fetchSiteSettings } = useSiteSettings();
const { t } = useTranslations();
const footerNavItems = ref<NavigationItem[]>([]);
const isLoading = ref(true);

// Fallback links dacă API-ul eșuează
const fallbackFooterLinks = {
    company: [
        { id: 1, title: 'Despre noi', href: '/about', is_external: false, target: '_self' },
        { id: 2, title: 'Contact', href: '/contact', is_external: false, target: '_self' },
        { id: 3, title: 'Cariere', href: '/careers', is_external: false, target: '_self' },
        { id: 4, title: 'Blog', href: '/blog', is_external: false, target: '_self' },
    ],
    customer: [
        { id: 5, title: 'Centru de ajutor', href: '/help', is_external: false, target: '_self' },
        { id: 6, title: 'Livrare', href: '/shipping', is_external: false, target: '_self' },
        { id: 7, title: 'Returnări', href: '/returns', is_external: false, target: '_self' },
        { id: 8, title: 'FAQ', href: '/faq', is_external: false, target: '_self' },
    ],
    legal: [
        { id: 9, title: 'Termeni și condiții', href: '/terms', is_external: false, target: '_self' },
        { id: 10, title: 'Politica de confidențialitate', href: '/privacy', is_external: false, target: '_self' },
        { id: 11, title: 'Cookie-uri', href: '/cookies', is_external: false, target: '_self' },
        { id: 12, title: 'GDPR', href: '/gdpr', is_external: false, target: '_self' },
    ],
};

// Fetch navigation items from API
const fetchFooterNavigation = async () => {
    try {
        const response = await fetch('/api/navigations?group=footer');
        const data = await response.json();
        
        if (data.data && data.data.length > 0) {
            footerNavItems.value = data.data;
        } else {
            // Dacă nu există elemente în baza de date, folosește fallback
            footerNavItems.value = [
                ...fallbackFooterLinks.company,
                ...fallbackFooterLinks.customer,
                ...fallbackFooterLinks.legal,
            ];
        }
    } catch (error) {
        console.error('Error fetching footer navigation:', error);
        // Folosește fallback dacă API-ul eșuează
        footerNavItems.value = [
            ...fallbackFooterLinks.company,
            ...fallbackFooterLinks.customer,
            ...fallbackFooterLinks.legal,
        ];
    } finally {
        isLoading.value = false;
    }
};

// Organizează link-urile din baza de date în categorii
const footerLinks = computed(() => {
    // Dacă există categorii definite în baza de date, folosește-le
    const company = footerNavItems.value.filter(item => item.category === 'company');
    const customer = footerNavItems.value.filter(item => item.category === 'customer');
    const legal = footerNavItems.value.filter(item => item.category === 'legal');

    // Dacă nu există categorii, folosește fallback sau filtrare bazată pe titlu
    if (company.length === 0 && customer.length === 0 && legal.length === 0) {
        const companyByTitle = footerNavItems.value.filter(item => 
            item.title.toLowerCase().includes('despre') || 
            item.title.toLowerCase().includes('contact') ||
            item.title.toLowerCase().includes('cariere') ||
            item.title.toLowerCase().includes('blog')
        );
        
        const customerByTitle = footerNavItems.value.filter(item => 
            item.title.toLowerCase().includes('ajutor') || 
            item.title.toLowerCase().includes('livrare') ||
            item.title.toLowerCase().includes('return') ||
            item.title.toLowerCase().includes('faq')
        );
        
        const legalByTitle = footerNavItems.value.filter(item => 
            item.title.toLowerCase().includes('termeni') || 
            item.title.toLowerCase().includes('politica') ||
            item.title.toLowerCase().includes('cookie') ||
            item.title.toLowerCase().includes('gdpr')
        );

        return {
            company: companyByTitle.length > 0 ? companyByTitle : fallbackFooterLinks.company,
            customer: customerByTitle.length > 0 ? customerByTitle : fallbackFooterLinks.customer,
            legal: legalByTitle.length > 0 ? legalByTitle : fallbackFooterLinks.legal,
        };
    }

    return {
        company: company.length > 0 ? company : fallbackFooterLinks.company,
        customer: customer.length > 0 ? customer : fallbackFooterLinks.customer,
        legal: legal.length > 0 ? legal : fallbackFooterLinks.legal,
    };
});

onMounted(() => {
    fetchSiteSettings();
    fetchFooterNavigation();
});
</script>

<template>
    <footer class="border-t bg-gray-50 dark:bg-gray-900">
        <div class="mx-auto max-w-7xl px-4 py-12 md:px-6">
            <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-4">
                <!-- Company Info -->
                <div>
                    <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">
                        {{ t('about_us') }}
                    </h3>
                    <p
                        v-if="siteSettings?.site_description"
                        class="mb-4 text-sm text-gray-600 dark:text-gray-400"
                    >
                        {{ siteSettings.site_description }}
                    </p>
                    <p
                        v-else
                        class="mb-4 text-sm text-gray-600 dark:text-gray-400"
                    >
                        Magazinul tău online de încredere pentru toate nevoile.
                        Oferim produse de calitate la prețuri competitive.
                    </p>
                    <div class="flex gap-4">
                        <a
                            v-if="siteSettings?.site_facebook"
                            :href="siteSettings.site_facebook"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="text-gray-600 transition-colors hover:text-gray-900 dark:text-gray-400 dark:hover:text-white"
                        >
                            <Facebook class="h-5 w-5" />
                        </a>
                        <a
                            v-if="siteSettings?.site_instagram"
                            :href="siteSettings.site_instagram"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="text-gray-600 transition-colors hover:text-gray-900 dark:text-gray-400 dark:hover:text-white"
                        >
                            <Instagram class="h-5 w-5" />
                        </a>
                        <a
                            v-if="siteSettings?.site_twitter"
                            :href="siteSettings.site_twitter"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="text-gray-600 transition-colors hover:text-gray-900 dark:text-gray-400 dark:hover:text-white"
                        >
                            <Twitter class="h-5 w-5" />
                        </a>
                        <a
                            v-if="siteSettings?.site_linkedin"
                            :href="siteSettings.site_linkedin"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="text-gray-600 transition-colors hover:text-gray-900 dark:text-gray-400 dark:hover:text-white"
                        >
                            <Linkedin class="h-5 w-5" />
                        </a>
                    </div>
                </div>

                <!-- Company Links -->
                <div>
                    <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">
                        {{ t('company') }}
                    </h3>
                    <ul v-if="!isLoading" class="space-y-2">
                        <li
                            v-for="link in footerLinks.company"
                            :key="link.id || link.title"
                        >
                            <Link
                                v-if="!link.is_external"
                                :href="link.href"
                                class="text-sm text-gray-600 transition-colors hover:text-gray-900 dark:text-gray-400 dark:hover:text-white"
                            >
                                {{ link.title }}
                            </Link>
                            <a
                                v-else
                                :href="link.href"
                                :target="link.target || '_blank'"
                                :rel="link.is_external ? 'noopener noreferrer' : undefined"
                                class="text-sm text-gray-600 transition-colors hover:text-gray-900 dark:text-gray-400 dark:hover:text-white"
                            >
                                {{ link.title }}
                            </a>
                        </li>
                    </ul>
                    <div v-else class="space-y-2">
                        <div class="h-4 w-24 animate-pulse rounded bg-gray-200"></div>
                        <div class="h-4 w-24 animate-pulse rounded bg-gray-200"></div>
                        <div class="h-4 w-24 animate-pulse rounded bg-gray-200"></div>
                    </div>
                </div>

                <!-- Customer Service -->
                <div>
                    <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">
                        {{ t('customer_service') }}
                    </h3>
                    <ul v-if="!isLoading" class="space-y-2">
                        <li
                            v-for="link in footerLinks.customer"
                            :key="link.id || link.title"
                        >
                            <Link
                                v-if="!link.is_external"
                                :href="link.href"
                                class="text-sm text-gray-600 transition-colors hover:text-gray-900 dark:text-gray-400 dark:hover:text-white"
                            >
                                {{ link.title }}
                            </Link>
                            <a
                                v-else
                                :href="link.href"
                                :target="link.target || '_blank'"
                                :rel="link.is_external ? 'noopener noreferrer' : undefined"
                                class="text-sm text-gray-600 transition-colors hover:text-gray-900 dark:text-gray-400 dark:hover:text-white"
                            >
                                {{ link.title }}
                            </a>
                        </li>
                    </ul>
                    <div v-else class="space-y-2">
                        <div class="h-4 w-24 animate-pulse rounded bg-gray-200"></div>
                        <div class="h-4 w-24 animate-pulse rounded bg-gray-200"></div>
                        <div class="h-4 w-24 animate-pulse rounded bg-gray-200"></div>
                    </div>
                </div>

                <!-- Legal Links -->
                <div>
                    <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">
                        {{ t('legal') }}
                    </h3>
                    <ul v-if="!isLoading" class="space-y-2">
                        <li
                            v-for="link in footerLinks.legal"
                            :key="link.id || link.title"
                        >
                            <Link
                                v-if="!link.is_external"
                                :href="link.href"
                                class="text-sm text-gray-600 transition-colors hover:text-gray-900 dark:text-gray-400 dark:hover:text-white"
                            >
                                {{ link.title }}
                            </Link>
                            <a
                                v-else
                                :href="link.href"
                                :target="link.target || '_blank'"
                                :rel="link.is_external ? 'noopener noreferrer' : undefined"
                                class="text-sm text-gray-600 transition-colors hover:text-gray-900 dark:text-gray-400 dark:hover:text-white"
                            >
                                {{ link.title }}
                            </a>
                        </li>
                    </ul>
                    <div v-else class="space-y-2">
                        <div class="h-4 w-24 animate-pulse rounded bg-gray-200"></div>
                        <div class="h-4 w-24 animate-pulse rounded bg-gray-200"></div>
                        <div class="h-4 w-24 animate-pulse rounded bg-gray-200"></div>
                    </div>
                </div>

                <!-- Contact Info -->
                <div>
                    <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">
                        {{ t('contact') }}
                    </h3>
                    <ul class="space-y-3">
                        <li
                            v-if="siteSettings?.site_address"
                            class="flex items-start gap-3"
                        >
                            <MapPin class="mt-0.5 h-5 w-5 text-gray-600 dark:text-gray-400" />
                            <span
                                class="text-sm text-gray-600 dark:text-gray-400"
                                v-html="siteSettings.site_address.replace(/\n/g, '<br />')"
                            ></span>
                        </li>
                        <li
                            v-if="siteSettings?.site_phone"
                            class="flex items-center gap-3"
                        >
                            <Phone class="h-5 w-5 text-gray-600 dark:text-gray-400" />
                            <a
                                :href="`tel:${siteSettings.site_phone.replace(/\s/g, '')}`"
                                class="text-sm text-gray-600 transition-colors hover:text-gray-900 dark:text-gray-400 dark:hover:text-white"
                            >
                                {{ siteSettings.site_phone }}
                            </a>
                        </li>
                        <li
                            v-if="siteSettings?.site_email"
                            class="flex items-center gap-3"
                        >
                            <Mail class="h-5 w-5 text-gray-600 dark:text-gray-400" />
                            <a
                                :href="`mailto:${siteSettings.site_email}`"
                                class="text-sm text-gray-600 transition-colors hover:text-gray-900 dark:text-gray-400 dark:hover:text-white"
                            >
                                {{ siteSettings.site_email }}
                            </a>
                        </li>
                        <li
                            v-if="!siteSettings?.site_address && !siteSettings?.site_phone && !siteSettings?.site_email"
                            class="text-sm text-gray-600 dark:text-gray-400"
                        >
                            {{ t('contact_info_coming_soon') }}
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
                        © {{ currentYear }}
                        <span v-if="siteSettings?.site_name">{{ siteSettings.site_name }}</span>
                        <span v-else>{{ t('all_rights_reserved') }}</span>
                    </p>
                    <div v-if="!isLoading" class="flex flex-wrap gap-4">
                        <Link
                            href="/pages"
                            class="text-sm text-gray-600 transition-colors hover:text-gray-900 dark:text-gray-400 dark:hover:text-white"
                        >
                            {{ t('pages') }}
                        </Link>
                        <Link
                            v-for="link in footerLinks.legal"
                            :key="link.id || link.title"
                            :href="link.href"
                            :target="link.is_external ? (link.target || '_blank') : '_self'"
                            :rel="link.is_external ? 'noopener noreferrer' : undefined"
                            class="text-sm text-gray-600 transition-colors hover:text-gray-900 dark:text-gray-400 dark:hover:text-white"
                        >
                            {{ link.title }}
                        </Link>
                    </div>
                    <div v-else class="flex gap-6">
                        <div class="h-4 w-24 animate-pulse rounded bg-gray-200"></div>
                        <div class="h-4 w-24 animate-pulse rounded bg-gray-200"></div>
                    </div>
                </div>
            </div>
        </div>
    </footer>
</template>

