<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { useSiteSettings } from '@/composables/useSiteSettings';
import { computed, onMounted } from 'vue';

const page = usePage();
const { siteSettings, fetchSiteSettings, isLoading } = useSiteSettings();

const appName = computed(() => {
    if (siteSettings.value?.site_name) {
        return siteSettings.value.site_name;
    }
    return (page.props.name as string) || 'Laravel';
});

const logoIcon = computed(() => {
    return siteSettings.value?.site_logo_icon;
});

const showSiteName = computed(() => {
    return siteSettings.value?.show_site_name ?? true;
});

const showLogo = computed(() => {
    return siteSettings.value?.show_logo ?? true;
});

onMounted(() => {
    fetchSiteSettings();
});
</script>

<template>
    <div
        v-if="showLogo"
        class="flex aspect-square size-8 items-center justify-center rounded-md bg-transparent text-sidebar-primary-foreground"
    >
        <AppLogoIcon 
            v-if="!logoIcon"
            class="size-5 fill-current text-white dark:text-black" 
        />
        <img
            v-else
            :src="logoIcon"
            :alt="appName"
            class="size-5 object-contain"
        />
    </div>
    <div v-if="showSiteName" class="ml-1 grid flex-1 text-left text-sm">
        <span class="mb-0.5 truncate leading-tight font-semibold"
            >{{ appName }}</span
        >
    </div>
</template>
