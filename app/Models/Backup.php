<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Backup extends Model
{
    protected $fillable = [
        'name',
        'type',
        'file_path',
        'file_size',
        'storage_location',
        'external_path',
        'status',
        'error_message',
        'is_scheduled',
        'schedule_frequency'
    ];

    protected $casts = [
        'file_size' => 'integer',
        'is_scheduled' => 'boolean',
    ];

    public function getFileSizeHumanAttribute()
    {
        $bytes = $this->file_size;
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }
}
