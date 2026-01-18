<script setup lang="ts">
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

const currentLocale = availableLocales.find((l) => l.code === locale.value) || availableLocales[0];
</script>

<template>
    <DropdownMenu>
        <DropdownMenuTrigger as-child>
            <Button variant="ghost" size="icon" class="h-9 w-9 text-gray-600 transition-colors duration-200 hover:text-primary dark:text-gray-300">
                <Globe class="h-4 w-4 text-current" />
                <span class="sr-only">{{ t('select_language') }}</span>
            </Button>
        </DropdownMenuTrigger>
        <DropdownMenuContent align="end" class="w-36">
            <DropdownMenuItem
                v-for="loc in availableLocales"
                :key="loc.code"
                @click="switchLocale(loc.code)"
                :class="[
                    'cursor-pointer text-sm',
                    locale.value === loc.code && 'bg-primary/10 dark:bg-primary/20 font-medium text-primary dark:text-primary',
                ]"
            >
                <span class="flex-1">{{ loc.native }}</span>
                <span
                    v-if="locale.value === loc.code"
                    class="ml-2 text-primary dark:text-primary text-xs font-bold"
                >
                    ✓
                </span>
            </DropdownMenuItem>
        </DropdownMenuContent>
    </DropdownMenu>
</template>

