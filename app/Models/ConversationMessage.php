<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConversationMessage extends Model
{
    protected $fillable = ['conversation_id', 'role', 'content', 'ai_source'];
    
    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }
}
