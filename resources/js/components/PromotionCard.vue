<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { Calendar } from 'lucide-vue-next';
import { computed } from 'vue';
import { useTranslations } from '@/composables/useTranslations';

interface Promotion {
    id: number;
    title: string;
    description: string | null;
    banner: string;
    external_link: string | null;
    page_id: number | null;
    product_id: number | null;
    page?: {
        id: number;
        title: string;
        slug: string;
    } | null;
    product?: {
        id: number;
        name: string;
        slug: string;
    } | null;
    link: string | null;
    end_date: string | null;
    created_at: string;
    updated_at: string;
}

const props = defineProps<{
    promotion: Promotion;
}>();

const { t } = useTranslations();

// Calculate days remaining
const daysRemaining = computed(() => {
    if (!props.promotion.end_date) {
        return null; // No end date set
    }
    
    const endDate = new Date(props.promotion.end_date);
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    endDate.setHours(0, 0, 0, 0);
    
    const diffTime = endDate.getTime() - today.getTime();
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    
    return diffDays > 0 ? diffDays : 0;
});

// Format end date
const formattedEndDate = computed(() => {
    if (!props.promotion.end_date) {
        return null;
    }
    
    const endDate = new Date(props.promotion.end_date);
    return endDate.toLocaleDateString('ro-RO', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
    });
});

// Handle promotion click
const handleClick = () => {
    if (!props.promotion.link) {
        return;
    }

    // Check if it's an external link
    if (props.promotion.external_link) {
        window.open(props.promotion.external_link, '_blank');
        return;
    }

    // Check if it's a page link
    if (props.promotion.page_id && props.promotion.page?.slug) {
        router.visit(`/${props.promotion.page.slug}`);
        return;
    }

    // Check if it's a product link
    if (props.promotion.product_id && props.promotion.product?.slug) {
        router.visit(`/products/${props.promotion.product.slug}`);
        return;
    }
};
</script>

<template>
    <div
        class="group flex cursor-pointer flex-col overflow-hidden rounded-lg bg-white transition-all dark:bg-gray-800"
        @click="handleClick"
    >
        <!-- Banner Image -->
        <div class="relative aspect-[4/3] w-full overflow-hidden bg-gray-100 dark:bg-gray-800">
            <img
                :src="promotion.banner"
                :alt="promotion.title"
                class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105"
            />
        </div>

        <!-- Content -->
        <div class="flex flex-1 flex-col p-4">
            <!-- Duration Info -->
            <div
                v-if="promotion.end_date"
                class="mb-3 flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400"
            >
                <Calendar class="h-4 w-4" />
                <span v-if="daysRemaining !== null && daysRemaining > 0">
                    {{ t('days_remaining') }} {{ daysRemaining }} {{ daysRemaining === 1 ? t('day') : t('days') }}
                </span>
                <span v-else-if="daysRemaining === 0" class="text-red-600 dark:text-red-400">
                    {{ t('expired') }}
                </span>
                <span v-else class="text-gray-500 dark:text-gray-400">
                    {{ t('no_deadline') }}
                </span>
                <span v-if="formattedEndDate" class="mx-1">•</span>
                <span v-if="formattedEndDate">{{ t('until') }} {{ formattedEndDate }}</span>
            </div>

            <!-- Title -->
            <h3 class="mb-2 line-clamp-2 text-lg font-semibold text-gray-900 transition-colors group-hover:text-primary-600 dark:text-white dark:group-hover:text-primary-400">
                {{ promotion.title }}
            </h3>

            <!-- Description -->
            <p
                v-if="promotion.description"
                class="line-clamp-2 text-sm text-gray-600 dark:text-gray-400"
            >
                {{ promotion.description }}
            </p>
        </div>
    </div>
</template>

