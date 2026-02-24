<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SecurityAccessRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'request_number',
        'requested_at',
        'requestor_id',
        'department_id',
        'username',
        'user_id_action',
        'password_action',
        'email_action',
        'internet_access',
        'file_sharing',
        'vpn_action',
        'reason',
        'accpac_action',
        'ifs_action',
        'administrator_action',
        'restore',
        'fingerprint_action',
        'change_data_action',
        'status',
        'approval_date',
        'approved_by',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'requested_at' => 'datetime',
        'approval_date' => 'datetime',
        'restore' => 'boolean',
    ];

    public function requestor()
    {
        return $this->belongsTo(User::class, 'requestor_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function attachments()
    {
        return $this->hasMany(SecurityAccessAttachment::class);
    }

    public static function generateRequestNumber(): string
    {
        $yearMonth = date('Ym');
        $prefix = "SAF-{$yearMonth}-";

        $lastRequest = self::where('request_number', 'like', "{$prefix}%")
            ->orderBy('request_number', 'desc')
            ->first();

        if ($lastRequest) {
            $lastNumber = (int) substr($lastRequest->request_number, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }
}
