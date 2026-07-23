<script setup lang="ts">
import { RouterLink, useRouter } from 'vue-router'
import { GraduationCap, LogOut, UserRound } from '@lucide/vue'
import { Button } from '@/components/ui/button'
import { useAuthStore } from '@/stores/auth'
import { tr } from '@/locales/tr'

const router = useRouter()
const auth = useAuthStore()

async function handleLogout() {
  auth.logout()
  await router.push({ name: 'login' })
}
</script>

<template>
  <header class="bg-background/80 border-border sticky top-0 z-10 border-b backdrop-blur">
    <div class="mx-auto flex w-full max-w-5xl items-center justify-between gap-4 px-6 py-3">
      <RouterLink :to="{ name: 'dashboard' }" class="flex items-center gap-2">
        <span
          class="bg-primary text-primary-foreground flex size-8 items-center justify-center rounded-lg"
        >
          <GraduationCap class="size-4" />
        </span>
        <span class="text-foreground text-sm font-semibold tracking-tight">
          {{ tr.app.name }}
        </span>
      </RouterLink>

      <div class="flex items-center gap-2">
        <span v-if="auth.fullName" class="text-muted-foreground hidden text-sm sm:inline">
          {{ auth.fullName }}
        </span>
        <Button as-child variant="ghost" size="sm">
          <RouterLink :to="{ name: 'profile' }">
            <UserRound class="size-4" />
            {{ tr.nav.profile }}
          </RouterLink>
        </Button>
        <Button variant="outline" size="sm" @click="handleLogout">
          <LogOut class="size-4" />
          {{ tr.nav.logout }}
        </Button>
      </div>
    </div>
  </header>
</template>
