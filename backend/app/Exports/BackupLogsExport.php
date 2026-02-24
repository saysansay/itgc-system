<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class BackupLogsExport implements FromCollection, WithHeadings, WithMapping
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
            'System ID',
            'System Name',
            'Backup Type',
            'Scheduled Time',
            'Start Time',
            'End Time',
            'Status',
            'Backup Location',
            'Backup Size',
            'Error Message',
            'Is Verified',
            'Verified By',
            'Verified By Name',
            'Verified At',
            'Verification Notes',
            'Evidence File Path',
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
            $item->system_id,
            $item->system?->name ?? '',
            $item->backup_type,
            $this->formatDateTime($item->scheduled_time),
            $this->formatDateTime($item->start_time),
            $this->formatDateTime($item->end_time),
            $item->status,
            $item->backup_location,
            $item->backup_size,
            $item->error_message,
            $item->is_verified ? '1' : '0',
            $item->verified_by,
            $item->verifier?->name ?? '',
            $this->formatDateTime($item->verified_at),
            $item->verification_notes,
            $item->evidence_file_path,
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
