<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ItAssetsExport implements FromCollection, WithHeadings, WithMapping
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
            'Asset Tag',
            'Name',
            'Category',
            'Brand',
            'Model',
            'Serial Number',
            'Specifications',
            'Purchase Date',
            'Purchase Price',
            'Warranty Expiry',
            'Status',
            'Assigned To',
            'Assigned User Name',
            'Department ID',
            'Department Name',
            'Location',
            'Notes',
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
            $item->asset_tag,
            $item->name,
            $item->category,
            $item->brand,
            $item->model,
            $item->serial_number,
            $item->specifications,
            $this->formatDate($item->purchase_date),
            $item->purchase_price,
            $item->warranty_expiry,
            $item->status,
            $item->assigned_to,
            $item->assignedUser?->name ?? '',
            $item->department_id,
            $item->department?->name ?? '',
            $item->location,
            $item->notes,
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
