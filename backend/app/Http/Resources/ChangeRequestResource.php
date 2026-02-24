<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChangeRequestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'ticket_number' => $this->ticket_number,
            'title' => $this->title,
            'description' => $this->description,
            'system' => [
                'id' => $this->system->id,
                'name' => $this->system->name,
                'code' => $this->system->code,
            ],
            'change_type' => $this->change_type,
            'risk_level' => $this->risk_level,
            'impact_analysis' => $this->impact_analysis,
            'rollback_plan' => $this->rollback_plan,
            'planned_start' => $this->planned_start?->format('Y-m-d H:i:s'),
            'planned_end' => $this->planned_end?->format('Y-m-d H:i:s'),
            'actual_start' => $this->actual_start?->format('Y-m-d H:i:s'),
            'actual_end' => $this->actual_end?->format('Y-m-d H:i:s'),
            'status' => $this->status,
            'rejection_reason' => $this->rejection_reason,
            'requester' => [
                'id' => $this->requester->id,
                'name' => $this->requester->name,
                'employee_id' => $this->requester->employee_id,
            ],
            'implementer' => $this->implementer ? [
                'id' => $this->implementer->id,
                'name' => $this->implementer->name,
            ] : null,
            'approved_by' => $this->approver ? [
                'id' => $this->approver->id,
                'name' => $this->approver->name,
            ] : null,
            'approved_at' => $this->approved_at?->format('Y-m-d H:i:s'),
            'evidences' => $this->evidences->map(function ($evidence) {
                return [
                    'id' => $evidence->id,
                    'file_name' => $evidence->file_name,
                    'file_type' => $evidence->file_type,
                    'file_size' => $evidence->file_size,
                    'description' => $evidence->description,
                    'uploaded_at' => $evidence->created_at->format('Y-m-d H:i:s'),
                ];
            }),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}
