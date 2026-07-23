<script setup lang="ts">
import { ref } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
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
import { ALPHANUMERIC_REGEX } from '@/types/api.ts'
import { tr } from '@/locales/tr'

const router = useRouter()
const auth = useAuthStore()

const FIELD_NAMES = [
  'name',
  'surname',
  'email',
  'password',
  'password_confirmation',
  'registrationCode',
] as const

const formError = ref('')
const showPassword = ref(false)
const showPasswordConfirmation = ref(false)
const validationSchema = toTypedSchema(
  z
    .object({
      name: z
        .string()
        .min(1, tr.validation.nameRequired)
        .max(100, tr.validation.nameMax)
        .regex(ALPHANUMERIC_REGEX, tr.validation.nameAlphaNum),
      surname: z
        .string()
        .min(1, tr.validation.surnameRequired)
        .max(100, tr.validation.surnameMax)
        .regex(ALPHANUMERIC_REGEX, tr.validation.surnameAlphaNum),
      email: z
        .string()
        .min(1, tr.validation.emailRequired)
        .max(100, tr.validation.emailMax)
        .email(tr.validation.emailInvalid),
      password: z.string().min(1, tr.validation.passwordRequired).min(6, tr.validation.passwordMin),
      password_confirmation: z.string().min(1, tr.validation.passwordConfirmRequired),
      registrationCode: z.string().min(1, tr.validation.codeRequired).min(6, tr.validation.codeMin),
    })
    .refine((values) => values.password === values.password_confirmation, {
      message: tr.validation.passwordMismatch,
      path: ['password_confirmation'],
    }),
)

const { defineField, errors, handleSubmit, isSubmitting, setErrors } = useForm({
  validationSchema,
  initialValues: {
    name: '',
    surname: '',
    email: '',
    password: '',
    password_confirmation: '',
    registrationCode: '',
  },
})

const [name, nameAttrs] = defineField('name')
const [surname, surnameAttrs] = defineField('surname')
const [email, emailAttrs] = defineField('email')
const [password, passwordAttrs] = defineField('password')
const [passwordConfirmation, passwordConfirmationAttrs] = defineField('password_confirmation')
const [registrationCode, registrationCodeAttrs] = defineField('registrationCode')

function applyApiErrors(error: ApiError) {
  setErrors({
    name: error.errors.name?.[0],
    surname: error.errors.surname?.[0],
    email: error.errors.email?.[0],
    password: error.errors.password?.[0],
    password_confirmation: error.errors.password_confirmation?.[0],
    registrationCode: error.errors.registrationCode?.[0],
  })

  const handled = FIELD_NAMES as readonly string[]
  const fieldKeys = Object.keys(error.errors)
  const unmapped = fieldKeys.filter((key) => !handled.includes(key))

  formError.value = fieldKeys.length === 0 || unmapped.length > 0 ? error.message : ''
}

const onSubmit = handleSubmit(async (values) => {
  formError.value = ''

  try {
    await auth.register({
      name: values.name,
      surname: values.surname,
      email: values.email,
      password: values.password,
      password_confirmation: values.password_confirmation,
      registrationCode: values.registrationCode,
    })

    await router.push({ name: 'login', query: { registered: '1' } })
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
</script>

<template>
  <AuthShell class="max-w-md">
    <div
      v-if="formError"
      role="alert"
      class="border-destructive/40 bg-destructive/10 text-destructive rounded-md border px-3 py-2 text-sm"
    >
      {{ formError }}
    </div>

    <Card>
      <CardHeader>
        <CardTitle class="text-xl"> {{ tr.register.title }} </CardTitle>
        <CardDescription> {{ tr.register.description }} </CardDescription>
      </CardHeader>
      <CardContent>
        <form novalidate @submit="onSubmit">
          <FieldGroup class="gap-4">
            <div class="grid grid-cols-1 gap-x-2 gap-y-4 sm:grid-cols-2">
              <Field :data-invalid="Boolean(errors.name)">
                <FieldLabel for="register-name"> {{ tr.fields.name }} </FieldLabel>
                <Input
                  id="register-name"
                  v-model="name"
                  v-bind="nameAttrs"
                  type="text"
                  autocomplete="given-name"
                  :placeholder="tr.register.namePlaceholder"
                  :aria-invalid="Boolean(errors.name)"
                />
                <FieldError :errors="[errors.name]" />
              </Field>

              <Field :data-invalid="Boolean(errors.surname)">
                <FieldLabel for="register-surname"> {{ tr.fields.surname }} </FieldLabel>
                <Input
                  id="register-surname"
                  v-model="surname"
                  v-bind="surnameAttrs"
                  type="text"
                  autocomplete="family-name"
                  :placeholder="tr.register.surnamePlaceholder"
                  :aria-invalid="Boolean(errors.surname)"
                />
                <FieldError :errors="[errors.surname]" />
              </Field>
            </div>

            <Field :data-invalid="Boolean(errors.email)">
              <FieldLabel for="register-email"> {{ tr.fields.email }} </FieldLabel>
              <Input
                id="register-email"
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
              <FieldLabel for="register-password"> {{ tr.fields.password }} </FieldLabel>
              <div class="relative">
                <Input
                  id="register-password"
                  v-model="password"
                  v-bind="passwordAttrs"
                  :type="showPassword ? 'text' : 'password'"
                  autocomplete="new-password"
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
                  @click="showPassword = !showPassword"
                >
                  <EyeOff v-if="showPassword" class="size-4" />
                  <Eye v-else class="size-4" />
                </Button>
              </div>
              <FieldError :errors="[errors.password]" />
            </Field>

            <Field :data-invalid="Boolean(errors.password_confirmation)">
              <FieldLabel for="register-password-confirmation">
                {{ tr.register.passwordConfirmLabel }}
              </FieldLabel>
              <div class="relative">
                <Input
                  id="register-password-confirmation"
                  v-model="passwordConfirmation"
                  v-bind="passwordConfirmationAttrs"
                  :type="showPasswordConfirmation ? 'text' : 'password'"
                  autocomplete="new-password"
                  class="pr-10"
                  :aria-invalid="Boolean(errors.password_confirmation)"
                />
                <Button
                  type="button"
                  variant="ghost"
                  size="icon-sm"
                  class="text-muted-foreground hover:text-foreground absolute inset-y-0 right-0 my-auto mr-1"
                  :aria-label="
                    showPasswordConfirmation ? tr.fields.passwordHide : tr.fields.passwordShow
                  "
                  :aria-pressed="showPasswordConfirmation"
                  @click="showPasswordConfirmation = !showPasswordConfirmation"
                >
                  <EyeOff v-if="showPasswordConfirmation" class="size-4" />
                  <Eye v-else class="size-4" />
                </Button>
              </div>
              <FieldError :errors="[errors.password_confirmation]" />
            </Field>

            <Field :data-invalid="Boolean(errors.registrationCode)">
              <FieldLabel for="register-code"> {{ tr.register.codeLabel }} </FieldLabel>
              <Input
                id="register-code"
                v-model="registrationCode"
                v-bind="registrationCodeAttrs"
                type="text"
                autocomplete="off"
                spellcheck="false"
                :placeholder="tr.register.codePlaceholder"
                class="font-mono tracking-widest uppercase"
                :aria-invalid="Boolean(errors.registrationCode)"
              />
              <FieldDescription>
                {{ tr.register.codeHint }}
              </FieldDescription>
              <FieldError :errors="[errors.registrationCode]" />
            </Field>

            <Field class="mt-2">
              <Button type="submit" class="w-full" :disabled="isSubmitting">
                <LoaderCircle v-if="isSubmitting" class="size-4 animate-spin" />
                {{ isSubmitting ? tr.register.submitting : tr.register.submit }}
              </Button>
              <FieldDescription class="text-center">
                {{ tr.register.haveAccount }}
                <RouterLink
                  to="/login"
                  class="text-foreground font-medium underline-offset-4 hover:underline"
                >
                  {{ tr.register.loginLink }}
                </RouterLink>
              </FieldDescription>
            </Field>
          </FieldGroup>
        </form>
      </CardContent>
    </Card>
  </AuthShell>
</template>
