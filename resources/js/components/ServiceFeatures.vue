<script setup lang="ts">
import { computed, onMounted } from 'vue';
import { Zap, Settings, Truck, Smile, Star } from 'lucide-vue-next';
import * as lucideIcons from 'lucide-vue-next';
import { useSiteSettings } from '@/composables/useSiteSettings';

interface ServiceFeature {
    icon: any;
    title: string;
    description: string;
}

const { siteSettings, fetchSiteSettings } = useSiteSettings();

// Map icon names to components
const getIconComponent = (iconName: string) => {
    const iconKey = iconName as keyof typeof lucideIcons;
    return (lucideIcons[iconKey] as any) || Zap; // Fallback to Zap if icon not found
};

const features = computed<ServiceFeature[]>(() => {
    if (!siteSettings.value) {
        return [];
    }

    const settings = siteSettings.value;

    return [
        {
            icon: getIconComponent(settings.service_feature_1_icon || 'Zap'),
            title: settings.service_feature_1_title || 'RAPIDITATE',
            description: settings.service_feature_1_description || '',
        },
        {
            icon: getIconComponent(settings.service_feature_2_icon || 'Settings'),
            title: settings.service_feature_2_title || 'SUPORT ÎN ALEGERE',
            description: settings.service_feature_2_description || '',
        },
        {
            icon: getIconComponent(settings.service_feature_3_icon || 'Truck'),
            title: settings.service_feature_3_title || 'TRANSPORT',
            description: settings.service_feature_3_description || '',
        },
        {
            icon: getIconComponent(settings.service_feature_4_icon || 'Smile'),
            title: settings.service_feature_4_title || 'CALITATE GARANTATĂ',
            description: settings.service_feature_4_description || '',
        },
    ];
});

onMounted(async () => {
    await fetchSiteSettings();
});
</script>

<template>
    <div class="w-full bg-gray-50 dark:bg-gray-900 py-12">
        <div class="mx-auto max-w-7xl px-4 md:px-6">
            <div class="rounded-lg bg-white p-8 dark:bg-gray-800">
                <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-4">
                    <div
                        v-for="(feature, index) in features"
                        :key="index"
                        class="flex flex-col gap-4"
                    >
                        <!-- Icon Container -->
                        <div class="flex items-start gap-4">
                            <div class="relative flex-shrink-0">
                                <!-- Stars for Calitate Garantată - positioned above -->
                                <div
                                    v-if="feature.title === 'CALITATE GARANTATĂ'"
                                    class="absolute -top-2 left-1/2 flex -translate-x-1/2 gap-0.5"
                                >
                                    <Star
                                        class="h-3 w-3 fill-teal-700 text-teal-700 dark:fill-teal-500 dark:text-teal-500"
                                    />
                                    <Star
                                        class="h-3 w-3 fill-teal-700 text-teal-700 dark:fill-teal-500 dark:text-teal-500"
                                    />
                                </div>
                                <div
                                    class="flex h-12 w-12 items-center justify-center rounded-lg"
                                >
                                    <component
                                        :is="feature.icon"
                                        class="h-6 w-6 text-teal-700 dark:text-teal-500"
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- Title -->
                        <h3
                            class="text-lg font-bold uppercase tracking-tight text-gray-800 dark:text-white"
                        >
                            {{ feature.title }}
                        </h3>

                        <!-- Description -->
                        <p class="text-sm leading-relaxed text-gray-600 dark:text-gray-300">
                            {{ feature.description }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

