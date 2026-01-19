<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import { cn } from '@/lib/utils'

const props = defineProps<{
  class?: HTMLAttributes['class']
}>()
</script>

<template>
  <div
    data-slot="card"
    :class="
      cn(
        'bg-white dark:bg-gray-800 text-gray-900 dark:text-white flex flex-col gap-6 rounded-xl py-6 border border-gray-200',
        props.class,
      )
    "
  >
    <slot />
  </div>
</template>
