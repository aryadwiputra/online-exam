<?php

namespace App\Http\Requests;

use App\Models\Lesson;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLessonRequest extends FormRequest
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
        /** @var Lesson|null $lesson */
        $lesson = $this->route('lesson');

        return [
            'title' => [
                'required',
                'string',
                'max:255',
                Rule::unique('lessons', 'title')->ignore($lesson?->id),
            ],
        ];
    }
}
