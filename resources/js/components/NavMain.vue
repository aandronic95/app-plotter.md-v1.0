<script setup lang="ts">
import {
    SidebarGroup,
    SidebarGroupLabel,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    SidebarMenuSub,
    SidebarMenuSubButton,
    SidebarMenuSubItem,
} from '@/components/ui/sidebar';
import { urlIsActive } from '@/lib/utils';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { computed, onMounted, ref } from 'vue';
import { ChevronDown, ChevronRight, List } from 'lucide-vue-next';
import * as lucideIcons from 'lucide-vue-next';
import { useTranslations } from '@/composables/useTranslations';

interface NavigationItem {
    id: number;
    title: string;
    href: string;
    icon?: string;
    is_external?: boolean;
    target?: string;
}

interface CategoryGroup {
    name: string;
    items: NavigationItem[];
}

const props = defineProps<{
    items?: NavItem[];
    group?: string;
}>();

const page = usePage();
const { t } = useTranslations();
const navigationItems = ref<NavigationItem[]>([]);
const isLoading = ref(true);
const categories = ref<CategoryGroup[]>([]);
const isCategoriesLoading = ref(false);
const isCategoriesExpanded = ref(false);

// Get icon component by name
const getIconComponent = (iconName?: string) => {
    if (!iconName) return null;
    
    // Convert icon name to PascalCase if needed
    const iconKey = iconName
        .split('-')
        .map(word => word.charAt(0).toUpperCase() + word.slice(1))
        .join('');
    
    // Try to find icon in lucide-vue-next
    const IconComponent = (lucideIcons as Record<string, any>)[iconKey] || 
                         (lucideIcons as Record<string, any>)[iconName];
    
    return IconComponent || null;
};

// Fetch navigation items from API
const fetchNavigationItems = async () => {
    try {
        const group = props.group || 'main';
        const response = await fetch(`/api/navigations?group=${group}`);
        const data = await response.json();
        navigationItems.value = data.data || [];
    } catch (error) {
        console.error('Error fetching navigation items:', error);
        // Fallback to props.items if API fails
        if (props.items) {
            navigationItems.value = props.items.map(item => ({
                id: Math.random(),
                title: item.title,
                href: item.href as string,
                icon: item.icon?.name,
            }));
        }
    } finally {
        isLoading.value = false;
    }
};

// Fetch categories from API
const fetchCategories = async () => {
    try {
        isCategoriesLoading.value = true;
        const group = props.group || 'main';
        const url = `/api/navigations/categories?group=${group}`;
        console.log('Fetching categories from:', url);
        
        const response = await fetch(url);
        
        if (!response.ok) {
            const errorText = await response.text();
            console.error('HTTP error:', response.status, errorText);
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        console.log('Categories API response:', data);
        
        if (data.data && Array.isArray(data.data) && data.data.length > 0) {
            categories.value = data.data;
            console.log('Categories loaded successfully:', categories.value.length, 'categories');
        } else {
            console.warn('No categories found in response:', data);
            categories.value = [];
        }
    } catch (error) {
        console.error('Error fetching categories:', error);
        categories.value = [];
    } finally {
        isCategoriesLoading.value = false;
    }
};

// Toggle categories expansion
const toggleCategories = () => {
    isCategoriesExpanded.value = !isCategoriesExpanded.value;
};

// Convert navigation items to NavItem format
const navItems = computed<NavItem[]>(() => {
    if (props.items && navigationItems.value.length === 0) {
        return props.items;
    }
    
    return navigationItems.value.map(item => ({
        title: item.title,
        href: item.href,
        icon: getIconComponent(item.icon),
    }));
});

onMounted(() => {
    fetchNavigationItems();
    fetchCategories();
});
</script>

<template>
    <SidebarGroup class="px-2 py-0">
        <SidebarGroupLabel>Platform</SidebarGroupLabel>
        <SidebarMenu v-if="!isLoading">
            <!-- Categories Item (First) -->
            <SidebarMenuItem v-if="!isCategoriesLoading && categories.length > 0" key="categories-item">
                <SidebarMenuButton
                    @click="toggleCategories"
                    :is-active="false"
                    :tooltip="t('categories')"
                >
                    <List />
                    <span>{{ t('categories') }}</span>
                    <component
                        :is="isCategoriesExpanded ? ChevronDown : ChevronRight"
                        class="ml-auto"
                    />
                </SidebarMenuButton>
                <SidebarMenuSub v-show="isCategoriesExpanded">
                    <template v-for="category in categories" :key="category.name">
                        <div class="px-2 py-1.5">
                            <div class="text-xs font-semibold text-muted-foreground">
                                {{ category.name }}
                            </div>
                        </div>
                        <SidebarMenuSubItem
                            v-for="item in category.items"
                            :key="item.id"
                        >
                            <SidebarMenuSubButton
                                as-child
                                :is-active="urlIsActive(item.href, page.url)"
                            >
                                <Link
                                    :href="item.href"
                                    :target="item.is_external ? (item.target || '_blank') : '_self'"
                                    :rel="item.is_external ? 'noopener noreferrer' : undefined"
                                >
                                    {{ item.title }}
                                </Link>
                            </SidebarMenuSubButton>
                        </SidebarMenuSubItem>
                    </template>
                </SidebarMenuSub>
            </SidebarMenuItem>

            <!-- Debug: Show if categories are loading or empty -->
            <SidebarMenuItem v-if="isCategoriesLoading" key="categories-loading">
                <SidebarMenuButton disabled>
                    <List />
                    <span>{{ t('loading_categories') }}</span>
                </SidebarMenuButton>
            </SidebarMenuItem>

            <!-- Other Navigation Items -->
            <SidebarMenuItem v-for="item in navItems" :key="item.title">
                <SidebarMenuButton
                    as-child
                    :is-active="urlIsActive(item.href, page.url)"
                    :tooltip="item.title"
                >
                    <Link 
                        :href="item.href"
                        :target="navigationItems.find(n => n.href === item.href)?.target || '_self'"
                    >
                        <component v-if="item.icon" :is="item.icon" />
                        <span>{{ item.title }}</span>
                    </Link>
                </SidebarMenuButton>
            </SidebarMenuItem>
        </SidebarMenu>
        <div v-else class="px-2 py-4 text-sm text-muted-foreground">
            {{ t('loading') }}
        </div>
    </SidebarGroup>
</template>
