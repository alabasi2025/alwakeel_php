<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    protected $fillable = ['title', 'last_message_at'];
    
    protected $casts = [
        'last_message_at' => 'datetime',
    ];
    
    public function messages()
    {
        return $this->hasMany(ConversationMessage::class)->orderBy('created_at');
    }
    
    public function latestMessage()
    {
        return $this->hasOne(ConversationMessage::class)->latestOfMany();
    }
    
    public function updateTitle()
    {
        $firstMessage = $this->messages()->where('role', 'user')->first();
        if ($firstMessage && !$this->title) {
            $this->title = mb_substr($firstMessage->content, 0, 50) . (mb_strlen($firstMessage->content) > 50 ? '...' : '');
            $this->save();
        }
    }
}
