import { ref } from 'vue';

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
    show_loyalty_points: boolean;
    show_newsletter_form: boolean;
    loading_text_main: string | null;
    loading_text_sub: string | null;
    header_contact_1_phone: string | null;
    header_contact_1_email: string | null;
    header_contact_2_phone: string | null;
    header_contact_2_email: string | null;
    header_contact_3_phone: string | null;
    header_contact_3_email: string | null;
    service_feature_1_icon: string;
    service_feature_1_title: string;
    service_feature_1_description: string;
    service_feature_2_icon: string;
    service_feature_2_title: string;
    service_feature_2_description: string;
    service_feature_3_icon: string;
    service_feature_3_title: string;
    service_feature_3_description: string;
    service_feature_4_icon: string;
    service_feature_4_title: string;
    service_feature_4_description: string;
}

// Singleton pattern - shared state across all components
const siteSettings = ref<SiteSettings | null>(null);
const isLoading = ref(false);
let fetchPromise: Promise<SiteSettings> | null = null;

const CACHE_KEY = 'site_settings_cache';
const CACHE_VERSION_KEY = 'site_settings_version';
const CACHE_TTL = 24 * 60 * 60 * 1000; // 24 hours in milliseconds

interface CacheEntry {
    data: SiteSettings;
    timestamp: number;
    version: string;
}

// Load from localStorage
const loadFromCache = (): SiteSettings | null => {
    if (typeof window === 'undefined') return null;
    
    try {
        const cached = localStorage.getItem(CACHE_KEY);
        const cachedVersion = localStorage.getItem(CACHE_VERSION_KEY);
        
        if (!cached) return null;
        
        const entry: CacheEntry = JSON.parse(cached);
        const now = Date.now();
        
        // Check if cache is expired
        if (now - entry.timestamp > CACHE_TTL) {
            localStorage.removeItem(CACHE_KEY);
            localStorage.removeItem(CACHE_VERSION_KEY);
            return null;
        }
        
        // Check version (in case settings structure changed)
        const currentVersion = entry.version || '1.0';
        if (cachedVersion && cachedVersion !== currentVersion) {
            localStorage.removeItem(CACHE_KEY);
            localStorage.removeItem(CACHE_VERSION_KEY);
            return null;
        }
        
        return entry.data;
    } catch (error) {
        console.error('Error loading site settings from cache:', error);
        localStorage.removeItem(CACHE_KEY);
        localStorage.removeItem(CACHE_VERSION_KEY);
        return null;
    }
};

// Save to localStorage
const saveToCache = (data: SiteSettings, version: string = '1.0') => {
    if (typeof window === 'undefined') return;
    
    try {
        const entry: CacheEntry = {
            data,
            timestamp: Date.now(),
            version,
        };
        localStorage.setItem(CACHE_KEY, JSON.stringify(entry));
        localStorage.setItem(CACHE_VERSION_KEY, version);
    } catch (error) {
        console.error('Error saving site settings to cache:', error);
        // If storage is full, try to clear old cache
        try {
            localStorage.removeItem(CACHE_KEY);
            localStorage.removeItem(CACHE_VERSION_KEY);
        } catch {
            // Ignore
        }
    }
};

export const useSiteSettings = () => {
    const fetchSiteSettings = async (): Promise<SiteSettings> => {
        // Return cached value if available in memory
        if (siteSettings.value) {
            return siteSettings.value;
        }

        // Try to load from localStorage cache
        const cachedData = loadFromCache();
        if (cachedData) {
            siteSettings.value = cachedData;
            // Fetch in background to update cache
            fetchSiteSettingsInBackground();
            return siteSettings.value;
        }

        // If a fetch is already in progress, return that promise
        if (fetchPromise) {
            return fetchPromise;
        }

        // Start new fetch
        isLoading.value = true;
        fetchPromise = (async () => {
            try {
                const response = await fetch('/api/site-settings', {
                    cache: 'default',
                });
                const data = await response.json();
                siteSettings.value = data.data;
                
                // Save to cache
                saveToCache(data.data);
                
                return siteSettings.value;
            } catch (error) {
                console.error('Error fetching site settings:', error);
                // Try to use cached data as fallback
                const cachedFallback = loadFromCache();
                if (cachedFallback) {
                    siteSettings.value = cachedFallback;
                    return siteSettings.value;
                }
                
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
                    show_loyalty_points: true,
                    show_newsletter_form: true,
                    loading_text_main: 'tanavius',
                    loading_text_sub: 'www.plotter.md',
                    header_contact_1_phone: null,
                    header_contact_1_email: null,
                    header_contact_2_phone: null,
                    header_contact_2_email: null,
                    header_contact_3_phone: null,
                    header_contact_3_email: null,
                    service_feature_1_icon: 'Zap',
                    service_feature_1_title: 'RAPIDITATE',
                    service_feature_1_description: 'Efectuăm rapid comenzile dvs., asigurându-ne că fiecare detaliu este gestionat cu precizie și promptitudine.',
                    service_feature_2_icon: 'Settings',
                    service_feature_2_title: 'SUPORT ÎN ALEGERE',
                    service_feature_2_description: 'Echipa noastră dedicată vă garantează că veți fi ajutați să faceți o alegere corectă pentru rezultatul dorit.',
                    service_feature_3_icon: 'Truck',
                    service_feature_3_title: 'TRANSPORT',
                    service_feature_3_description: 'Cu mândrie vă asigurăm că fiecare comandă beneficiază de un angajament ferm: garantăm livrare fără deteriorare',
                    service_feature_4_icon: 'Smile',
                    service_feature_4_title: 'CALITATE GARANTATĂ',
                    service_feature_4_description: 'Suntem mândri să vă asigurăm că fiecare produs sau serviciu pe care îl oferim vine însoţit de o promisiune fermă: calitate garantată.',
                };
                return siteSettings.value;
            } finally {
                isLoading.value = false;
                fetchPromise = null;
            }
        })();

        return fetchPromise;
    };

    // Background fetch to update cache without blocking
    const fetchSiteSettingsInBackground = async () => {
        try {
            const response = await fetch('/api/site-settings', {
                cache: 'default',
            });
            const data = await response.json();
            if (data.data) {
                siteSettings.value = data.data;
                saveToCache(data.data);
            }
        } catch (error) {
            // Silently fail in background
            console.debug('Background fetch failed:', error);
        }
    };

    const refreshSettings = async (): Promise<SiteSettings> => {
        siteSettings.value = null;
        fetchPromise = null;
        
        // Clear cache
        if (typeof window !== 'undefined') {
            localStorage.removeItem(CACHE_KEY);
            localStorage.removeItem(CACHE_VERSION_KEY);
        }
        
        return await fetchSiteSettings();
    };

    return {
        siteSettings,
        isLoading,
        fetchSiteSettings,
        refreshSettings,
    };
};

