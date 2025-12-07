/**
 * Generic API caching utility with LocalStorage persistence
 */

interface CacheEntry<T> {
    data: T;
    timestamp: number;
    version?: string;
}

interface CacheOptions {
    ttl?: number; // Time to live in milliseconds (default: 1 hour)
    version?: string; // Cache version for invalidation
    key: string; // Unique cache key
}

const DEFAULT_TTL = 60 * 60 * 1000; // 1 hour

export const useApiCache = <T>() => {
    /**
     * Load data from cache
     */
    const loadFromCache = <T>(options: CacheOptions): T | null => {
        if (typeof window === 'undefined') return null;
        
        try {
            const cached = localStorage.getItem(options.key);
            const cachedVersion = localStorage.getItem(`${options.key}_version`);
            
            if (!cached) return null;
            
            const entry: CacheEntry<T> = JSON.parse(cached);
            const now = Date.now();
            const ttl = options.ttl || DEFAULT_TTL;
            
            // Check if cache is expired
            if (now - entry.timestamp > ttl) {
                localStorage.removeItem(options.key);
                localStorage.removeItem(`${options.key}_version`);
                return null;
            }
            
            // Check version (in case data structure changed)
            if (options.version && cachedVersion && cachedVersion !== options.version) {
                localStorage.removeItem(options.key);
                localStorage.removeItem(`${options.key}_version`);
                return null;
            }
            
            return entry.data;
        } catch (error) {
            console.error(`Error loading cache for ${options.key}:`, error);
            localStorage.removeItem(options.key);
            localStorage.removeItem(`${options.key}_version`);
            return null;
        }
    };

    /**
     * Save data to cache
     */
    const saveToCache = <T>(data: T, options: CacheOptions): void => {
        if (typeof window === 'undefined') return;
        
        try {
            const entry: CacheEntry<T> = {
                data,
                timestamp: Date.now(),
                version: options.version,
            };
            localStorage.setItem(options.key, JSON.stringify(entry));
            if (options.version) {
                localStorage.setItem(`${options.key}_version`, options.version);
            }
        } catch (error) {
            console.error(`Error saving cache for ${options.key}:`, error);
            // If storage is full, try to clear old cache
            try {
                localStorage.removeItem(options.key);
                if (options.version) {
                    localStorage.removeItem(`${options.key}_version`);
                }
            } catch (e) {
                // Ignore
            }
        }
    };

    /**
     * Clear specific cache
     */
    const clearCache = (key: string): void => {
        if (typeof window === 'undefined') return;
        localStorage.removeItem(key);
        localStorage.removeItem(`${key}_version`);
    };

    /**
     * Clear all API caches (keeps other localStorage items)
     */
    const clearAllCaches = (): void => {
        if (typeof window === 'undefined') return;
        const keys = Object.keys(localStorage);
        keys.forEach(key => {
            if (key.includes('_api_cache') || key.includes('_version')) {
                localStorage.removeItem(key);
            }
        });
    };

    /**
     * Fetch with cache support
     */
    const fetchWithCache = async <T>(
        url: string,
        options: CacheOptions & {
            fetchOptions?: RequestInit;
            transform?: (data: any) => T;
        }
    ): Promise<T> => {
        // Try cache first
        const cached = loadFromCache<T>(options);
        if (cached) {
            // Fetch in background to update cache
            fetchWithCacheInBackground(url, options);
            return cached;
        }

        // Fetch from API
        try {
            const response = await fetch(url, options.fetchOptions || {});
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            let data = await response.json();
            
            // Apply transform if provided
            if (options.transform) {
                data = options.transform(data);
            }
            
            // Save to cache
            saveToCache(data, options);
            
            return data;
        } catch (error) {
            console.error(`Error fetching ${url}:`, error);
            throw error;
        }
    };

    /**
     * Background fetch to update cache without blocking
     */
    const fetchWithCacheInBackground = async <T>(
        url: string,
        options: CacheOptions & {
            fetchOptions?: RequestInit;
            transform?: (data: any) => T;
        }
    ): Promise<void> => {
        try {
            const response = await fetch(url, options.fetchOptions || {});
            if (!response.ok) return;
            
            let data = await response.json();
            
            // Apply transform if provided
            if (options.transform) {
                data = options.transform(data);
            }
            
            // Save to cache
            saveToCache(data, options);
        } catch (error) {
            // Silently fail in background
            console.debug(`Background fetch failed for ${url}:`, error);
        }
    };

    return {
        loadFromCache,
        saveToCache,
        clearCache,
        clearAllCaches,
        fetchWithCache,
        fetchWithCacheInBackground,
    };
};

