<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ChangeRequestsExport implements FromCollection, WithHeadings, WithMapping
{
    private $items;

    public function __construct($items)
    {
        $this->items = $items;
    }

    public function collection()
    {
        return $this->items;
    }

    public function headings(): array
    {
        return [
            'ID',
            'Ticket Number',
            'Title',
            'Description',
            'System ID',
            'System Name',
            'Change Type',
            'Risk Level',
            'Impact Analysis',
            'Rollback Plan',
            'Planned Start',
            'Planned End',
            'Actual Start',
            'Actual End',
            'Status',
            'Rejection Reason',
            'Requester ID',
            'Requester Name',
            'Implementer ID',
            'Implementer Name',
            'Approved By',
            'Approved By Name',
            'Approved At',
            'Created By',
            'Updated By',
            'Created At',
            'Updated At'
        ];
    }

    public function map($item): array
    {
        return [
            $item->id,
            $item->ticket_number,
            $item->title,
            $item->description,
            $item->system_id,
            $item->system?->name ?? '',
            $item->change_type,
            $item->risk_level,
            $item->impact_analysis,
            $item->rollback_plan,
            $this->formatDateTime($item->planned_start),
            $this->formatDateTime($item->planned_end),
            $this->formatDateTime($item->actual_start),
            $this->formatDateTime($item->actual_end),
            $item->status,
            $item->rejection_reason,
            $item->requester_id,
            $item->requester?->name ?? '',
            $item->implementer_id,
            $item->implementer?->name ?? '',
            $item->approved_by,
            $item->approver?->name ?? '',
            $this->formatDateTime($item->approved_at),
            $item->created_by,
            $item->updated_by,
            $this->formatDateTime($item->created_at),
            $this->formatDateTime($item->updated_at)
        ];
    }

    private function formatDateTime($value): string
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d H:i:s');
        }

        return $value ?? '';
    }
}
