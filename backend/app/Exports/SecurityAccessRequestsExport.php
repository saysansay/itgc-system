<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SecurityAccessRequestsExport implements FromCollection, WithHeadings, WithMapping
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
            'Requested At',
            'Requestor ID',
            'Requestor Name',
            'Department ID',
            'Department Name',
            'Username',
            'User ID Action',
            'Password Action',
            'Email Action',
            'Internet Access',
            'File Sharing',
            'VPN Action',
            'Reason',
            'ACCPAC Action',
            'IFS Action',
            'Administrator Action',
            'Restore',
            'Fingerprint Action',
            'Change Data Action',
            'Notes',
            'Status',
            'Approved By',
            'Approved By Name',
            'Approval Date',
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
            $this->formatDateTime($item->requested_at),
            $item->requestor_id,
            $item->requestor?->name ?? '',
            $item->department_id,
            $item->department?->name ?? '',
            $item->username,
            $item->user_id_action,
            $item->password_action,
            $item->email_action,
            $item->internet_access,
            $item->file_sharing,
            $item->vpn_action,
            $item->reason,
            $item->accpac_action,
            $item->ifs_action,
            $item->administrator_action,
            $item->restore ? '1' : '0',
            $item->fingerprint_action,
            $item->change_data_action,
            $item->notes,
            $item->status,
            $item->approved_by,
            $item->approver?->name ?? '',
            $this->formatDateTime($item->approval_date),
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
