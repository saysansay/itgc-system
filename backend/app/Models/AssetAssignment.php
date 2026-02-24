<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'it_asset_id',
        'user_id',
        'assigned_date',
        'returned_date',
        'assignment_notes',
        'return_notes',
        'condition_on_assign',
        'condition_on_return',
        'assigned_by',
        'received_by',
    ];

    protected $casts = [
        'assigned_date' => 'date',
        'returned_date' => 'date',
    ];

    // Relationships
    public function asset()
    {
        return $this->belongsTo(ItAsset::class, 'it_asset_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function receivedBy()
    {
        return $this->belongsTo(User::class, 'received_by');
    }
}
