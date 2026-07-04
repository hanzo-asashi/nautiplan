<?php

namespace App\Http\Requests\Activity;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSubActivityStatusRequest extends FormRequest
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
            'status' => ['required', 'string', 'in:pending,in_progress,completed,cancelled'],
            'progress_percentage' => ['required', 'integer', 'min:0', 'max:100'],
        ];
    }
}
