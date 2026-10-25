<?php

namespace App\Http\Requests;

use App\Models\Student;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StoreStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var Student|null $student */
        $student = $this->route('student');
        $isUpdate = $student !== null;

        return [
            'classroom_id' => ['required', 'integer', 'exists:classrooms,id'],
            'nisn' => [
                'required',
                'string',
                'max:30',
                Rule::unique('students', 'nisn')->ignore($student?->id),
            ],
            'name' => ['required', 'string', 'max:255'],
            'gender' => ['required', 'in:L,P'],
            'password' => [
                $isUpdate ? 'nullable' : 'required',
                'string',
                Password::min(6),
            ],
        ];
    }
}
