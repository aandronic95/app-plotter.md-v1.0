<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { Head } from '@inertiajs/vue3';
import { onMounted } from 'vue';

onMounted(() => {
    router.visit('/admin');
});
</script>

<template>
    <Head title="Redirecting..." />
    <div class="flex h-full items-center justify-center">
        <div class="text-center">
            <p class="text-lg text-gray-600 dark:text-gray-400">
                Redirecționare către admin...
            </p>
        </div>
    </div>
</template>
