import { ref, onMounted } from 'vue';

interface SiteSettings {
    site_name: string;
    site_description: string | null;
    site_logo: string | null;
    site_logo_icon: string | null;
    site_favicon: string | null;
    site_email: string | null;
    site_phone: string | null;
    site_address: string | null;
    site_facebook: string | null;
    site_instagram: string | null;
    site_twitter: string | null;
    site_linkedin: string | null;
    site_meta_keywords: string | null;
    site_meta_description: string | null;
    site_google_analytics: string | null;
    show_login_modal: boolean;
    show_site_name: boolean;
    show_logo: boolean;
}

const siteSettings = ref<SiteSettings | null>(null);
const isLoading = ref(true);

export const useSiteSettings = () => {
    const fetchSiteSettings = async () => {
        if (siteSettings.value) {
            return siteSettings.value;
        }

        try {
            const response = await fetch('/api/site-settings');
            const data = await response.json();
            siteSettings.value = data.data;
            return siteSettings.value;
        } catch (error) {
            console.error('Error fetching site settings:', error);
            // Fallback to default values
            siteSettings.value = {
                site_name: 'Laravel',
                site_description: null,
                site_logo: null,
                site_logo_icon: null,
                site_favicon: null,
                site_email: null,
                site_phone: null,
                site_address: null,
                site_facebook: null,
                site_instagram: null,
                site_twitter: null,
                site_linkedin: null,
                site_meta_keywords: null,
                site_meta_description: null,
                site_google_analytics: null,
                show_login_modal: true,
                show_site_name: true,
                show_logo: true,
            };
            return siteSettings.value;
        } finally {
            isLoading.value = false;
        }
    };

    const refreshSettings = async () => {
        siteSettings.value = null;
        isLoading.value = true;
        return await fetchSiteSettings();
    };

    return {
        siteSettings,
        isLoading,
        fetchSiteSettings,
        refreshSettings,
    };
};

