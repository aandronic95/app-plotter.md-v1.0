<script setup lang="ts">
import AppLogo from '@/components/AppLogo.vue';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    NavigationMenu,
    NavigationMenuItem,
    NavigationMenuList,
} from '@/components/ui/navigation-menu';
import {
    Sheet,
    SheetContent,
    SheetHeader,
    SheetTitle,
    SheetTrigger,
} from '@/components/ui/sheet';
import {
    Tooltip,
    TooltipContent,
    TooltipProvider,
    TooltipTrigger,
} from '@/components/ui/tooltip';
import UserMenuContent from '@/components/UserMenuContent.vue';
import { getInitials } from '@/composables/useInitials';
import { useApiCache } from '@/composables/useApiCache';
import { toUrl, urlIsActive } from '@/lib/utils';
import { dashboard } from '@/routes';
import type { BreadcrumbItem, NavItem } from '@/types';
import { InertiaLinkProps, Link, usePage } from '@inertiajs/vue3';
import { BookOpen, ChevronDown, Folder, LayoutGrid, Menu, Phone, Search } from 'lucide-vue-next';
import { computed, onMounted, ref } from 'vue';
import * as lucideIcons from 'lucide-vue-next';

interface Props {
    breadcrumbs?: BreadcrumbItem[];
}

interface NavigationItem {
    id: number;
    title: string;
    href: string;
    icon?: string;
    is_external?: boolean;
    target?: string;
    children?: NavigationItem[];
    has_children?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

const page = usePage();
const auth = computed(() => page.props.auth);
const apiCache = useApiCache();
const menuItems = ref<NavigationItem[]>([]);
const isLoading = ref(true);

const isCurrentRoute = computed(
    () => (url: NonNullable<InertiaLinkProps['href']>) =>
        urlIsActive(url, page.url),
);

const activeItemStyles = computed(
    () => (url: NonNullable<InertiaLinkProps['href']>) =>
        isCurrentRoute.value(toUrl(url))
            ? 'text-neutral-900 dark:bg-neutral-800 dark:text-neutral-100'
            : '',
);

// Get icon component by name
const getIconComponent = (iconName?: string) => {
    if (!iconName) return null;
    
    const iconKey = iconName
        .split('-')
        .map(word => word.charAt(0).toUpperCase() + word.slice(1))
        .join('');
    
    const IconComponent = (lucideIcons as Record<string, any>)[iconKey] || 
                         (lucideIcons as Record<string, any>)[iconName];
    
    return IconComponent || null;
};

// Fallback menu items
const fallbackMenuItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
];

// Fetch navigation items from API with cache
const fetchNavigationItems = async () => {
    try {
        isLoading.value = true;
        
        const data = await apiCache.fetchWithCache<{ data: NavigationItem[] }>(
            '/api/navigations?group=admin',
            {
                key: 'nav_admin_api_cache',
                ttl: 2 * 60 * 60 * 1000, // 2 hours
                version: '1.0',
            }
        );
        
        if (data.data && Array.isArray(data.data) && data.data.length > 0) {
            menuItems.value = data.data;
        } else {
            menuItems.value = [];
        }
    } catch (error) {
        console.error('Error fetching navigation items:', error);
        const cached = apiCache.loadFromCache<{ data: NavigationItem[] }>({
            key: 'nav_admin_api_cache',
            ttl: 2 * 60 * 60 * 1000,
        });
        
        if (cached?.data && Array.isArray(cached.data) && cached.data.length > 0) {
            menuItems.value = cached.data;
        } else {
            menuItems.value = [];
        }
    } finally {
        isLoading.value = false;
    }
};

// Convert navigation items to NavItem format
const mainNavItems = computed<NavItem[]>(() => {
    if (menuItems.value.length > 0) {
        return menuItems.value.map(item => ({
            title: item.title,
            href: item.href,
            icon: getIconComponent(item.icon),
        }));
    }
    return fallbackMenuItems;
});

// Check if item has children
const hasChildren = (href: string): boolean => {
    const item = menuItems.value.find(m => m.href === href);
    return !!(item?.children?.length || item?.has_children);
};

const rightNavItems: NavItem[] = [
    {
        title: 'Repository',
        href: 'https://github.com/laravel/vue-starter-kit',
        icon: Folder,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits#vue',
        icon: BookOpen,
    },
];

onMounted(() => {
    fetchNavigationItems();
});
</script>

<template>
    <div>
        <div class="border-b border-sidebar-border/80">
            <div class="mx-auto flex h-16 items-center px-4 md:max-w-7xl">
                <!-- Mobile Menu -->
                <div class="lg:hidden">
                    <Sheet>
                        <SheetTrigger :as-child="true">
                            <Button
                                variant="ghost"
                                size="icon"
                                class="mr-2 h-9 w-9"
                            >
                                <Menu class="h-5 w-5" />
                            </Button>
                        </SheetTrigger>
                        <SheetContent side="left" class="w-[300px] p-6">
                            <SheetTitle class="sr-only"
                                >Navigation Menu</SheetTitle
                            >
                            <SheetHeader class="flex justify-start text-left">
                                <AppLogoIcon
                                    class="size-6 fill-current text-black dark:text-white"
                                />
                            </SheetHeader>
                            <div
                                class="flex h-full flex-1 flex-col justify-between space-y-4 py-6"
                            >
                                <nav class="-mx-3 space-y-1">
                                    <Link
                                        v-for="item in mainNavItems"
                                        :key="item.title"
                                        :href="item.href"
                                        class="flex items-center gap-x-3 rounded-lg px-3 py-2 text-sm font-medium hover:bg-accent"
                                        :class="activeItemStyles(item.href)"
                                    >
                                        <component
                                            v-if="item.icon"
                                            :is="item.icon"
                                            class="h-5 w-5"
                                        />
                                        {{ item.title }}
                                    </Link>
                                </nav>
                                <div class="flex flex-col space-y-4">
                                    <a
                                        v-for="item in rightNavItems"
                                        :key="item.title"
                                        :href="toUrl(item.href)"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="flex items-center space-x-2 text-sm font-medium"
                                    >
                                        <component
                                            v-if="item.icon"
                                            :is="item.icon"
                                            class="h-5 w-5"
                                        />
                                        <span>{{ item.title }}</span>
                                    </a>
                                </div>
                            </div>
                        </SheetContent>
                    </Sheet>
                </div>

                <Link :href="dashboard()" class="flex items-center gap-x-2">
                    <AppLogo />
                </Link>

                <!-- Contact Information Columns -->
                <div class="hidden flex-1 items-center justify-center gap-6 lg:flex">
                    <div class="flex items-center gap-2">
                        <Phone class="h-5 w-5 text-gray-700 dark:text-gray-300" />
                        <div class="flex flex-col">
                            <a href="tel:+37368582157" class="text-sm font-bold text-gray-900 hover:text-primary dark:text-white">
                                +373 68 582 157
                            </a>
                            <a href="mailto:sales@plotter.md" class="text-xs text-gray-600 hover:text-primary dark:text-gray-400">
                                sales@plotter.md
                            </a>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <Phone class="h-5 w-5 text-gray-700 dark:text-gray-300" />
                        <div class="flex flex-col">
                            <a href="tel:+37360169285" class="text-sm font-bold text-gray-900 hover:text-primary dark:text-white">
                                +373 60 169 285
                            </a>
                            <a href="mailto:info@plotter.md" class="text-xs text-gray-600 hover:text-primary dark:text-gray-400">
                                info@plotter.md
                            </a>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <Phone class="h-5 w-5 text-gray-700 dark:text-gray-300" />
                        <div class="flex flex-col">
                            <a href="tel:+37360169285" class="text-sm font-bold text-gray-900 hover:text-primary dark:text-white">
                                +373 60 169 285
                            </a>
                            <a href="mailto:office@plotter.md" class="text-xs text-gray-600 hover:text-primary dark:text-gray-400">
                                office@plotter.md
                            </a>
                        </div>
                    </div>
                </div>

                <div class="ml-auto flex items-center space-x-2">
                    <div class="relative flex items-center space-x-1">
                        <Button
                            variant="ghost"
                            size="icon"
                            class="group h-9 w-9 cursor-pointer"
                        >
                            <Search
                                class="size-5 opacity-80 group-hover:opacity-100"
                            />
                        </Button>

                        <div class="hidden space-x-1 lg:flex">
                            <template
                                v-for="item in rightNavItems"
                                :key="item.title"
                            >
                                <TooltipProvider :delay-duration="0">
                                    <Tooltip>
                                        <TooltipTrigger>
                                            <Button
                                                variant="ghost"
                                                size="icon"
                                                as-child
                                                class="group h-9 w-9 cursor-pointer"
                                            >
                                                <a
                                                    :href="toUrl(item.href)"
                                                    target="_blank"
                                                    rel="noopener noreferrer"
                                                >
                                                    <span class="sr-only">{{
                                                        item.title
                                                    }}</span>
                                                    <component
                                                        :is="item.icon"
                                                        class="size-5 opacity-80 group-hover:opacity-100"
                                                    />
                                                </a>
                                            </Button>
                                        </TooltipTrigger>
                                        <TooltipContent>
                                            <p>{{ item.title }}</p>
                                        </TooltipContent>
                                    </Tooltip>
                                </TooltipProvider>
                            </template>
                        </div>
                    </div>

                    <DropdownMenu>
                        <DropdownMenuTrigger :as-child="true">
                            <Button
                                variant="ghost"
                                size="icon"
                                class="relative size-10 w-auto rounded-full p-1 focus-within:ring-2 focus-within:ring-primary"
                            >
                                <Avatar
                                    class="size-8 overflow-hidden rounded-full"
                                >
                                    <AvatarImage
                                        v-if="auth.user.avatar"
                                        :src="auth.user.avatar"
                                        :alt="auth.user.name"
                                    />
                                    <AvatarFallback
                                        class="rounded-lg bg-neutral-200 font-semibold text-black dark:bg-neutral-700 dark:text-white"
                                    >
                                        {{ getInitials(auth.user?.name) }}
                                    </AvatarFallback>
                                </Avatar>
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end" class="w-56">
                            <UserMenuContent :user="auth.user" />
                        </DropdownMenuContent>
                    </DropdownMenu>
                </div>
            </div>
        </div>

        <!-- Desktop Navigation Bar -->
        <div class="hidden border-b border-gray-200/80 bg-[#6B7A47] lg:block">
            <div class="mx-auto flex h-12 items-center justify-center px-4 md:max-w-7xl">
                <NavigationMenu class="flex h-full w-full items-stretch justify-center">
                    <NavigationMenuList
                        class="flex h-full items-stretch justify-center space-x-2"
                    >
                        <template v-if="!isLoading && mainNavItems.length > 0">
                            <NavigationMenuItem
                                v-for="(item, index) in mainNavItems"
                                :key="index"
                                class="relative flex h-full items-center"
                            >
                                <Link
                                    :class="[
                                        'group relative flex h-full items-center gap-1.5 rounded-md px-4 py-2 text-sm font-semibold uppercase tracking-wide text-white transition-all duration-200 hover:bg-white/10',
                                        isCurrentRoute(item.href) ? 'bg-white/10' : '',
                                    ]"
                                    :href="item.href"
                                >
                                    <component
                                        v-if="item.icon"
                                        :is="item.icon"
                                        class="h-4 w-4"
                                    />
                                    <span>{{ item.title }}</span>
                                    <ChevronDown
                                        v-if="hasChildren(toUrl(item.href))"
                                        class="h-4 w-4 transition-transform duration-200 group-hover:rotate-180"
                                    />
                                </Link>
                                <div
                                    v-if="isCurrentRoute(item.href)"
                                    class="absolute bottom-0 left-0 h-0.5 w-full translate-y-px bg-white"
                                ></div>
                            </NavigationMenuItem>
                        </template>
                        <template v-else-if="isLoading">
                            <div class="flex h-9 items-center gap-2 px-3">
                                <div class="h-4 w-20 animate-pulse rounded-md bg-white/20"></div>
                                <div class="h-4 w-20 animate-pulse rounded-md bg-white/20"></div>
                            </div>
                        </template>
                    </NavigationMenuList>
                </NavigationMenu>
            </div>
        </div>

        <div
            v-if="props.breadcrumbs.length > 1"
            class="flex w-full border-b border-sidebar-border/70"
        >
            <div
                class="mx-auto flex h-12 w-full items-center justify-start px-4 text-neutral-500 md:max-w-7xl"
            >
                <Breadcrumbs :breadcrumbs="breadcrumbs" />
            </div>
        </div>
    </div>
</template>
