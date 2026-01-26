<script setup lang="ts">
import emblaCarouselVue, { type EmblaCarouselVueType } from 'embla-carousel-vue'
import type { EmblaOptionsType, EmblaPluginType } from 'embla-carousel'
import { computed, provide, ref, watch } from 'vue'
import { cn } from '@/lib/utils'

export type CarouselApi = EmblaCarouselVueType[1]

export interface CarouselProps {
  opts?: EmblaOptionsType
  plugins?: EmblaPluginType[]
  orientation?: 'horizontal' | 'vertical'
  class?: string
}

const props = withDefaults(defineProps<CarouselProps>(), {
  orientation: 'horizontal',
})

const [emblaRef, emblaApi] = emblaCarouselVue(
  {
    ...props.opts,
    axis: props.orientation === 'horizontal' ? 'x' : 'y',
  },
  props.plugins || [],
)

const carouselApi = ref<CarouselApi>(emblaApi.value)

const onSelect = (api: CarouselApi) => {
  if (!api) {
    return
  }
  carouselApi.value = api
}

watch(emblaApi, onSelect, { immediate: true })

const canScrollPrev = computed(() => {
  const api = carouselApi.value
  if (!api) return false
  return api.canScrollPrev()
})

const canScrollNext = computed(() => {
  const api = carouselApi.value
  if (!api) return false
  return api.canScrollNext()
})

const scrollPrev = () => {
  const api = carouselApi.value
  if (!api) return
  api.scrollPrev()
}

const scrollNext = () => {
  const api = carouselApi.value
  if (!api) return
  api.scrollNext()
}

provide('carouselApi', carouselApi)
provide('canScrollPrev', canScrollPrev)
provide('canScrollNext', canScrollNext)
provide('scrollPrev', scrollPrev)
provide('scrollNext', scrollNext)

defineExpose({
  carouselApi,
  scrollPrev,
  scrollNext,
  canScrollPrev,
  canScrollNext,
})

const emit = defineEmits<{
  'init-api': [api: CarouselApi]
}>()

watch(carouselApi, (api) => {
  if (api) {
    emit('init-api', api)
  }
}, { immediate: true })
</script>

<template>
  <div :class="cn('relative', props.class)">
    <div
      ref="emblaRef"
      class="overflow-hidden"
    >
      <slot
        :can-scroll-prev="canScrollPrev"
        :can-scroll-next="canScrollNext"
        :scroll-prev="scrollPrev"
        :scroll-next="scrollNext"
      />
    </div>
    <slot
      name="controls"
      :can-scroll-prev="canScrollPrev"
      :can-scroll-next="canScrollNext"
      :scroll-prev="scrollPrev"
      :scroll-next="scrollNext"
    />
  </div>
</template>

