<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreExamRequest extends FormRequest
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
        return [
            'title' => ['required', 'string', 'max:255'],
            'lesson_id' => ['required', 'integer', 'exists:lessons,id'],
            'classroom_id' => ['required', 'integer', 'exists:classrooms,id'],
            'duration' => ['required', 'integer', 'min:1', 'max:1440'],
            'description' => ['nullable', 'string'],
            'random_question' => ['required', 'boolean'],
            'random_answer' => ['required', 'boolean'],
            'show_answer' => ['required', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'duration.min' => 'Durasi ujian minimal 1 menit.',
            'duration.max' => 'Durasi ujian maksimal 1440 menit.',
        ];
    }
}
