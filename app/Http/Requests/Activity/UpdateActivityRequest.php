<?php

namespace App\Http\Requests\Activity;

use App\Models\Activity;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateActivityRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $activity = $this->route('activity');
        $activityId = $activity instanceof Activity ? $activity->id : ($activity ? (string) $activity : '');

        return [
            'code' => ['required', 'string', 'unique:activities,code,'.$activityId.',id,fiscal_year_id,'.$this->input('fiscal_year_id')],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'program_id' => ['required', 'exists:programs,id'],
            'renja_id' => ['nullable', 'exists:renjas,id'],
            'unit_id' => ['required', 'exists:units,id'],
            'fiscal_year_id' => ['required', 'exists:fiscal_years,id'],
            'responsible_user_id' => ['nullable', 'exists:users,id'],
            'status' => ['required', 'string', 'in:draft,proposed,approved,in_progress,completed,cancelled'],
            'priority' => ['required', 'string', 'in:low,medium,high,critical'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'progress_percentage' => ['required', 'integer', 'min:0', 'max:100'],
            'location' => ['nullable', 'string'],

            // Sub Activities
            'sub_activities' => ['nullable', 'array'],
            'sub_activities.*.id' => ['nullable', 'exists:sub_activities,id'],
            'sub_activities.*.name' => ['required', 'string'],
            'sub_activities.*.description' => ['nullable', 'string'],
            'sub_activities.*.status' => ['required', 'string', 'in:pending,in_progress,completed,cancelled'],
            'sub_activities.*.start_date' => ['nullable', 'date'],
            'sub_activities.*.end_date' => ['nullable', 'date', 'after_or_equal:sub_activities.*.start_date'],
            'sub_activities.*.progress_percentage' => ['required', 'integer', 'min:0', 'max:100'],
            'sub_activities.*.assigned_to' => ['nullable', 'exists:users,id'],

            // Indicators
            'indicators' => ['nullable', 'array'],
            'indicators.*.id' => ['nullable', 'exists:activity_indicators,id'],
            'indicators.*.code' => ['required', 'string', 'max:255'],
            'indicators.*.name' => ['required', 'string', 'max:255'],
            'indicators.*.indicator_type' => ['required', 'string', 'in:iku,ikk'],
            'indicators.*.target_value' => ['required', 'numeric', 'min:0'],
            'indicators.*.actual_value' => ['nullable', 'numeric', 'min:0'],
            'indicators.*.unit_of_measure' => ['required', 'string', 'max:255'],
            'indicators.*.quarter' => ['required', 'string', 'in:Q1,Q2,Q3,Q4,annual'],
        ];
    }
}
