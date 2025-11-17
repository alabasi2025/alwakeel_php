<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TerminalCommand extends Model
{
    protected $fillable = [
        'shell_type',
        'command',
        'output',
        'exit_code',
        'execution_time',
        'working_directory',
        'status'
    ];

    protected $casts = [
        'execution_time' => 'float',
        'exit_code' => 'integer',
    ];
}
