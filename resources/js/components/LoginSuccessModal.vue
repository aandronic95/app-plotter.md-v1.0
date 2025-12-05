<script setup lang="ts">
import Dialog from '@/components/ui/dialog/Dialog.vue';
import DialogContent from '@/components/ui/dialog/DialogContent.vue';
import { CheckCircle, Gift, Star, ShoppingBag, Heart } from 'lucide-vue-next';
import { ref, computed, watch, onMounted } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { useSiteSettings } from '@/composables/useSiteSettings';

const page = usePage();
const { siteSettings, fetchSiteSettings } = useSiteSettings();
const showLoginModal = ref(false);
const flash = computed(() => page.props.flash as { login_success?: boolean } | undefined);

// Function to check if modal should be shown
const checkAndShowModal = () => {
    if (flash.value?.login_success && siteSettings.value?.show_login_modal !== false) {
        showLoginModal.value = true;
    }
};

// Watch for login success flash message
watch(flash, () => {
    checkAndShowModal();
}, { immediate: true });

// Watch for site settings changes
watch(siteSettings, () => {
    checkAndShowModal();
}, { immediate: true });

// Fetch site settings and check for login success on mount
onMounted(async () => {
    await fetchSiteSettings();
    checkAndShowModal();
});
</script>

<template>
    <!-- Login Success Modal -->
    <Dialog v-model:open="showLoginModal">
        <DialogContent class="max-w-md">
            <div class="flex flex-col items-center text-center space-y-4">
                <!-- Success Icon -->
                <div class="flex items-center justify-center w-16 h-16 rounded-full bg-green-100 dark:bg-green-900/20 mb-2">
                    <CheckCircle class="w-10 h-10 text-green-600 dark:text-green-400" />
                </div>

                <!-- Title -->
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                    Bine ai venit!
                </h2>

                <!-- Subtitle -->
                <p class="text-gray-600 dark:text-gray-400">
                    Te-ai logat cu succes! Iată ce poți face acum:
                </p>

                <!-- Benefits List -->
                <div class="w-full space-y-3 mt-4">
                    <div class="flex items-start space-x-3 p-3 rounded-lg bg-blue-50 dark:bg-blue-900/20">
                        <ShoppingBag class="w-5 h-5 text-blue-600 dark:text-blue-400 mt-0.5 flex-shrink-0" />
                        <div class="text-left">
                            <p class="font-semibold text-gray-900 dark:text-white">Cumpărături rapide</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Adaugă produse în coș și finalizează comenzi cu ușurință</p>
                        </div>
                    </div>

                    <div class="flex items-start space-x-3 p-3 rounded-lg bg-pink-50 dark:bg-pink-900/20">
                        <Heart class="w-5 h-5 text-pink-600 dark:text-pink-400 mt-0.5 flex-shrink-0" />
                        <div class="text-left">
                            <p class="font-semibold text-gray-900 dark:text-white">Lista de dorințe</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Salvează produsele preferate pentru mai târziu</p>
                        </div>
                    </div>

                    <div class="flex items-start space-x-3 p-3 rounded-lg bg-yellow-50 dark:bg-yellow-900/20">
                        <Star class="w-5 h-5 text-yellow-600 dark:text-yellow-400 mt-0.5 flex-shrink-0" />
                        <div class="text-left">
                            <p class="font-semibold text-gray-900 dark:text-white">Oferte exclusive</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Primește notificări despre promoții și reduceri speciale</p>
                        </div>
                    </div>

                    <div class="flex items-start space-x-3 p-3 rounded-lg bg-purple-50 dark:bg-purple-900/20">
                        <Gift class="w-5 h-5 text-purple-600 dark:text-purple-400 mt-0.5 flex-shrink-0" />
                        <div class="text-left">
                            <p class="font-semibold text-gray-900 dark:text-white">Profil personalizat</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Gestionează adresele și comenzile tale într-un singur loc</p>
                        </div>
                    </div>
                </div>

                <!-- Close Button -->
                <button
                    @click="showLoginModal = false"
                    class="mt-4 w-full px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors"
                >
                    Înțeles, mulțumesc!
                </button>
            </div>
        </DialogContent>
    </Dialog>
</template>

