<script setup lang="ts">
import { Phone } from 'lucide-vue-next';
import { computed } from 'vue';
import { useSiteSettings } from '@/composables/useSiteSettings';

const { siteSettings } = useSiteSettings();

// Check if there are any header contacts to display
const hasHeaderContacts = computed(() => {
    if (!siteSettings.value) return false;
    return !!(
        siteSettings.value.header_contact_1_phone ||
        siteSettings.value.header_contact_1_email ||
        siteSettings.value.header_contact_2_phone ||
        siteSettings.value.header_contact_2_email ||
        siteSettings.value.header_contact_3_phone ||
        siteSettings.value.header_contact_3_email
    );
});
</script>

<template>
    <div v-if="hasHeaderContacts" class="flex items-center gap-4 lg:gap-6">
        <div v-if="siteSettings?.header_contact_1_phone || siteSettings?.header_contact_1_email" class="flex items-center gap-2 flex-shrink-0">
            <Phone class="h-5 w-5 text-gray-900 dark:text-white flex-shrink-0" />
            <div class="flex flex-col">
                <a
                    v-if="siteSettings?.header_contact_1_phone"
                    :href="`tel:${siteSettings.header_contact_1_phone.replace(/\s/g, '')}`"
                    class="text-sm font-bold text-gray-900 dark:text-white hover:text-gray-900/80 dark:hover:text-white/80 transition-colors duration-200 whitespace-nowrap"
                >
                    {{ siteSettings.header_contact_1_phone }}
                </a>
                <a
                    v-if="siteSettings?.header_contact_1_email"
                    :href="`mailto:${siteSettings.header_contact_1_email}`"
                    class="text-xs text-gray-300 dark:text-gray-400 hover:text-white/80 transition-colors duration-200 whitespace-nowrap"
                >
                    {{ siteSettings.header_contact_1_email }}
                </a>
            </div>
        </div>
        <div v-if="siteSettings?.header_contact_2_phone || siteSettings?.header_contact_2_email" class="flex items-center gap-2 flex-shrink-0">
            <Phone class="h-5 w-5 text-gray-900 dark:text-white flex-shrink-0" />
            <div class="flex flex-col">
                <a
                    v-if="siteSettings?.header_contact_2_phone"
                    :href="`tel:${siteSettings.header_contact_2_phone.replace(/\s/g, '')}`"
                    class="text-sm font-bold text-gray-900 dark:text-white hover:text-gray-900/80 dark:hover:text-white/80 transition-colors duration-200 whitespace-nowrap"
                >
                    {{ siteSettings.header_contact_2_phone }}
                </a>
                <a
                    v-if="siteSettings?.header_contact_2_email"
                    :href="`mailto:${siteSettings.header_contact_2_email}`"
                    class="text-xs text-gray-300 dark:text-gray-400 hover:text-gray-900/80 dark:hover:text-white/80 transition-colors duration-200 whitespace-nowrap"
                >
                    {{ siteSettings.header_contact_2_email }}
                </a>
            </div>
        </div>
        <div v-if="siteSettings?.header_contact_3_phone || siteSettings?.header_contact_3_email" class="flex items-center gap-2 flex-shrink-0">
            <Phone class="h-5 w-5 text-gray-900 dark:text-white flex-shrink-0" />
            <div class="flex flex-col">
                <a
                    v-if="siteSettings?.header_contact_3_phone"
                    :href="`tel:${siteSettings.header_contact_3_phone.replace(/\s/g, '')}`"
                    class="text-sm font-bold text-gray-900 dark:text-white hover:text-gray-900/80 dark:hover:text-white/80 transition-colors duration-200 whitespace-nowrap"
                >
                    {{ siteSettings.header_contact_3_phone }}
                </a>
                <a
                    v-if="siteSettings?.header_contact_3_email"
                    :href="`mailto:${siteSettings.header_contact_3_email}`"
                    class="text-xs text-gray-300 dark:text-gray-400 hover:text-gray-900/80 dark:hover:text-white/80 transition-colors duration-200 whitespace-nowrap"
                >
                    {{ siteSettings.header_contact_3_email }}
                </a>
            </div>
        </div>
    </div>
</template>

