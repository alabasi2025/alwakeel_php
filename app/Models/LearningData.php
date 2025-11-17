<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LearningData extends Model
{
    use HasFactory;
    
    protected $table = 'learning_data';
    
    protected $fillable = [
        'user_input',
        'system_response',
        'success_score',
        'source',
        'usage_count',
        'pattern',
        'suggestion',
        'frequency',
        'confidence',
        'category'
    ];
    
    protected $casts = [
        'success_score' => 'float',
        'usage_count' => 'integer',
        'frequency' => 'integer',
        'confidence' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];
}
