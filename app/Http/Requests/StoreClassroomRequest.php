<?php

namespace App\Http\Requests;

use App\Models\Classroom;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreClassroomRequest extends FormRequest
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
        /** @var Classroom|null $classroom */
        $classroom = $this->route('classroom');

        return [
            'title' => [
                'required',
                'string',
                'max:255',
                Rule::unique('classrooms', 'title')->ignore($classroom?->id),
            ],
        ];
    }
}
