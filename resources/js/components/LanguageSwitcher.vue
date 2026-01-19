<script setup lang="ts">
import { computed } from 'vue';
import { Globe } from 'lucide-vue-next';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Button } from '@/components/ui/button';
import { useTranslations } from '@/composables/useTranslations';

const { locale, switchLocale, availableLocales, t } = useTranslations();

// Get current locale value for comparison
const currentLocaleCode = computed(() => locale.value);
</script>

<template>
    <DropdownMenu>
        <DropdownMenuTrigger as-child>
            <Button 
                variant="ghost" 
                size="icon" 
                class="text-gray-900 dark:text-white group relative h-10 w-10 transition-all duration-200 hover:bg-white/10 hover:text-gray-900 dark:hover:text-white"
                :title="t('select_language')"
            >
                <Globe class="text-gray-900 dark:text-white h-5 w-5 transition-transform duration-200 group-hover:rotate-12" />
                <span class="sr-only">{{ t('select_language') }}</span>
            </Button>
        </DropdownMenuTrigger>
        <DropdownMenuContent align="end" class="text-gray-900 dark:text-white w-40 border-gray-200/50 bg-gray-50/95 backdrop-blur-md dark:border-gray-700/50 dark:bg-gray-900/95">
            <DropdownMenuItem
                v-for="loc in availableLocales"
                :key="loc.code"
                @click="switchLocale(loc.code)"
                :class="[
                    'cursor-pointer text-sm transition-all duration-200',
                    currentLocaleCode === loc.code 
                        ? 'bg-primary/10 font-medium text-primary dark:bg-primary/20 dark:text-primary' 
                        : 'hover:bg-accent hover:text-accent-foreground dark:hover:bg-accent/50',
                ]"
            >
                <span class="flex-1">{{ loc.native }}</span>
                <span
                    v-if="currentLocaleCode === loc.code"
                    class="ml-2 text-primary text-xs font-bold dark:text-primary"
                >
                    ✓
                </span>
            </DropdownMenuItem>
        </DropdownMenuContent>
    </DropdownMenu>
</template>

