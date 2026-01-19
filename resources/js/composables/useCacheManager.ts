/**
 * Centralized cache management and invalidation
 */

export const useCacheManager = () => {
    /**
     * Clear all API caches
     */
    const clearAllCaches = () => {
        if (typeof window === 'undefined') return;
        
        // Clear localStorage caches
        const keys = Object.keys(localStorage);
        keys.forEach(key => {
            if (key.includes('_api_cache') || key.includes('_version')) {
                localStorage.removeItem(key);
            }
        });
        
        // Clear site-settings cache
        localStorage.removeItem('site_settings_cache');
        localStorage.removeItem('site_settings_version');
        
        // Clear Service Worker cache if available
        if ('caches' in window) {
            caches.keys().then(cacheNames => {
                cacheNames.forEach(cacheName => {
                    if (cacheName.includes('app-plotter')) {
                        caches.delete(cacheName);
                    }
                });
            });
        }
    };

    /**
     * Clear specific cache by key pattern
     */
    const clearCacheByPattern = (pattern: string | RegExp) => {
        if (typeof window === 'undefined') return;
        
        const keys = Object.keys(localStorage);
        const regex = typeof pattern === 'string' ? new RegExp(pattern) : pattern;
        
        keys.forEach(key => {
            if (regex.test(key)) {
                localStorage.removeItem(key);
            }
        });
    };

    /**
     * Clear navigation caches
     */
    const clearNavigationCaches = () => {
        clearCacheByPattern(/^nav_/);
    };

    /**
     * Clear site settings cache
     */
    const clearSiteSettingsCache = () => {
        if (typeof window === 'undefined') return;
        localStorage.removeItem('site_settings_cache');
        localStorage.removeItem('site_settings_version');
    };

    /**
     * Invalidate cache on user login/logout
     */
    const invalidateOnAuthChange = () => {
        // Clear wishlist-related caches
        clearCacheByPattern(/wishlist/);
        // Navigation might change based on user role
        clearNavigationCaches();
    };

    /**
     * Get cache statistics
     */
    const getCacheStats = () => {
        if (typeof window === 'undefined') return null;
        
        const keys = Object.keys(localStorage);
        const cacheKeys = keys.filter(key => 
            key.includes('_api_cache') || 
            key.includes('_version') || 
            key.includes('site_settings')
        );
        
        let totalSize = 0;
        cacheKeys.forEach(key => {
            const item = localStorage.getItem(key);
            if (item) {
                totalSize += new Blob([item]).size;
            }
        });
        
        return {
            cacheCount: cacheKeys.length,
            totalSize: totalSize,
            totalSizeKB: (totalSize / 1024).toFixed(2),
        };
    };

    return {
        clearAllCaches,
        clearCacheByPattern,
        clearNavigationCaches,
        clearSiteSettingsCache,
        invalidateOnAuthChange,
        getCacheStats,
    };
};

