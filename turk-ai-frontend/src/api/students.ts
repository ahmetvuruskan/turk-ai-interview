import { http } from '@/lib/http'
import type { Student, StudentListParams, StudentWithParent } from '@/types/api'

export const StudentService = {
  list: (params?: StudentListParams, signal?: AbortSignal) =>
    http.getPage<StudentWithParent>('/students', { params, signal }),
  assignCode: (id: string) => http.post<Student>(`/students/${id}/code`),
}
