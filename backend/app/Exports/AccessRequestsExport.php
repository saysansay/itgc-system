<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AccessRequestsExport implements FromCollection, WithHeadings, WithMapping
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
            'Request Number',
            'User ID',
            'User Name',
            'System ID',
            'System Name',
            'Access Type',
            'Access Level',
            'Purpose',
            'Start Date',
            'End Date',
            'Status',
            'Rejection Reason',
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
            $item->request_number ?? $item->ticket_number,
            $item->user_id,
            $item->user?->name ?? '',
            $item->system_id,
            $item->system?->name ?? '',
            $item->access_type,
            $item->access_level ?? '',
            $item->purpose,
            $this->formatDate($item->start_date),
            $this->formatDate($item->end_date),
            $item->status,
            $item->rejection_reason,
            $item->approved_by,
            $item->approver?->name ?? '',
            $this->formatDateTime($item->approved_at),
            $item->created_by,
            $item->updated_by,
            $this->formatDateTime($item->created_at),
            $this->formatDateTime($item->updated_at)
        ];
    }

    private function formatDate($value): string
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d');
        }

        return $value ?? '';
    }

    private function formatDateTime($value): string
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d H:i:s');
        }

        return $value ?? '';
    }
}
