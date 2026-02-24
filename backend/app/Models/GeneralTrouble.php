<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GeneralTrouble extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'trouble_number',
        'user_id',
        'reported_at',
        'duration_value',
        'duration_unit',
        'problem',
        'analysis',
        'solution',
        'type',
        'pic_id',
        'partner',
        'notes',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'reported_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function pic()
    {
        return $this->belongsTo(User::class, 'pic_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public static function generateTroubleNumber(): string
    {
        $yearMonth = date('Ym');
        $prefix = "GT-{$yearMonth}-";

        $lastRequest = self::where('trouble_number', 'like', "{$prefix}%")
            ->orderBy('trouble_number', 'desc')
            ->first();

        if ($lastRequest) {
            $lastNumber = (int) substr($lastRequest->trouble_number, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }
}
