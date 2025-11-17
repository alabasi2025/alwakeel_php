<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SavedCommand extends Model
{
    protected $fillable = [
        'name',
        'command',
        'description',
        'category',
        'shell_type',
        'icon',
        'usage_count',
        'is_favorite'
    ];

    protected $casts = [
        'usage_count' => 'integer',
        'is_favorite' => 'boolean',
    ];

    public function incrementUsage()
    {
        $this->increment('usage_count');
    }
}
