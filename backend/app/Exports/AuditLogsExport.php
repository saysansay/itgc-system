<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AuditLogsExport implements FromCollection, WithHeadings, WithMapping
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
            'User ID',
            'User Name',
            'Action',
            'Module',
            'Entity Type',
            'Entity ID',
            'Description',
            'Old Values',
            'New Values',
            'IP Address',
            'User Agent',
            'Created At',
            'Updated At'
        ];
    }

    public function map($item): array
    {
        return [
            $item->id,
            $item->user_id,
            $item->user?->name ?? '',
            $item->action,
            $item->module,
            $item->entity_type,
            $item->entity_id,
            $item->description,
            $this->formatJson($item->old_values),
            $this->formatJson($item->new_values),
            $item->ip_address,
            $item->user_agent,
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

    private function formatJson($value): string
    {
        if (is_array($value) || is_object($value)) {
            $encoded = json_encode($value);
            return $encoded === false ? '' : $encoded;
        }

        return $value ?? '';
    }
}
