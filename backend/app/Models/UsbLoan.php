<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UsbLoan extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'loan_number',
        'requestor_id',
        'department_id',
        'purpose',
        'loan_datetime',
        'return_datetime',
        'actual_return_datetime',
        'pic_id',
        'notes',
        'status',
        'approved_by',
        'approved_at',
        'rejection_reason',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'loan_datetime' => 'datetime',
        'return_datetime' => 'datetime',
        'actual_return_datetime' => 'datetime',
        'approved_at' => 'datetime',
    ];

    public function requestor()
    {
        return $this->belongsTo(User::class, 'requestor_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function pic()
    {
        return $this->belongsTo(User::class, 'pic_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public static function generateLoanNumber()
    {
        $yearMonth = date('Ym'); // Format: YYYYMM
        $prefix = "USB-{$yearMonth}-";
        
        // Get last loan number for this month
        $lastLoan = self::where('loan_number', 'like', "{$prefix}%")
            ->orderBy('loan_number', 'desc')
            ->first();
        
        if ($lastLoan) {
            // Extract number and increment
            $lastNumber = (int) substr($lastLoan->loan_number, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }
}
