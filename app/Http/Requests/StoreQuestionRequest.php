<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreQuestionRequest extends FormRequest
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
            'question' => ['required', 'string'],
            'options' => ['required', 'array', 'min:2', 'max:5'],
            'options.*' => ['required', 'string', 'max:1000'],
            'answer_index' => ['required', 'integer', 'min:0'],
        ];
    }

    /**
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $options = $this->input('options');

                if (! is_array($options) || $validator->errors()->hasAny(['options', 'answer_index'])) {
                    return;
                }

                if ((int) $this->input('answer_index') >= count($options)) {
                    $validator->errors()->add(
                        'answer_index',
                        'Jawaban benar harus menunjuk salah satu pilihan yang tersedia.',
                    );
                }
            },
        ];
    }
}
