<script setup lang="ts">
import { cn } from '@/lib/utils'

const props = defineProps<{
  class?: string
}>()
</script>

<template>
  <div
    :class="cn('min-w-0 shrink-0 grow-0 basis-full', props.class)"
    role="group"
    aria-roledescription="slide"
    aria-label="Slide"
  >
    <slot />
  </div>
</template>

