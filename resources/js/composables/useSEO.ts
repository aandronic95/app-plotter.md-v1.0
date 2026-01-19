import { computed } from 'vue';
import { useSiteSettings } from './useSiteSettings';

interface SEOData {
    title?: string;
    description?: string;
    image?: string;
    url?: string;
    type?: string;
    keywords?: string;
}

export const useSEO = (seoData?: SEOData) => {
    const { siteSettings } = useSiteSettings();

    const siteName = computed(() => {
        return siteSettings.value?.site_name || 'PLOTTER.MD';
    });

    const siteUrl = computed(() => {
        return typeof window !== 'undefined' ? window.location.origin : '';
    });

    const title = computed(() => {
        if (seoData?.title) {
            return `${seoData.title} - ${siteName.value}`;
        }
        return siteName.value;
    });

    const description = computed(() => {
        return seoData?.description || 
               siteSettings.value?.site_meta_description || 
               siteSettings.value?.site_description || 
               '';
    });

    const image = computed(() => {
        if (seoData?.image) {
            return seoData.image.startsWith('http') 
                ? seoData.image 
                : `${siteUrl.value}${seoData.image}`;
        }
        return siteSettings.value?.site_logo 
            ? (siteSettings.value.site_logo.startsWith('http') 
                ? siteSettings.value.site_logo 
                : `${siteUrl.value}/storage/${siteSettings.value.site_logo}`)
            : `${siteUrl.value}/images/logo.png`;
    });

    const url = computed(() => {
        if (seoData?.url) {
            return seoData.url.startsWith('http') 
                ? seoData.url 
                : `${siteUrl.value}${seoData.url}`;
        }
        return typeof window !== 'undefined' ? window.location.href : siteUrl.value;
    });

    const type = computed(() => {
        return seoData?.type || 'website';
    });

    const keywords = computed(() => {
        return seoData?.keywords || siteSettings.value?.site_meta_keywords || '';
    });

    const structuredData = computed(() => {
        const baseData: any = {
            '@context': 'https://schema.org',
            '@type': 'Organization',
            name: siteName.value,
            url: siteUrl.value,
        };

        if (siteSettings.value?.site_logo) {
            baseData.logo = image.value;
        }

        if (siteSettings.value?.site_email) {
            baseData.email = siteSettings.value.site_email;
        }

        if (siteSettings.value?.site_phone) {
            baseData.telephone = siteSettings.value.site_phone;
        }

        if (siteSettings.value?.site_address) {
            baseData.address = {
                '@type': 'PostalAddress',
                addressLocality: siteSettings.value.site_address,
            };
        }

        if (siteSettings.value?.site_facebook) {
            baseData.sameAs = [siteSettings.value.site_facebook];
            if (siteSettings.value.site_instagram) {
                baseData.sameAs.push(siteSettings.value.site_instagram);
            }
            if (siteSettings.value.site_twitter) {
                baseData.sameAs.push(siteSettings.value.site_twitter);
            }
        }

        return baseData;
    });

    return {
        title,
        description,
        image,
        url,
        type,
        keywords,
        siteName,
        siteUrl,
        structuredData,
    };
};

