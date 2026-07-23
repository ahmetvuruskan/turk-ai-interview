<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import { GraduationCap } from '@lucide/vue'
import { cn } from '@/lib/utils'
import { tr } from '@/locales/tr'

const props = defineProps<{
  class?: HTMLAttributes['class']
}>()
</script>

<template>
  <div class="bg-muted/40 flex min-h-svh w-full flex-col items-center justify-center p-6 md:p-10">
    <div :class="cn('flex w-full max-w-sm flex-col gap-6', props.class)">
      <div class="flex items-center justify-center gap-2 self-center">
        <span
          class="bg-primary text-primary-foreground flex size-9 items-center justify-center rounded-lg shadow-sm"
        >
          <GraduationCap class="size-5" />
        </span>
        <span class="text-foreground text-base font-semibold tracking-tight">
          {{ tr.app.name }}
        </span>
      </div>
      <slot />
    </div>
  </div>
</template>
