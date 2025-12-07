import { ref, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

interface WishlistStatus {
    [productId: number]: boolean;
}

const wishlistStatuses = ref<WishlistStatus>({});
const isLoading = ref(false);
let fetchPromise: Promise<WishlistStatus> | null = null;

export const useWishlistBatch = () => {
    const page = usePage();
    const isAuthenticated = computed(() => !!page.props.auth?.user);

    const checkBatch = async (productIds: number[]): Promise<WishlistStatus> => {
        // If not authenticated, return all false
        if (!isAuthenticated.value) {
            const result: WishlistStatus = {};
            productIds.forEach(id => {
                result[id] = false;
            });
            return result;
        }

        // Filter out already cached product IDs
        const uncachedIds = productIds.filter(id => !(id in wishlistStatuses.value));

        // If all are cached, return cached values
        if (uncachedIds.length === 0) {
            const result: WishlistStatus = {};
            productIds.forEach(id => {
                result[id] = wishlistStatuses.value[id] || false;
            });
            return result;
        }

        // If a fetch is already in progress, wait for it
        if (fetchPromise) {
            await fetchPromise;
            const result: WishlistStatus = {};
            productIds.forEach(id => {
                result[id] = wishlistStatuses.value[id] || false;
            });
            return result;
        }

        // Start new batch fetch
        isLoading.value = true;
        fetchPromise = (async () => {
            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                
                if (!csrfToken) {
                    throw new Error('CSRF token not found');
                }

                const response = await fetch('/wishlist/check-batch', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({
                        product_ids: uncachedIds,
                    }),
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();
                
                // Update cache
                if (data.data) {
                    Object.assign(wishlistStatuses.value, data.data);
                }

                return wishlistStatuses.value;
            } catch (error) {
                console.error('Error fetching wishlist batch:', error);
                // Return false for all uncached IDs on error
                const result: WishlistStatus = {};
                uncachedIds.forEach(id => {
                    result[id] = false;
                    wishlistStatuses.value[id] = false;
                });
                return result;
            } finally {
                isLoading.value = false;
                fetchPromise = null;
            }
        })();

        await fetchPromise;

        // Return results for requested product IDs
        const result: WishlistStatus = {};
        productIds.forEach(id => {
            result[id] = wishlistStatuses.value[id] || false;
        });
        return result;
    };

    const updateStatus = (productId: number, inWishlist: boolean) => {
        wishlistStatuses.value[productId] = inWishlist;
    };

    const clearCache = () => {
        wishlistStatuses.value = {};
    };

    // Expose updateStatus globally for ProductCard to use
    if (typeof window !== 'undefined') {
        (window as any).wishlistBatchUpdate = updateStatus;
    }

    return {
        wishlistStatuses,
        isLoading,
        checkBatch,
        updateStatus,
        clearCache,
    };
};

