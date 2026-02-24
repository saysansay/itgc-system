<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RestoreTestLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'backup_log_id',
        'test_date',
        'tested_by',
        'status',
        'test_notes',
        'issues_found',
        'evidence_file_path',
    ];

    protected $casts = [
        'test_date' => 'datetime',
    ];

    // Relationships
    public function backupLog()
    {
        return $this->belongsTo(BackupLog::class);
    }

    public function tester()
    {
        return $this->belongsTo(User::class, 'tested_by');
    }
}
