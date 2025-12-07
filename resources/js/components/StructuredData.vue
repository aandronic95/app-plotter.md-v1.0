<script setup lang="ts">
import { computed, onMounted } from 'vue';

interface Props {
    type: 'Organization' | 'Product' | 'BreadcrumbList' | 'WebSite';
    data?: Record<string, any>;
}

const props = defineProps<Props>();

const jsonLd = computed(() => {
    const baseData: any = {
        '@context': 'https://schema.org',
        '@type': props.type,
    };

    if (props.data) {
        Object.assign(baseData, props.data);
    }

    return JSON.stringify(baseData);
});

onMounted(() => {
    // Script tag is already in template, this is just for reference
});
</script>

<template>
    <script type="application/ld+json" v-html="jsonLd" />
</template>

