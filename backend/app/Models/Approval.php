<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Approval extends Model
{
    use HasFactory;

    protected $fillable = [
        'approval_token',
        'approvable_type',
        'approvable_id',
        'level',
        'approver_id',
        'status',
        'comments',
        'responded_at',
        'responded_ip',
    ];

    protected $casts = [
        'responded_at' => 'datetime',
    ];

    // Relationships
    public function approvable()
    {
        return $this->morphTo();
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    // Boot method to generate approval token
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->approval_token) {
                $model->approval_token = Str::random(64);
            }
        });
    }
}
