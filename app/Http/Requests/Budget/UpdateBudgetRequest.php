<?php

namespace App\Http\Requests\Budget;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateBudgetRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization is handled by Policies in Controller
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'budget_category' => ['required', 'string', 'in:personnel,goods_services,capital,other'],
            'account_code' => ['nullable', 'string', 'max:50'],
            'account_name' => ['nullable', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:255'],
            'revision_description' => ['required', 'string', 'max:1000'],

            // Items
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['nullable', 'exists:budget_items,id'],
            'items.*.name' => ['required', 'string', 'max:255'],
            'items.*.volume' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit' => ['required', 'string', 'max:50'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ];
    }
}
