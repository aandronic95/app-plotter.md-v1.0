<script setup lang="ts">
import { computed, inject } from 'vue'
import { ArrowRight } from 'lucide-vue-next'
import { Button } from '@/components/ui/button'
import { cn } from '@/lib/utils'

const props = defineProps<{
  class?: string
}>()

const canScrollNext = inject<ReturnType<typeof computed<boolean>>>('canScrollNext', computed(() => false))
const scrollNext = inject<() => void>('scrollNext', () => {})
</script>

<template>
  <Button
    v-if="canScrollNext"
    :class="cn('absolute h-8 w-8 rounded-full right-4 top-1/2 -translate-y-1/2', props.class)"
    variant="outline"
    size="icon"
    @click="scrollNext"
  >
    <ArrowRight class="h-4 w-4" />
    <span class="sr-only">Next slide</span>
  </Button>
</template>

