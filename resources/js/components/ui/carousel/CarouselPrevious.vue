<script setup lang="ts">
import { computed, inject } from 'vue'
import { ArrowLeft } from 'lucide-vue-next'
import { Button } from '@/components/ui/button'
import { cn } from '@/lib/utils'

const props = defineProps<{
  class?: string
}>()

const canScrollPrev = inject<ReturnType<typeof computed<boolean>>>('canScrollPrev', computed(() => false))
const scrollPrev = inject<() => void>('scrollPrev', () => {})
</script>

<template>
  <Button
    v-if="canScrollPrev"
    :class="cn('absolute h-8 w-8 rounded-full left-4 top-1/2 -translate-y-1/2', props.class)"
    variant="outline"
    size="icon"
    @click="scrollPrev"
  >
    <ArrowLeft class="h-4 w-4" />
    <span class="sr-only">Previous slide</span>
  </Button>
</template>

