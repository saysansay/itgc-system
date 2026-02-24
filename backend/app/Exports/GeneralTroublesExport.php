<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class GeneralTroublesExport implements FromCollection, WithHeadings, WithMapping
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
            'Trouble Number',
            'User ID',
            'User Name',
            'Reported At',
            'Duration Value',
            'Duration Unit',
            'Problem',
            'Analysis',
            'Solution',
            'Type',
            'PIC ID',
            'PIC Name',
            'Partner',
            'Notes',
            'Status',
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
            $item->trouble_number,
            $item->user_id,
            $item->user?->name ?? '',
            $this->formatDateTime($item->reported_at),
            $item->duration_value,
            $item->duration_unit,
            $item->problem,
            $item->analysis,
            $item->solution,
            $item->type,
            $item->pic_id,
            $item->pic?->name ?? '',
            $item->partner,
            $item->notes,
            $item->status,
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
