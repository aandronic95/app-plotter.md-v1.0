<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import { FileText } from 'lucide-vue-next';
import { useTranslations } from '@/composables/useTranslations';

interface Page {
    id: number;
    title: string;
    slug: string;
    excerpt?: string;
}

interface Props {
    limit?: number;
    showTitle?: boolean;
    title?: string;
}

const props = withDefaults(defineProps<Props>(), {
    limit: 5,
    showTitle: true,
    title: undefined,
});

const { t } = useTranslations();
const pages = ref<Page[]>([]);
const isLoading = ref(true);

const fetchPages = async () => {
    try {
        const response = await fetch('/api/pages?published_only=true&order_by=sort_order&order_dir=asc');
        const data = await response.json();
        
        if (data.data) {
            pages.value = props.limit ? data.data.slice(0, props.limit) : data.data;
        }
    } catch (error) {
        console.error('Error fetching pages:', error);
    } finally {
        isLoading.value = false;
    }
};

onMounted(() => {
    fetchPages();
});
</script>

<template>
    <div class="pages-list">
        <h3
            v-if="showTitle"
            class="mb-4 text-lg font-semibold text-gray-900 dark:text-white"
        >
            {{ title || t('pages') }}
        </h3>
        
        <div v-if="isLoading" class="space-y-2">
            <div
                v-for="i in limit"
                :key="i"
                class="h-4 animate-pulse rounded bg-gray-200 dark:bg-gray-700"
            />
        </div>
        
        <ul
            v-else-if="pages.length > 0"
            class="space-y-2"
        >
            <li
                v-for="page in pages"
                :key="page.id"
            >
                <Link
                    :href="`/${page.slug}`"
                    class="flex items-start gap-2 text-sm text-gray-600 transition-colors hover:text-primary-600 dark:text-gray-400 dark:hover:text-primary-400"
                >
                    <FileText class="mt-0.5 h-4 w-4 flex-shrink-0" />
                    <span>{{ page.title }}</span>
                </Link>
            </li>
        </ul>
        
        <p
            v-else
            class="text-sm text-gray-500 dark:text-gray-400"
        >
            {{ t('no_pages_available') }}
        </p>
        
        <Link
            v-if="pages.length > 0 && limit"
            href="/pages"
            class="mt-4 inline-block text-sm text-primary-600 hover:text-primary-700 dark:text-primary-400"
        >
            {{ t('view_all_pages') }}
        </Link>
    </div>
</template>

