<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BackupLog extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'system_id',
        'backup_type',
        'scheduled_time',
        'start_time',
        'end_time',
        'status',
        'backup_location',
        'backup_size',
        'error_message',
        'is_verified',
        'verified_by',
        'verified_at',
        'verification_notes',
        'evidence_file_path',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'scheduled_time' => 'datetime',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'verified_at' => 'datetime',
        'is_verified' => 'boolean',
    ];

    // Relationships
    public function system()
    {
        return $this->belongsTo(System::class);
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function restoreTests()
    {
        return $this->hasMany(RestoreTestLog::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
