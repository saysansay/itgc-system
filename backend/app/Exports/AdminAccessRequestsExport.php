<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AdminAccessRequestsExport implements FromCollection, WithHeadings, WithMapping
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
            'Requestor ID',
            'Requestor Name',
            'Department ID',
            'Department Name',
            'Request Type',
            'Duration Value',
            'Duration Unit',
            'Method',
            'Hostname',
            'User Administrator',
            'Purpose',
            'Requested At',
            'Partner',
            'Notes',
            'Status',
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
            $item->request_number,
            $item->requestor_id,
            $item->requestor?->name ?? '',
            $item->department_id,
            $item->department?->name ?? '',
            $item->request_type,
            $item->duration_value,
            $item->duration_unit,
            $item->method,
            $item->hostname,
            $item->user_administrator,
            $item->purpose,
            $this->formatDateTime($item->requested_at),
            $item->partner,
            $item->notes,
            $item->status,
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
