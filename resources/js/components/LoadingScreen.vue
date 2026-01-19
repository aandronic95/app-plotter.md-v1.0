<script setup lang="ts">
import { onMounted, ref } from 'vue';

const isVisible = ref(true);

onMounted(() => {
    // Hide after 1 second
    setTimeout(() => {
        isVisible.value = false;
    }, 1000);
});
</script>

<template>
    <Transition
        name="fade"
        appear
    >
        <div
            v-if="isVisible"
            class="fixed inset-0 z-[9999] flex items-center justify-center bg-gray-50 dark:bg-gray-900"
        >
            <div class="flex flex-col items-center justify-center">
                <img
                    src="/logo.jpg"
                    alt="Loading..."
                    class="max-w-xs w-full h-auto animate-pulse"
                />
            </div>
        </div>
    </Transition>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>

