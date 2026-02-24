<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class UsbLoansExport implements FromCollection, WithHeadings, WithMapping
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
            'Loan Number',
            'Requestor ID',
            'Requestor Name',
            'Department ID',
            'Department Name',
            'Purpose',
            'Loan Datetime',
            'Return Datetime',
            'Actual Return Datetime',
            'PIC ID',
            'PIC Name',
            'Notes',
            'Status',
            'Approved By',
            'Approved By Name',
            'Approved At',
            'Rejection Reason',
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
            $item->loan_number,
            $item->requestor_id,
            $item->requestor?->name ?? '',
            $item->department_id,
            $item->department?->name ?? '',
            $item->purpose,
            $this->formatDateTime($item->loan_datetime),
            $this->formatDateTime($item->return_datetime),
            $this->formatDateTime($item->actual_return_datetime),
            $item->pic_id,
            $item->pic?->name ?? '',
            $item->notes,
            $item->status,
            $item->approved_by,
            $item->approver?->name ?? '',
            $this->formatDateTime($item->approved_at),
            $item->rejection_reason,
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
