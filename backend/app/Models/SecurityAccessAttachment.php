<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class SecurityAccessAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'security_access_request_id',
        'file_path',
        'file_name',
        'mime_type',
        'file_size',
        'uploaded_by',
    ];

    protected $appends = ['url'];

    public function request()
    {
        return $this->belongsTo(SecurityAccessRequest::class, 'security_access_request_id');
    }

    public function getUrlAttribute(): string
    {
        return Storage::url($this->file_path);
    }
}
