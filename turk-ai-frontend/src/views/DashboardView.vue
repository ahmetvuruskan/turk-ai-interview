<script setup lang="ts">
import { computed, h, onMounted, onUnmounted, ref, watch } from 'vue'
import { refDebounced } from '@vueuse/core'
import {
  Check,
  ChevronDown,
  ChevronLeft,
  ChevronRight,
  ChevronUp,
  ChevronsLeft,
  ChevronsRight,
  ChevronsUpDown,
  Copy,
  Search,
} from '@lucide/vue'
import AppHeader from '@/components/layout/AppHeader.vue'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Skeleton } from '@/components/ui/skeleton'
import { useAuthStore } from '@/stores/auth'
import { StudentService } from '@/api/students'
import { ApiError, isCanceled } from '@/lib/http'
import { tr } from '@/locales/tr'
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table'
import type { PaginationMeta, StudentWithParent } from '@/types/api'
import {
  FlexRender,
  getCoreRowModel,
  useVueTable,
  type ColumnDef,
  type PaginationState,
  type SortingState,
} from '@tanstack/vue-table'

const auth = useAuthStore()

const roleLabel = computed(() => {
  const role = auth.user?.role
  if (!role) return tr.roles.unknown
  return tr.roles[role] ?? role
})

const students = computed(() => auth.user?.students ?? [])

const loadError = ref('')
const studentList = ref<StudentWithParent[]>([])
const studentsLoading = ref(false)
const studentsError = ref('')
const sorting = ref<SortingState>([])
const copiedCode = ref<string | null>(null)

const PAGE_SIZES = [10, 25, 50, 100]

const pagination = ref<PaginationState>({
  pageIndex: 0,
  pageSize: 10,
})

const SORT_FIELDS: Record<string, string> = {
  student: 'name',
  number: 'number',
  grade: 'grade',
  registration_code: 'registration_code',
  parent: 'parent',
}

const search = ref('')
const debouncedSearch = refDebounced(search, 400)

const studentsMeta = ref<PaginationMeta>({
  currentPage: 1,
  lastPage: 1,
  perPage: 10,
  total: 0,
})

const totalStudents = computed(() => studentsMeta.value.total)

const showSkeleton = computed(() => studentsLoading.value && studentList.value.length === 0)
const isRefreshing = computed(() => studentsLoading.value && studentList.value.length > 0)
const skeletonRows = computed(() => Math.min(pagination.value.pageSize, 8))

let copyResetTimer: ReturnType<typeof setTimeout> | undefined

async function copyCode(code: string) {
  try {
    await navigator.clipboard.writeText(code)
  } catch {
    studentsError.value = tr.students.copyFailed
    return
  }

  copiedCode.value = code
  clearTimeout(copyResetTimer)
  copyResetTimer = setTimeout(() => {
    copiedCode.value = null
  }, 2000)
}

onUnmounted(() => {
  clearTimeout(copyResetTimer)
})

const columns: ColumnDef<StudentWithParent>[] = [
  {
    id: 'student',
    header: tr.students.columns.student,
    accessorFn: (row) => `${row.name} ${row.surname}`,
    cell: ({ getValue }) => h('span', { class: 'font-medium' }, getValue<string>()),
  },
  {
    accessorKey: 'number',
    header: tr.students.columns.number,
    cell: ({ getValue }) => getValue<string | null>() ?? '—',
  },
  {
    accessorKey: 'grade',
    header: tr.students.columns.grade,
    cell: ({ getValue }) => getValue<string | null>() ?? '—',
  },
  {
    accessorKey: 'registration_code',
    header: tr.students.columns.code,
    cell: ({ getValue }) => {
      const code = getValue<string | null>()
      if (!code) {
        return h('span', { class: 'text-muted-foreground' }, '-')
      }

      const copied = copiedCode.value === code

      return h('div', { class: 'flex items-center gap-1' }, [
        h('span', { class: 'font-mono tracking-wide' }, code),
        h(
          Button,
          {
            variant: 'ghost',
            size: 'icon-xs',
            class: 'text-muted-foreground hover:text-foreground',
            'aria-label': copied ? tr.students.copiedAria : tr.students.copyCode,
            title: copied ? tr.students.copied : tr.students.copyCode,
            onClick: () => copyCode(code),
          },
          () =>
            h(copied ? Check : Copy, { class: copied ? 'size-3.5 text-emerald-600' : 'size-3.5' }),
        ),
      ])
    },
  },
  {
    id: 'parent',
    header: tr.students.columns.parent,
    accessorFn: (row) => (row.parent ? `${row.parent.name} ${row.parent.surname}` : ''),
    cell: ({ row }) => {
      const parent = row.original.parent
      if (!parent) {
        return h('span', { class: 'text-muted-foreground' }, '-')
      }
      return h('div', { class: 'flex flex-col' }, [
        h('span', `${parent.name} ${parent.surname}`),
        h('span', { class: 'text-muted-foreground text-xs' }, parent.email),
      ])
    },
  },
  {
    id: 'registrationCodeGenerate',
    header: tr.students.columns.actions,
    enableSorting: false,
    cell: ({ row }) => {
      const student = row.original
      if (student.parent || student.registration_code) {
        return h('span', { class: 'text-muted-foreground' }, '—')
      }
      return h(
        Button,
        {
          variant: 'outline',
          size: 'sm',
          onClick: () => handleAssignCode(student),
        },
        () => tr.students.generateCode,
      )
    },
  },
]

async function handleAssignCode(student: StudentWithParent) {
  try {
    await StudentService.assignCode(student.id)
    await loadStudents()
  } catch (error) {
    if (error instanceof ApiError && error.status === 401) return
    studentsError.value = error instanceof ApiError ? error.message : tr.errors.unexpected
  }
}

const studentTable = useVueTable({
  get data() {
    return studentList.value
  },
  columns,
  state: {
    get sorting() {
      return sorting.value
    },
    get pagination() {
      return pagination.value
    },
  },
  get pageCount() {
    return studentsMeta.value.lastPage
  },
  manualPagination: true,
  manualSorting: true,
  onSortingChange: (updater) => {
    sorting.value = typeof updater === 'function' ? updater(sorting.value) : updater
    pagination.value = { ...pagination.value, pageIndex: 0 }
  },
  onPaginationChange: (updater) => {
    pagination.value = typeof updater === 'function' ? updater(pagination.value) : updater
  },
  getCoreRowModel: getCoreRowModel(),
})

let listController: AbortController | undefined

async function loadStudents(silent = false) {
  listController?.abort()
  const controller = new AbortController()
  listController = controller

  if (!silent) studentsLoading.value = true
  studentsError.value = ''

  const activeSort = sorting.value[0]

  try {
    const page = await StudentService.list(
      {
        page: pagination.value.pageIndex + 1,
        perPage: pagination.value.pageSize,
        search: debouncedSearch.value.trim() || undefined,
        sort: activeSort ? SORT_FIELDS[activeSort.id] : undefined,
        direction: activeSort ? (activeSort.desc ? 'desc' : 'asc') : undefined,
      },
      controller.signal,
    )

    studentList.value = page.data
    studentsMeta.value = page.meta

    const lastIndex = page.meta.lastPage - 1
    if (pagination.value.pageIndex > lastIndex) {
      pagination.value = { ...pagination.value, pageIndex: lastIndex }
    }
  } catch (error) {
    if (isCanceled(error)) return
    if (error instanceof ApiError && error.status === 401) return
    studentsError.value = error instanceof ApiError ? error.message : tr.students.loadError
  } finally {
    if (listController === controller) studentsLoading.value = false
  }
}

watch(debouncedSearch, () => {
  pagination.value = { ...pagination.value, pageIndex: 0 }
})

watch(
  [pagination, sorting, debouncedSearch],
  () => {
    if (auth.isAdmin) loadStudents()
  },
  { deep: true },
)

onMounted(async () => {
  if (!auth.user) {
    try {
      await auth.fetchMe()
    } catch (error) {
      if (error instanceof ApiError && error.status === 401) return
      loadError.value = error instanceof ApiError ? error.message : tr.errors.profileLoad
      return
    }
  }

  if (auth.isAdmin) {
    await loadStudents()
  }
})
</script>

<template>
  <div class="bg-muted/40 min-h-svh w-full">
    <AppHeader />

    <main class="mx-auto flex w-full max-w-5xl flex-col gap-6 px-6 py-8">
      <div
        v-if="!auth.user"
        role="status"
        class="border-border bg-background text-muted-foreground rounded-xl border px-4 py-3 text-sm shadow-sm"
      >
        {{ loadError || tr.errors.profileLoad }}
      </div>

      <Card v-else>
        <CardHeader>
          <CardTitle class="text-xl"> {{ tr.account.title }} </CardTitle>
        </CardHeader>
        <CardContent>
          <dl class="text-sm">
            <div class="border-border flex items-center justify-between gap-4 border-b py-3">
              <dt class="text-muted-foreground">{{ tr.account.fullName }}</dt>
              <dd class="text-foreground text-right font-medium">
                {{ auth.fullName || '-' }}
              </dd>
            </div>
            <div class="border-border flex items-center justify-between gap-4 border-b py-3">
              <dt class="text-muted-foreground">{{ tr.account.email }}</dt>
              <dd class="text-foreground text-right font-medium break-all">
                {{ auth.user?.email ?? '-' }}
              </dd>
            </div>
            <div class="flex items-center justify-between gap-4 py-3">
              <dt class="text-muted-foreground">{{ tr.account.role }}</dt>
              <dd class="text-foreground text-right font-medium">
                {{ roleLabel }}
              </dd>
            </div>
          </dl>
        </CardContent>
      </Card>

      <Card v-if="auth.user && auth.user.role == 'ROLE_USER'">
        <CardHeader>
          <CardTitle class="text-xl"> {{ tr.myStudents.title }} </CardTitle>
          <CardDescription> {{ tr.myStudents.description }} </CardDescription>
        </CardHeader>
        <CardContent>
          <p v-if="students.length === 0" class="text-muted-foreground text-sm">
            {{ tr.myStudents.empty }}
          </p>
          <ul v-else class="text-sm">
            <li
              v-for="(student, index) in students"
              :key="student.id"
              :class="[
                'flex flex-col gap-1 py-3 sm:flex-row sm:items-center sm:justify-between sm:gap-4',
                index < students.length - 1 ? 'border-border border-b' : '',
              ]"
            >
              <span class="text-foreground font-medium">
                {{ student.name }} {{ student.surname }}
              </span>
              <span class="text-muted-foreground flex items-center gap-3">
                <span
                  >{{ tr.myStudents.numberLabel }}:
                  {{ student.number ?? tr.myStudents.notSpecified }}</span
                >
                <span
                  >{{ tr.myStudents.gradeLabel }}:
                  {{ student.grade ?? tr.myStudents.notSpecified }}</span
                >
              </span>
            </li>
          </ul>
        </CardContent>
      </Card>
      <Card v-if="auth.user && auth.isAdmin">
        <CardHeader>
          <CardTitle class="text-xl"> {{ tr.students.title }} </CardTitle>
          <CardDescription> {{ tr.students.description }} </CardDescription>
        </CardHeader>
        <CardContent>
          <div class="relative mb-4 sm:max-w-xs">
            <Search
              class="text-muted-foreground pointer-events-none absolute inset-y-0 left-3 my-auto size-4"
            />
            <Input
              v-model="search"
              type="search"
              :placeholder="tr.students.searchPlaceholder"
              class="pl-9"
              :aria-label="tr.students.searchLabel"
            />
          </div>

          <div
            v-if="studentsError"
            role="alert"
            class="border-destructive/40 bg-destructive/10 text-destructive rounded-md border px-3 py-2 text-sm"
          >
            {{ studentsError }}
          </div>

          <div
            v-else
            class="border-border overflow-hidden rounded-md border transition-opacity [&>[data-slot=table-container]]:max-h-[60vh]"
            :class="isRefreshing ? 'pointer-events-none opacity-60' : ''"
            :aria-busy="studentsLoading"
          >
            <Table>
              <TableHeader class="bg-muted sticky top-0 z-10">
                <TableRow
                  v-for="headerGroup in studentTable.getHeaderGroups()"
                  :key="headerGroup.id"
                  class="bg-muted hover:bg-muted"
                >
                  <TableHead v-for="header in headerGroup.headers" :key="header.id">
                    <FlexRender
                      v-if="!header.column.getCanSort()"
                      :render="header.column.columnDef.header"
                      :props="header.getContext()"
                    />
                    <button
                      v-else
                      type="button"
                      class="hover:text-foreground focus-visible:ring-ring/50 inline-flex items-center gap-1 rounded-sm outline-none focus-visible:ring-2"
                      @click="header.column.toggleSorting()"
                    >
                      <FlexRender
                        :render="header.column.columnDef.header"
                        :props="header.getContext()"
                      />
                      <ChevronUp v-if="header.column.getIsSorted() === 'asc'" class="size-3.5" />
                      <ChevronDown
                        v-else-if="header.column.getIsSorted() === 'desc'"
                        class="size-3.5"
                      />
                      <ChevronsUpDown v-else class="size-3.5 opacity-40" />
                    </button>
                  </TableHead>
                </TableRow>
              </TableHeader>
              <TableBody v-if="showSkeleton">
                <TableRow v-for="row in skeletonRows" :key="`skeleton-${row}`">
                  <TableCell v-for="column in columns.length" :key="`skeleton-${row}-${column}`">
                    <Skeleton class="h-4 w-full" />
                  </TableCell>
                </TableRow>
              </TableBody>

              <TableBody v-else>
                <TableRow v-for="row in studentTable.getRowModel().rows" :key="row.id">
                  <TableCell v-for="cell in row.getVisibleCells()" :key="cell.id">
                    <FlexRender :render="cell.column.columnDef.cell" :props="cell.getContext()" />
                  </TableCell>
                </TableRow>
                <TableRow v-if="studentTable.getRowModel().rows.length === 0">
                  <TableCell
                    :colspan="columns.length"
                    class="text-muted-foreground h-24 text-center"
                  >
                    {{ debouncedSearch.trim() ? tr.students.emptySearch : tr.students.empty }}
                  </TableCell>
                </TableRow>
              </TableBody>
            </Table>
          </div>

          <div
            v-if="!studentsError && !showSkeleton"
            class="flex flex-col items-center justify-between gap-4 pt-4 sm:flex-row"
          >
            <div class="text-muted-foreground flex items-center gap-2 text-sm">
              <span>{{ tr.students.pagination.total(totalStudents) }}</span>
              <span class="hidden sm:inline">·</span>
              <label for="page-size" class="hidden sm:inline">{{
                tr.students.pagination.perPage
              }}</label>
              <select
                id="page-size"
                :value="pagination.pageSize"
                class="border-input bg-background focus-visible:border-ring focus-visible:ring-ring/50 h-8 rounded-md border px-2 text-sm outline-none focus-visible:ring-2"
                @change="
                  studentTable.setPageSize(Number(($event.target as HTMLSelectElement).value))
                "
              >
                <option v-for="size in PAGE_SIZES" :key="size" :value="size">
                  {{ size }}
                </option>
              </select>
            </div>

            <div class="flex items-center gap-2">
              <span class="text-muted-foreground text-sm">
                {{
                  tr.students.pagination.pageInfo(
                    pagination.pageIndex + 1,
                    Math.max(studentTable.getPageCount(), 1),
                  )
                }}
              </span>
              <Button
                variant="outline"
                size="icon-sm"
                :aria-label="tr.students.pagination.firstPage"
                :disabled="!studentTable.getCanPreviousPage()"
                @click="studentTable.setPageIndex(0)"
              >
                <ChevronsLeft class="size-4" />
              </Button>
              <Button
                variant="outline"
                size="icon-sm"
                :aria-label="tr.students.pagination.prevPage"
                :disabled="!studentTable.getCanPreviousPage()"
                @click="studentTable.previousPage()"
              >
                <ChevronLeft class="size-4" />
              </Button>
              <Button
                variant="outline"
                size="icon-sm"
                :aria-label="tr.students.pagination.nextPage"
                :disabled="!studentTable.getCanNextPage()"
                @click="studentTable.nextPage()"
              >
                <ChevronRight class="size-4" />
              </Button>
              <Button
                variant="outline"
                size="icon-sm"
                :aria-label="tr.students.pagination.lastPage"
                :disabled="!studentTable.getCanNextPage()"
                @click="studentTable.setPageIndex(studentTable.getPageCount() - 1)"
              >
                <ChevronsRight class="size-4" />
              </Button>
            </div>
          </div>
        </CardContent>
      </Card>
    </main>
  </div>
</template>
