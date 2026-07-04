<?php

namespace App\Http\Requests\Budget;

use App\Models\Activity;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBudgetRequest extends FormRequest
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
            'activity_id' => ['required', Rule::exists(Activity::class, 'id')],
            'budget_category' => ['required', 'string', 'in:personnel,goods_services,capital,other'],
            'account_code' => ['nullable', 'string', 'max:50'],
            'account_name' => ['nullable', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0'],
            'fiscal_year_id' => ['required', 'exists:fiscal_years,id'],
        ];
    }
}
