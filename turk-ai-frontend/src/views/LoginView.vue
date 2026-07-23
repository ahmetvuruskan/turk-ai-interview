<script setup lang="ts">
import { computed, ref } from 'vue'
import { RouterLink, useRoute, useRouter } from 'vue-router'
import { toTypedSchema } from '@vee-validate/zod'
import { useForm } from 'vee-validate'
import * as z from 'zod'
import { Eye, EyeOff, LoaderCircle } from '@lucide/vue'
import AuthShell from '@/components/auth/AuthShell.vue'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Field, FieldDescription, FieldError, FieldGroup, FieldLabel } from '@/components/ui/field'
import { Input } from '@/components/ui/input'
import { ApiError } from '@/lib/http'
import { useAuthStore } from '@/stores/auth'
import { tr } from '@/locales/tr'

const route = useRoute()
const router = useRouter()
const auth = useAuthStore()

const FIELD_NAMES = ['email', 'password'] as const

const formError = ref('')
const showPassword = ref(false)

const justRegistered = computed(() => route.query.registered === '1')

const validationSchema = toTypedSchema(
  z.object({
    email: z
      .string()
      .min(1, tr.validation.emailRequired)
      .max(100, tr.validation.emailMax)
      .email(tr.validation.emailInvalid),
    password: z.string().min(1, tr.validation.passwordRequired).min(6, tr.validation.passwordMin),
  }),
)

const { defineField, errors, handleSubmit, isSubmitting, setErrors } = useForm({
  validationSchema,
  initialValues: {
    email: '',
    password: '',
  },
})

const [email, emailAttrs] = defineField('email')
const [password, passwordAttrs] = defineField('password')

function applyApiErrors(error: ApiError) {
  setErrors({
    email: error.errors.email?.[0],
    password: error.errors.password?.[0],
  })

  const handled = FIELD_NAMES as readonly string[]
  const fieldKeys = Object.keys(error.errors)
  const unmapped = fieldKeys.filter((key) => !handled.includes(key))

  formError.value = fieldKeys.length === 0 || unmapped.length > 0 ? error.message : ''
}

const onSubmit = handleSubmit(async (values) => {
  formError.value = ''

  try {
    await auth.login({ email: values.email, password: values.password })

    const redirect = route.query.redirect
    if (typeof redirect === 'string' && redirect.startsWith('/') && !redirect.startsWith('//')) {
      await router.push(redirect)
      return
    }

    await router.push({ name: 'dashboard' })
  } catch (error) {
    if (error instanceof ApiError) {
      if (error.isValidation) {
        applyApiErrors(error)
        return
      }
      formError.value = error.message
      return
    }

    formError.value = tr.errors.unexpectedRetry
  }
})

function togglePassword() {
  showPassword.value = !showPassword.value
}
</script>

<template>
  <AuthShell>
    <div
      v-if="justRegistered"
      role="status"
      class="rounded-md border border-emerald-500/40 bg-emerald-500/10 px-3 py-2 text-sm text-emerald-700 dark:text-emerald-400"
    >
      {{ tr.login.registeredSuccess }}
    </div>

    <div
      v-if="formError"
      role="alert"
      class="border-destructive/40 bg-destructive/10 text-destructive rounded-md border px-3 py-2 text-sm"
    >
      {{ formError }}
    </div>

    <Card>
      <CardHeader>
        <CardTitle class="text-xl">
          {{ tr.login.title }}
        </CardTitle>
        <CardDescription>
          {{ tr.login.description }}
        </CardDescription>
      </CardHeader>
      <CardContent>
        <form novalidate @submit="onSubmit">
          <FieldGroup class="gap-4">
            <Field :data-invalid="Boolean(errors.email)">
              <FieldLabel for="login-email">
                {{ tr.fields.email }}
              </FieldLabel>
              <Input
                id="login-email"
                v-model="email"
                v-bind="emailAttrs"
                type="email"
                autocomplete="email"
                :placeholder="tr.fields.emailPlaceholder"
                :aria-invalid="Boolean(errors.email)"
              />
              <FieldError :errors="[errors.email]" />
            </Field>

            <Field :data-invalid="Boolean(errors.password)">
              <FieldLabel for="login-password">
                {{ tr.fields.password }}
              </FieldLabel>
              <div class="relative">
                <Input
                  id="login-password"
                  v-model="password"
                  v-bind="passwordAttrs"
                  :type="showPassword ? 'text' : 'password'"
                  autocomplete="current-password"
                  class="pr-10"
                  :aria-invalid="Boolean(errors.password)"
                />
                <Button
                  type="button"
                  variant="ghost"
                  size="icon-sm"
                  class="text-muted-foreground hover:text-foreground absolute inset-y-0 right-0 my-auto mr-1"
                  :aria-label="showPassword ? tr.fields.passwordHide : tr.fields.passwordShow"
                  :aria-pressed="showPassword"
                  @click="togglePassword"
                >
                  <EyeOff v-if="showPassword" class="size-4" />
                  <Eye v-else class="size-4" />
                </Button>
              </div>
              <FieldError :errors="[errors.password]" />
            </Field>

            <Field class="mt-2">
              <Button type="submit" class="w-full" :disabled="isSubmitting">
                <LoaderCircle v-if="isSubmitting" class="size-4 animate-spin" />
                {{ isSubmitting ? tr.login.submitting : tr.login.submit }}
              </Button>
              <FieldDescription class="text-center">
                {{ tr.login.noAccount }}
                <RouterLink
                  to="/register"
                  class="text-foreground font-medium underline-offset-4 hover:underline"
                >
                  {{ tr.login.registerLink }}
                </RouterLink>
              </FieldDescription>
            </Field>
          </FieldGroup>
        </form>
      </CardContent>
    </Card>
  </AuthShell>
</template>
