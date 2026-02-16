<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ChevronRight, ChevronDown, type LucideIcon } from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface Category {
    id: number;
    name: string;
    slug: string;
    icon?: string;
    count?: number;
    children?: Category[];
}

interface Props {
    category: Category;
    activeCategorySlug?: string;
    level?: number;
    iconMap?: Record<string, LucideIcon>;
}

const props = withDefaults(defineProps<Props>(), {
    level: 0,
});

const expanded = ref(false);

const isActive = (slug: string) => {
    return props.activeCategorySlug === slug;
};

const hasActiveChild = (category: Category): boolean => {
    if (!category.children || category.children.length === 0) {
        return false;
    }
    if (category.children.some((child) => isActive(child.slug))) {
        return true;
    }
    return category.children.some((child) => hasActiveChild(child));
};

// Auto-expand if has active child
if (hasActiveChild(props.category) || isActive(props.category.slug)) {
    expanded.value = true;
}

const toggleExpand = (event: Event) => {
    event.preventDefault();
    event.stopPropagation();
    expanded.value = !expanded.value;
};

const getIcon = (iconName?: string): LucideIcon | null => {
    if (!iconName || !props.iconMap) return null;
    return props.iconMap[iconName.toLowerCase()] || null;
};

const paddingLeft = computed(() => {
    return props.level === 0 ? 'pl-4' : props.level === 1 ? 'pl-12' : `pl-${12 + (props.level - 1) * 4}`;
});
</script>

<template>
    <div class="w-full border-b border-gray-200 dark:border-gray-700 last:border-b-0">
        <div class="relative">
            <Link
                :href="`/categories/${category.slug}`"
                :class="[
                    'flex w-full items-center justify-between px-4 py-3 text-sm font-medium transition-all duration-200',
                    paddingLeft,
                    isActive(category.slug)
                        ? 'bg-primary-50 text-primary-900 dark:bg-primary-900/30 dark:text-primary-100 border-l-4 border-primary-500 dark:border-primary-400'
                        : 'text-gray-900 hover:bg-gray-50 dark:text-white dark:hover:bg-gray-800/50',
                ]"
            >
                <div class="flex items-center gap-3 flex-1">
                    <component
                        v-if="getIcon(category.icon)"
                        :is="getIcon(category.icon)"
                        class="h-5 w-5 flex-shrink-0 transition-colors"
                        :class="[
                            isActive(category.slug)
                                ? 'text-primary-600 dark:text-primary-400'
                                : 'text-cyan-500 dark:text-cyan-400',
                        ]"
                    />
                    <span class="text-sm font-medium dark:text-white">{{ category.name }}</span>
                </div>
                <div class="flex items-center gap-1">
                    <button
                        v-if="category.children && category.children.length > 0"
                        @click="toggleExpand"
                        class="p-1 hover:bg-gray-200 dark:hover:bg-gray-700 rounded transition-colors"
                        :aria-expanded="expanded"
                    >
                        <ChevronDown
                            v-if="expanded"
                            class="h-4 w-4 text-gray-500 dark:text-gray-400 transition-transform"
                        />
                        <ChevronRight
                            v-else
                            class="h-4 w-4 text-gray-500 dark:text-gray-400 transition-transform"
                        />
                    </button>
                    <ChevronRight
                        v-else
                        class="h-4 w-4 flex-shrink-0 text-gray-400 dark:text-gray-500"
                    />
                </div>
            </Link>
        </div>
        <!-- Nested Subcategories - Recursive -->
        <div
            v-if="category.children && category.children.length > 0 && expanded"
            class="bg-gray-50 dark:bg-gray-900/50 border-t border-gray-200 dark:border-gray-700"
        >
            <CategorySidebarItem
                v-for="child in category.children"
                :key="child.id"
                :category="child"
                :active-category-slug="activeCategorySlug"
                :level="level + 1"
                :icon-map="iconMap"
            />
        </div>
    </div>
</template>





