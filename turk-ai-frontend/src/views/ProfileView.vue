<script setup lang="ts">
import { onMounted, ref, watch } from 'vue'
import { RouterLink } from 'vue-router'
import { toTypedSchema } from '@vee-validate/zod'
import { useForm } from 'vee-validate'
import * as z from 'zod'
import { ArrowLeft, Eye, EyeOff, LoaderCircle } from '@lucide/vue'
import AppHeader from '@/components/layout/AppHeader.vue'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Field, FieldDescription, FieldError, FieldGroup, FieldLabel } from '@/components/ui/field'
import { Input } from '@/components/ui/input'
import { ApiError } from '@/lib/http'
import { useAuthStore } from '@/stores/auth'
import { ALPHANUMERIC_REGEX, type UpdateProfilePayload } from '@/types/api'
import { tr } from '@/locales/tr'

const auth = useAuthStore()

const FIELD_NAMES = ['name', 'surname', 'email', 'password', 'password_confirmation'] as const

const formError = ref('')
const successMessage = ref('')
const showPassword = ref(false)
const showPasswordConfirmation = ref(false)

const validationSchema = toTypedSchema(
  z
    .object({
      name: z
        .string()
        .trim()
        .min(1, tr.validation.nameRequired)
        .max(100, tr.validation.nameMax)
        .regex(ALPHANUMERIC_REGEX, tr.validation.nameAlphaNum),
      surname: z
        .string()
        .trim()
        .min(1, tr.validation.surnameRequired)
        .max(100, tr.validation.surnameMax)
        .regex(ALPHANUMERIC_REGEX, tr.validation.surnameAlphaNum),
      email: z
        .string()
        .trim()
        .min(1, tr.validation.emailRequired)
        .max(100, tr.validation.emailMax)
        .email(tr.validation.emailInvalid),
      password: z
        .string()
        .trim()
        .refine((value) => value === '' || value.length >= 6, tr.validation.passwordMin),
      password_confirmation: z.string().trim(),
    })
    .refine(
      (values) =>
        !values.password ||
        values.password_confirmation === '' ||
        values.password === values.password_confirmation,
      {
        message: tr.validation.passwordMismatch,
        path: ['password_confirmation'],
      },
    ),
)

const { defineField, errors, handleSubmit, isSubmitting, resetForm, setErrors } = useForm({
  validationSchema,
  initialValues: {
    name: '',
    surname: '',
    email: '',
    password: '',
    password_confirmation: '',
  },
})

const [name, nameAttrs] = defineField('name')
const [surname, surnameAttrs] = defineField('surname')
const [email, emailAttrs] = defineField('email')
const [password, passwordAttrs] = defineField('password', { validateOnModelUpdate: false })
const [passwordConfirmation, passwordConfirmationAttrs] = defineField('password_confirmation', {
  validateOnModelUpdate: false,
})

function fillFromUser() {
  if (!auth.user) return

  resetForm({
    values: {
      name: auth.user.name,
      surname: auth.user.surname,
      email: auth.user.email,
      password: '',
      password_confirmation: '',
    },
  })
}

function applyApiErrors(error: ApiError) {
  setErrors({
    name: error.errors.name?.[0],
    surname: error.errors.surname?.[0],
    email: error.errors.email?.[0],
    password: error.errors.password?.[0],
    password_confirmation: error.errors.password_confirmation?.[0],
  })

  const handled = FIELD_NAMES as readonly string[]
  const fieldKeys = Object.keys(error.errors)
  const unmapped = fieldKeys.filter((key) => !handled.includes(key))

  formError.value = fieldKeys.length === 0 || unmapped.length > 0 ? error.message : ''
}

const onSubmit = handleSubmit(async (values) => {
  formError.value = ''
  successMessage.value = ''

  if (values.password && !values.password_confirmation) {
    setErrors({ password_confirmation: tr.validation.passwordConfirmRequired })
    return
  }

  const payload: UpdateProfilePayload = {}

  if (values.name !== auth.user?.name) payload.name = values.name
  if (values.surname !== auth.user?.surname) payload.surname = values.surname
  if (values.email !== auth.user?.email) payload.email = values.email

  if (values.password) {
    payload.password = values.password
    payload.password_confirmation = values.password_confirmation
  }

  if (Object.keys(payload).length === 0) {
    formError.value = tr.profile.noChanges
    return
  }

  try {
    await auth.updateProfile(payload)
    fillFromUser()
    successMessage.value = tr.profile.saved
  } catch (error) {
    if (error instanceof ApiError) {
      if (error.status === 401) return
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

watch(() => auth.user, fillFromUser)

onMounted(async () => {
  if (!auth.user) {
    try {
      await auth.fetchMe()
    } catch (error) {
      if (error instanceof ApiError && error.status === 401) return
      formError.value = error instanceof ApiError ? error.message : tr.errors.profileLoad
      return
    }
  }

  fillFromUser()
})
</script>

<template>
  <div class="bg-muted/40 min-h-svh w-full">
    <AppHeader />

    <main class="mx-auto flex w-full max-w-2xl flex-col gap-6 px-6 py-8">
      <div>
        <Button as-child variant="ghost" size="sm" class="text-muted-foreground -ml-3">
          <RouterLink :to="{ name: 'dashboard' }">
            <ArrowLeft class="size-4" />
            {{ tr.nav.backToDashboard }}
          </RouterLink>
        </Button>
      </div>

      <div
        v-if="successMessage"
        role="status"
        class="rounded-md border border-emerald-500/40 bg-emerald-500/10 px-3 py-2 text-sm text-emerald-700 dark:text-emerald-400"
      >
        {{ successMessage }}
      </div>

      <div
        v-if="formError"
        role="alert"
        class="border-destructive/40 bg-destructive/10 text-destructive rounded-md border px-3 py-2 text-sm"
      >
        {{ formError }}
      </div>

      <form novalidate class="flex flex-col gap-6" @submit="onSubmit">
        <Card>
          <CardHeader>
            <CardTitle class="text-xl"> {{ tr.profile.infoTitle }} </CardTitle>
            <CardDescription> {{ tr.profile.infoDescription }} </CardDescription>
          </CardHeader>
          <CardContent>
            <FieldGroup class="gap-4">
              <div class="grid grid-cols-1 gap-x-2 gap-y-4 sm:grid-cols-2">
                <Field :data-invalid="Boolean(errors.name)">
                  <FieldLabel for="profile-name"> {{ tr.fields.name }} </FieldLabel>
                  <Input
                    id="profile-name"
                    v-model="name"
                    v-bind="nameAttrs"
                    type="text"
                    autocomplete="given-name"
                    :aria-invalid="Boolean(errors.name)"
                  />
                  <FieldError :errors="[errors.name]" />
                </Field>

                <Field :data-invalid="Boolean(errors.surname)">
                  <FieldLabel for="profile-surname"> {{ tr.fields.surname }} </FieldLabel>
                  <Input
                    id="profile-surname"
                    v-model="surname"
                    v-bind="surnameAttrs"
                    type="text"
                    autocomplete="family-name"
                    :aria-invalid="Boolean(errors.surname)"
                  />
                  <FieldError :errors="[errors.surname]" />
                </Field>
              </div>

              <Field :data-invalid="Boolean(errors.email)">
                <FieldLabel for="profile-email"> {{ tr.fields.email }} </FieldLabel>
                <Input
                  id="profile-email"
                  v-model="email"
                  v-bind="emailAttrs"
                  type="email"
                  autocomplete="email"
                  :aria-invalid="Boolean(errors.email)"
                />
                <FieldError :errors="[errors.email]" />
              </Field>
            </FieldGroup>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle class="text-xl"> {{ tr.profile.passwordTitle }} </CardTitle>
            <CardDescription>
              {{ tr.profile.passwordDescription }}
            </CardDescription>
          </CardHeader>
          <CardContent>
            <FieldGroup class="gap-4">
              <Field :data-invalid="Boolean(errors.password)">
                <FieldLabel for="profile-password"> {{ tr.profile.newPassword }} </FieldLabel>
                <div class="relative">
                  <Input
                    id="profile-password"
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
                <FieldDescription> {{ tr.profile.passwordHint }} </FieldDescription>
                <FieldError :errors="[errors.password]" />
              </Field>

              <Field :data-invalid="Boolean(errors.password_confirmation)">
                <FieldLabel for="profile-password-confirmation">
                  {{ tr.profile.newPasswordConfirm }}
                </FieldLabel>
                <div class="relative">
                  <Input
                    id="profile-password-confirmation"
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
            </FieldGroup>
          </CardContent>
        </Card>

        <div class="flex items-center justify-end gap-3">
          <Button type="button" variant="outline" :disabled="isSubmitting" @click="fillFromUser">
            {{ tr.profile.reset }}
          </Button>
          <Button type="submit" :disabled="isSubmitting">
            <LoaderCircle v-if="isSubmitting" class="size-4 animate-spin" />
            {{ isSubmitting ? tr.profile.saving : tr.profile.save }}
          </Button>
        </div>
      </form>
    </main>
  </div>
</template>
