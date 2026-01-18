<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { Phone } from 'lucide-vue-next';
import { useApiCache } from '@/composables/useApiCache';

interface HeaderContact {
    id: number;
    title: string;
    phone?: string | null;
    email?: string | null;
    order: number;
    is_active: boolean;
}

const apiCache = useApiCache();
const contacts = ref<HeaderContact[]>([]);
const isLoading = ref(true);

const fetchContacts = async () => {
    try {
        isLoading.value = true;
        
        const data = await apiCache.fetchWithCache<{ data: HeaderContact[] }>(
            '/api/header-contacts',
            {
                key: 'header_contacts_api_cache',
                ttl: 2 * 60 * 60 * 1000, // 2 hours
                version: '1.0',
            }
        );
        
        if (data.data && Array.isArray(data.data)) {
            contacts.value = data.data;
        }
    } catch (error) {
        console.error('Error fetching header contacts:', error);
        // Try to load from cache as fallback
        const cached = apiCache.loadFromCache<{ data: HeaderContact[] }>({
            key: 'header_contacts_api_cache',
            ttl: 2 * 60 * 60 * 1000,
        });
        
        if (cached?.data && Array.isArray(cached.data)) {
            contacts.value = cached.data;
        } else {
            contacts.value = [];
        }
    } finally {
        isLoading.value = false;
    }
};

const hasAnyContact = computed(() => {
    return contacts.value.length > 0;
});

onMounted(() => {
    fetchContacts();
});
</script>

<template>
    <div v-if="hasAnyContact" class="flex flex-1 items-center justify-center gap-6">
        <!-- Contact Blocks -->
        <div 
            v-for="contact in contacts" 
            :key="contact.id"
            class="flex items-center gap-3 rounded-lg bg-gray-50/50 px-5 py-2.5 backdrop-blur-sm transition-all duration-200 hover:bg-gray-50/80 dark:bg-gray-800/30 dark:hover:bg-gray-800/50"
        >
            <div class="flex h-9 w-9 items-center justify-center rounded-full bg-primary/5">
                <Phone class="h-4 w-4 text-primary" />
            </div>
            <div class="flex flex-col">
                <a
                    v-if="contact.phone"
                    :href="`tel:${contact.phone.replace(/\s/g, '')}`"
                    class="text-sm font-bold text-gray-800 transition-colors duration-200 hover:text-primary dark:text-white"
                >
                    {{ contact.phone }}
                </a>
                <a
                    v-if="contact.email"
                    :href="`mailto:${contact.email}`"
                    class="text-xs text-gray-500 transition-colors duration-200 hover:text-primary dark:text-gray-400"
                >
                    {{ contact.email }}
                </a>
            </div>
        </div>
    </div>
</template>

