<?php

namespace App\Http\Requests\Activity;

use Illuminate\Foundation\Http\FormRequest;

class UploadActivityDocumentRequest extends FormRequest
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
            'file' => ['required', 'file', 'max:10240', 'mimes:pdf,docx,xlsx,png,jpg'],
            'description' => ['nullable', 'string'],
            'parent_id' => ['nullable', 'integer', 'exists:activity_documents,id'],
        ];
    }
}
