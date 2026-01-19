<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { useTranslations } from '@/composables/useTranslations';
import { useSiteSettings } from '@/composables/useSiteSettings';
import { Link } from '@inertiajs/vue3';

const { t } = useTranslations();
const { siteSettings, fetchSiteSettings } = useSiteSettings();

const formData = ref({
    name: '',
    email: '',
    phone: '',
    privacy_accepted: false,
});

const isLoading = ref(false);
const isSubmitted = ref(false);
const error = ref<string | null>(null);
const successMessage = ref<string | null>(null);

const showForm = computed(() => {
    return siteSettings.value?.show_newsletter_form ?? true;
});

const validatePhone = (phone: string): boolean => {
    // Format: 079123456 (9 cifre, începe cu 0)
    const phoneRegex = /^0\d{8}$/;
    return phoneRegex.test(phone);
};

const handleSubmit = async (e: Event) => {
    e.preventDefault();
    error.value = null;
    successMessage.value = null;

    // Validare
    if (!formData.value.name.trim()) {
        error.value = 'Numele este obligatoriu.';
        return;
    }

    if (!formData.value.email.trim()) {
        error.value = 'Email-ul este obligatoriu.';
        return;
    }

    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(formData.value.email)) {
        error.value = 'Email-ul nu este valid.';
        return;
    }

    if (formData.value.phone && !validatePhone(formData.value.phone)) {
        error.value = 'Telefonul trebuie să fie în formatul: 079123456';
        return;
    }

    if (!formData.value.privacy_accepted) {
        error.value = 'Trebuie să acceptați politica de confidențialitate.';
        return;
    }

    isLoading.value = true;

    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        
        if (!csrfToken) {
            error.value = 'Eroare de securitate. Vă rugăm să reîncărcați pagina.';
            isLoading.value = false;
            return;
        }

        const response = await fetch('/api/newsletter', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                name: formData.value.name.trim(),
                email: formData.value.email.trim(),
                phone: formData.value.phone.trim() || null,
                privacy_accepted: formData.value.privacy_accepted,
            }),
        });

        const data = await response.json();

        if (!response.ok) {
            if (data.errors) {
                const firstError = Object.values(data.errors)[0];
                error.value = Array.isArray(firstError) ? firstError[0] : firstError;
            } else {
                error.value = data.message || 'A apărut o eroare. Vă rugăm să încercați din nou.';
            }
            isLoading.value = false;
            return;
        }

        // Success
        successMessage.value = data.message || 'V-ați abonat cu succes la newsletter!';
        formData.value = {
            name: '',
            email: '',
            phone: '',
            privacy_accepted: false,
        };
        isSubmitted.value = true;

        // Reset success message after 5 seconds
        setTimeout(() => {
            isSubmitted.value = false;
            successMessage.value = null;
        }, 5000);
    } catch (err) {
        error.value = 'A apărut o eroare. Vă rugăm să încercați din nou.';
        console.error('Newsletter subscription error:', err);
    } finally {
        isLoading.value = false;
    }
};

onMounted(async () => {
    await fetchSiteSettings();
});
</script>

<template>
    <div v-if="showForm" class="w-full">
        <div class="rounded-lg bg-pink-500 p-6 text-white transition-colors duration-200 dark:bg-gray-800 dark:text-gray-100">
            <!-- Title -->
            <h3 class="mb-6 text-2xl font-bold dark:text-white">
                {{ t('newsletter_title') || 'Află primul despre ofertele noastre exclusive.' }}
            </h3>

            <!-- Success Message -->
            <div
                v-if="successMessage"
                class="mb-4 rounded-md bg-green-500 p-3 text-sm text-white dark:bg-green-600 dark:text-white"
            >
                {{ successMessage }}
            </div>

            <!-- Error Message -->
            <div
                v-if="error"
                class="mb-4 rounded-md bg-red-500 p-3 text-sm text-white dark:bg-red-600 dark:text-white"
            >
                {{ error }}
            </div>

            <!-- Form -->
            <form @submit="handleSubmit" class="space-y-4">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <!-- Name Field -->
                    <div>
                        <input
                            v-model="formData.name"
                            type="text"
                            :placeholder="t('newsletter_name_placeholder') || 'Nume/Prenume'"
                            class="w-full rounded-md bg-white/10 px-4 py-3 text-white placeholder:text-white/70 focus:outline-none focus:ring-2 focus:ring-white/50 dark:bg-gray-700/50 dark:text-gray-100 dark:placeholder:text-gray-300 dark:focus:ring-gray-200/50"
                            required
                        />
                    </div>

                    <!-- Email Field -->
                    <div>
                        <input
                            v-model="formData.email"
                            type="email"
                            placeholder="Email"
                            class="w-full rounded-md bg-white/10 px-4 py-3 text-white placeholder:text-white/70 focus:outline-none focus:ring-2 focus:ring-white/50 dark:bg-gray-700/50 dark:text-gray-100 dark:placeholder:text-gray-300 dark:focus:ring-gray-200/50"
                            required
                        />
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <!-- Phone Field -->
                    <div>
                        <input
                            v-model="formData.phone"
                            type="tel"
                            :placeholder="t('newsletter_phone_placeholder') || 'Tel. ex: 079123456'"
                            class="w-full rounded-md bg-white/10 px-4 py-3 text-white placeholder:text-white/70 focus:outline-none focus:ring-2 focus:ring-white/50 dark:bg-gray-700/50 dark:text-gray-100 dark:placeholder:text-gray-300 dark:focus:ring-gray-200/50"
                        />
                    </div>

                    <!-- Subscribe Button -->
                    <div class="flex items-end">
                        <button
                            type="submit"
                            :disabled="isLoading"
                            class="w-full rounded-md bg-pink-500 px-6 py-3 font-semibold text-white transition-colors hover:bg-pink-600 focus:outline-none focus:ring-2 focus:ring-white/50 disabled:cursor-not-allowed disabled:opacity-50 dark:bg-pink-600 dark:hover:bg-pink-700 dark:focus:ring-gray-200/50"
                        >
                            <span v-if="!isLoading">
                                {{ t('newsletter_subscribe') || 'Abonează-te' }}
                            </span>
                            <span v-else>
                                {{ t('loading') || 'Se procesează...' }}
                            </span>
                        </button>
                    </div>
                </div>

                <!-- Privacy Policy Checkbox -->
                <div class="flex items-start gap-2">
                    <input
                        v-model="formData.privacy_accepted"
                        type="checkbox"
                        id="privacy-accepted"
                        class="mt-1 h-4 w-4 rounded text-pink-600 focus:ring-2 focus:ring-white/50 dark:text-pink-500 dark:focus:ring-gray-200/50"
                        required
                    />
                    <label for="privacy-accepted" class="text-sm text-white dark:text-gray-100">
                        {{ t('newsletter_privacy_text') || 'Sunt de acord cu' }}
                        <Link
                            href="/privacy"
                            class="underline hover:text-white/80 dark:hover:text-gray-200"
                        >
                            {{ t('privacy_policy') || 'politica de confidențialitate' }}
                        </Link>
                    </label>
                </div>
            </form>
        </div>
    </div>
</template>

