<?php

namespace App\Http\Controllers;

use App\Http\Resources\StudentCollectionResource;
use App\Http\Resources\StudentDetailResource;
use App\Models\Student;
use App\Services\StudentService;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function list(Request $request,StudentService $studentService)
    {
        $paginated = $studentService->list(
            $request->get('page'),
            $request->get('perPage'),
            $request->get('search')
        );
        return new StudentCollectionResource($paginated);
    }

    public function assignCode(Student $student, StudentService $studentService)
    {
        $student = $studentService->assignCode($student);

        return (new StudentDetailResource($student))
            ->withMessage('general.code_assigned');
    }
}
