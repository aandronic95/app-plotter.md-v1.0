<script setup lang="ts">
interface Category {
    id: number;
    name: string;
    slug: string;
    count?: number;
    children?: Category[];
}

interface Props {
    categories: Category[];
    level?: number;
    activeCategorySlug?: string;
    filters: {
        category?: string;
    };
}

const props = withDefaults(defineProps<Props>(), {
    level: 1,
});

const emit = defineEmits<{
    'category-click': [slug: string];
}>();

const handleClick = (slug: string) => {
    emit('category-click', slug);
};

const isActive = (slug: string) => {
    return props.filters.category === slug;
};
</script>

<template>
    <div
        v-if="categories && categories.length > 0"
        :class="[
            'mt-1 space-y-1',
            level === 1 ? 'ml-4' : level === 2 ? 'ml-8' : 'ml-12',
        ]"
    >
        <template v-for="category in categories" :key="category.id">
            <button
                @click="handleClick(category.slug)"
                :class="[
                    'w-full rounded-lg px-3 text-left transition-colors',
                    level === 1 ? 'py-1.5 text-xs' : 'py-1 text-xs',
                    isActive(category.slug)
                        ? 'bg-gray-900 text-white dark:bg-gray-700'
                        : 'text-gray-600 hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700',
                ]"
            >
                <span class="dark:text-white">{{ category.name }}</span>
            </button>
            <!-- Recursive: Render nested children if they exist -->
            <CategoryChildren
                v-if="category.children && category.children.length > 0"
                :categories="category.children"
                :level="level + 1"
                :filters="filters"
                @category-click="handleClick"
            />
        </template>
    </div>
</template>

