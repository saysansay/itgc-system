<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChangeRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'title' => 'required|string|max:200',
            'description' => 'required|string',
            'system_id' => 'required|exists:systems,id',
            'change_type' => 'required|in:enhancement,bug_fix,configuration,emergency',
            'risk_level' => 'required|in:low,medium,high',
            'impact_analysis' => 'required|string',
            'rollback_plan' => 'required|string',
            'planned_start' => 'nullable|date',
            'planned_end' => 'nullable|date|after:planned_start',
            'implementer_id' => 'nullable|exists:users,id',
        ];

        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $rules['title'] = 'sometimes|' . $rules['title'];
            $rules['description'] = 'sometimes|' . $rules['description'];
            $rules['system_id'] = 'sometimes|' . $rules['system_id'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Title is required',
            'description.required' => 'Description is required',
            'system_id.required' => 'System is required',
            'system_id.exists' => 'Selected system does not exist',
            'impact_analysis.required' => 'Impact analysis is required',
            'rollback_plan.required' => 'Rollback plan is required',
            'planned_end.after' => 'Planned end date must be after planned start date',
        ];
    }
}
